<?php

/**
 * Abstract class for rendering, validating and processing fields
 */

namespace hypeJunction\Prototyper;

use ElggEntity;
use Exception;
use stdClass;

abstract class Field {

	/**
	 * Short name of the field (used as metadata or annotation or relationship name)
	 * @var string
	 */
	protected $shortname;

	/**
	 * Type of the input (used for rendering input views and validation)
	 * @var string
	 */
	protected $type;

	/**
	 * Type of the model used to store and retrieve values
	 * @var string
	 */
	protected $data_type;

	/**
	 * Type of value (used for validation)
	 * @var type
	 */
	protected $value_type;

	/**
	 * Elgg view to display an input (if different from "input/$type"
	 * @var string
	 */
	protected $input_view;

	/**
	 * Elgg view to display an output (if different from "output/$type"
	 * @var string
	 */
	protected $output_view;

	/**
	 * Input label
	 * @var boolean|string
	 */
	protected $label;

	/**
	 * Help text
	 * @var boolean|string
	 */
	protected $help;

	/**
	 * Display an access input
	 * @var boolean
	 */
	protected $show_access;

	/**
	 * Allow cloning of input fields
	 * @var boolean
	 */
	protected $multiple;

	/**
	 * Value passed to the input
	 * @var mixed
	 */
	protected $value;

	/**
	 * Sticky value inherited from failed validation
	 * @var mixed
	 */
	protected $sticky_value;

	/**
	 * Entity this field applies to
	 * @var ElggEntity
	 */
	protected $entity;

	/**
	 * Vars passed to the input view
	 * @var stdClass
	 */
	protected $input_vars;

	/**
	 * Vars passed to the output view
	 * @var stdClass
	 */
	protected $output_vars;

	/**
	 * Validation status
	 * @var stdClass
	 */
	protected $validation;

	/**
	 * Validation rules
	 * @var array
	 */
	protected $validation_rules = array();

	/**
	 * Construct a new field
	 * @param string $shortname
	 * @param ElggEntity $entity
	 * @param array $options
	 * @throws Exception
	 */
	function __construct($shortname, $entity, $options = '') {
		if (!$shortname || !is_string($shortname)) {
			throw new Exception(get_class($this) . ' requires a non empty string shortname');
		}
		if (!elgg_instanceof($entity)) {
			throw new Exception(get_class($this) . ' requires a valid Elgg entity');
		}

		$this->shortname = $shortname;
		$this->entity = $entity;
		$this->setOptions($options);
	}

	/**
	 * Render input
	 */
	abstract function viewInput($vars = array());

	/**
	 * Render output
	 */
	abstract function viewOutput($vars = array());

	/**
	 * Extract field values from an entity
	 */
	abstract function getValues();

	/**
	 * Validate input values
	 * @return ValidationStatus
	 */
	abstract function validate();

	/**
	 * Apply input values to an entity
	 */
	abstract function handle();

	/**
	 * Get protected properties
	 * @param string $name
	 * @return mixed
	 */
	public function get($name) {
		return $this->$name;
	}

	/**
	 * Set field options
	 * @param string|array $options String specifying $type or an array of options
	 * @return Field
	 */
	public function setOptions($options = array()) {

		if (is_string($options)) {
			$options = array(
				'type' => $options,
			);
		} else if (!is_array($options)) {
			$options = array(
				'type' => 'text',
			);
		} else if (!isset($options['type'])) {
			$options['type'] = 'text';
		}

		$this->input_vars = new stdClass();
		foreach ($options as $key => $value) {

			switch ($key) {

				default :
					$this->input_vars->$key = $value;
					break;

				case 'shortname' :
					$this->shortname = $value;
					break;

				case 'type' :
					$this->type = $value;
					if (!isset($this->value_type)) {
						$this->value_type = $this->type;
					}
					break;

				case 'data_type' :
					// already set in constructor
					break;

				case 'value_type' :
					$this->value_type = $value;
					if (!$this->getValidationRule('value_type')) {
						$this->addValidationRule('value_type', $value);
					}
					break;

				case 'input_view' :
					$this->input_view = $value;
					break;

				case 'output_view' :
					$this->output_view = $value;
					break;

				case 'label' :
					$this->label = $value;
					break;

				case 'help' :
					$this->help = $value;
					break;

				case 'show_access' :
					$this->show_access = $value;
					break;

				case 'multiple' :
					$this->multiple = $value;
					break;

				case 'validation_rules' :
					if (!is_array($value)) {
						elgg_log('validation_rules property in ' . get_class($this) . ' should be set as array');
						$value = array($value);
					}

					foreach ($value as $rule => $expectation) {
						$this->addValidationRule($rule, $expectation);
					}
					break;

				case 'options' :
					if (is_string($value) && is_callable($value)) {
						$value = call_user_func($value, $this);
					}
					$this->input_vars->options = $value;
					break;

				case 'options_values' :
					$lang = get_language();
					$options_values = array();
					if (is_string($value) && is_callable($value)) {
						$options_values = call_user_func($value, $this);
					} else if (is_array($value)) {
						foreach ($value as $o_key => $o_value) {
							if (is_array($o_value)) {
								$o_value = elgg_extract($lang, $o_value);
								if (!$o_value) {
									$o_value = elgg_echo(implode(':', array_filter(array(
										'option',
										$this->entity->getType(),
										$this->entity->getSubtype(),
										$this->getShortname(),
										$o_key,
									))));
								}
							}
							$options_values[$o_key] = $o_value;
						}
					}
					if ($this->type == 'checkboxes' || $this->type == 'radio') {
						$inverse_options = array();
						foreach ($options_values as $o_key => $o_value) {
							$inverse_options[$o_value] = $o_key;
						}
						$this->input_vars->options = $inverse_options;
					}
					$this->input_vars->options_values = $options_values;
					break;
			}
		}

		return $this;
	}

	/**
	 * Get shortname
	 * @return string
	 */
	public function getShortname() {
		return $this->shortname;
	}

	/**
	 * Get entity
	 * @return ElggEntity
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * Get input type
	 * @return string
	 */
	public function getType() {
		return ($this->type) ? $this->type : 'text';
	}

	/**
	 * Get data type
	 * @return string
	 */
	public function getDataType() {
		return $this->data_type;
	}

	/**
	 * Get value type
	 * @return string
	 */
	public function getValueType() {
		return $this->value_type;
	}

	/**
	 * Get name of the input view
	 * @return string
	 */
	public function getInputView() {
		$view = (isset($this->input_view)) ? $this->input_view : "input/$this->type";
		return $view;
	}

	/**
	 * Get name of the output view
	 * @return string
	 */
	public function getOutputView() {
		return (isset($this->output_view)) ? $this->output_view : "output/$this->type";
	}

	/**
	 * Display access input
	 * @return boolean
	 */
	public function hasAccessInput() {
		return false;
	}

	/**
	 * Is user input required
	 * @return boolean
	 */
	public function isRequired() {
		return ($this->input_vars->required);
	}

	/**
	 * Allow cloning of the field
	 * @return boolean
	 */
	public function isMultiple() {
		return ($this->multiple);
	}

	/**
	 * Get vars to be passed to the input
	 * @return array
	 */
	public function getInputVars() {
		$this->input_vars->entity = $this->getEntity();
		$this->input_vars->required = $this->isRequired();
		return (array) $this->input_vars;
	}

	/**
	 * Get vars to the be passed to the output
	 * @return type
	 */
	public function getOutputVars() {
		$this->output_vars->entity = $this->getEntity();
		return (array) $this->output_vars;
	}

	/**
	 * Get input label
	 * @param string $lang	Language code
	 * @param boolean $raw	Get raw language key
	 * @return string|false
	 */
	public function getLabel($lang = '', $raw = false) {

		$key = implode(':', array_filter(array(
			'label',
			$this->entity->getType(),
			$this->entity->getSubtype(),
			$this->getShortname()
		)));

		if ($raw) {
			return $key;
		}

		if ($this->label === false) {
			return false;
		}

		if (!$lang) {
			$lang = get_language();
		}

		if (is_string($this->label)) {
			$translation = $this->label;
		} else if (is_array($this->label)) {
			$translation = elgg_extract($lang, $this->label);
		}


		return ($translation) ? $translation : elgg_echo($key, array(), $lang);
	}

	/**
	 * Get input help text
	 * @param string $lang	Language code
	 * @param boolean $raw	Get raw language key
	 * @return string|false
	 */
	public function getHelp($lang = '', $raw = false) {

		$key = implode(':', array_filter(array(
			'help',
			$this->entity->getType(),
			$this->entity->getSubtype(),
			$this->getShortname()
		)));

		if ($raw) {
			return $key;
		}

		if ($this->help === false) {
			return false;
		}

		if (!$lang) {
			$lang = get_language();
		}

		if (is_string($this->help)) {
			$translation = $this->help;
		} else if (is_array($this->help)) {
			$translation = elgg_extract($lang, $this->help);
		}


		return ($translation) ? $translation : elgg_echo($key, array(), $lang);
	}

	/**
	 * Add a validation rule to the field
	 * @param string $rule
	 * @param mixed $expectation
	 * @return Field
	 */
	public function addValidationRule($rule, $expectation) {
		$this->validation_rules[$rule] = $expectation;
		return $this;
	}

	/**
	 * Get rule expectations
	 * @param string $rule
	 * @return mixed
	 */
	public function getValidationRule($rule) {
		if (isset($this->validation_rules[$rule])) {
			return $this->validation_rules[$rule];
		}
		return false;
	}

	/**
	 * Get validation rules
	 * @return array
	 */
	public function getValidationRules() {
		return $this->validation_rules;
	}

	/**
	 * Apply validation rules
	 * @param mixed $value	Value to validate
	 * @param ValidationStatus $validation Current validation status
	 * @return ValidationStatus
	 */
	public function applyValidationRules($value = '', $validation = null) {

		if (!$validation instanceof ValidationStatus) {
			$validation = new ValidationStatus;
		}

		$validation_rules = $this->getValidationRules();
		if ($validation_rules) {

			foreach ($validation_rules as $rule => $expectation) {

				switch ($rule) {

					case 'value_type' :
						switch ($expectation) {
							case 'number' :
								if (!is_numeric($value)) {
									$validation->setFail(elgg_echo('prototyper:validate:error:value_type:number', array($this->getLabel())));
								}
								break;
							case 'entity' :
								if (is_numeric($value) && !elgg_entity_exists($value)) {
									$validation->setFail(elgg_echo('prototyper:validate:error:value_type:entity', array($this->getLabel())));
								}
								break;
							case 'image' :
								$type = elgg_extract('type', $value);
								if (!$type || substr_count($type, 'image/') == 0) {
									$validation->setFail(elgg_echo('prototyper:validate:error:value_type:image', array($this->getLabel())));
								}
								break;
						}
						break;

					case 'min' :
						if ($value < $expectation) {
							$validation->setFail(elgg_echo('prototyper:validate:error:min', array($this->getLabel(), $expectation)));
						}
						break;

					case 'max' :
						if ($value > $expectation) {
							$validation->setFail(elgg_echo('prototyper:validate:error:max', array($this->getLabel(), $expectation)));
						}
						break;

					case 'minlength' :
						if (strlen($value) < $expectation) {
							$validation->setFail(elgg_echo('prototyper:validate:error:minlength', array($this->getLabel(), $expectation)));
						}
						break;

					case 'maxlength' :
						if (strlen($value) > $expectation) {
							$validation->setFail(elgg_echo('prototyper:validate:error:maxlength', array($this->getLabel(), $expectation)));
						}
						break;
				}

				$validation = elgg_trigger_plugin_hook('validate:rule', 'prototyper', array(
					'rule' => $rule,
					'field' => $this,
					'value' => $value,
					'expectation' => $expectation,
						), $validation);

				if (!$validation instanceof ValidationStatus) {
					elgg_log("'validate:rule','prototyper' hook must return an instance of ValidationStatus", 'ERROR');
					$validation = new ValidationStatus();
				}
			}
		}
		return $validation;
	}

	/**
	 * Set validation status
	 * @param boolean $status
	 * @param boolean $messages
	 * @return Field
	 */
	public function setValidation($status = true, $messages = array()) {
		$this->validation = new ValidationStatus($status, $messages);
		return $this;
	}

	/**
	 * Get validation status object
	 * @return ValidationStatus
	 */
	public function getValidation() {
		return ($this->validation instanceof ValidationStatus) ? $this->validation : new ValidationStatus();
	}

	/**
	 * Get validation status
	 * @return boolean
	 */
	public function isValid() {
		return $this->getValidation()->getStatus();
	}

	/**
	 * Get validation messages
	 * @return array
	 */
	public function getValidationMessages() {
		return $this->getValidation()->getMessages();
	}

	/**
	 * Set a sticky value
	 * @param mixed $value
	 * @return Field
	 */
	public function setStickyValue($value = '') {
		$this->sticky_value = $value;
		return $this;
	}

	/**
	 * Get a sticky value
	 * @return mixed
	 */
	public function getStickyValue() {
		return $this->sticky_value;
	}

	/**
	 * Generate microformat classes
	 * @return string
	 */
	public function getMicroformat() {

		$classes = array();

		$shortname = $this->getShortname();
		$type = $this->getType();

		switch ($shortname) {
			case 'title' :
			case 'name' :
				$classes[] = 'p-name';
				break;
			case 'phone' :
			case 'mobile' :
			case 'contact_phone' :
				$classes[] = 'p-tel';
				break;
			case 'sex' :
			case 'gender' :
				$classes[] = 'p-sex';
				break;
			case 'description' :
				$classes[] = 'p-description';
				break;
			case 'summary' :
			case 'briefdescription' :
				$classes[] = 'p-summary';
				break;
			case 'guid' :
				$classes[] = 'u-uid';
				break;
			default :
				$property = preg_replace('/[^a-z0-9\-]/i', '-', $shortname);
				$classes[] = "p-$property";
				break;
		}
		switch ($type) {
			case 'url' :
				$classes[] = 'u-url';
				break;
			case 'email' :
				$classes[] = 'u-email';
				break;
			case 'phone' :
				$classes[] = 'p-tel';
				break;
			case 'location' :
				$classes[] = 'p-adr p-location';
				break;
			case 'tags' :
			case 'category' :
			case 'categories' :
				$classes[] = 'p-category';
				break;
		}

		return implode(' ', array_unique($classes));
	}

}
