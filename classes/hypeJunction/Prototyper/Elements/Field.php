<?php

namespace hypeJunction\Prototyper\Elements;

abstract class Field implements FieldProperties, FieldInput, FieldOutput, FieldData, FieldStickyValues, FieldValidation {

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
	 * Type of entity
	 * @var string
	 */
	protected $entity_type;

	/**
	 * Subtype of entity
	 * @var string
	 */
	protected $entity_subtype;

	/**
	 * Class name
	 * @var string 
	 */
	protected $class_name;

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
	 * Admin only field
	 * @var boolean
	 */
	protected $admin_only;

	/**
	 * Hide on profile
	 * @var boolean 
	 */
	protected $hide_on_profile;

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
	 * Vars passed to the input view
	 * @var \stdClass
	 */
	protected $input_vars;

	/**
	 * Vars passed to the output view
	 * @var \stdClass
	 */
	protected $output_vars;

	/**
	 * Validation status
	 * @var \stdClass
	 */
	protected $validation;

	/**
	 * Validation rules
	 * @var array
	 */
	protected $validation_rules = array();

	/**
	 * Order of the field
	 * @var int
	 */
	protected $priority = 500;

	/**
	 * Flags used for filtering
	 * @var array
	 */
	protected $flags = array();

	/**
	 * Construct a new field
	 *
	 * @param array $options Options
	 */
	public function __construct(array $options = array()) {
		$this->input_vars = new \stdClass();
		$this->output_vars = new \stdClass();
		foreach ($options as $key => $value) {
			$this->set($key, $value);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($name) {
		return $this->$name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($name, $value = null) {

		$props = get_object_vars($this);

		switch ($name) {

			default :
				if (array_key_exists($name, $props)) {
					$this->$name = $value;
				} else {
					$this->input_vars->$name = $value;
				}
				break;

			case 'type' :
				$this->setType($value);
				break;

			case 'value_type' :
				$this->setValueType($value);
				break;

			case 'validation' :
			case 'validation_rules' :
				$value = (array) $value;
				foreach ($value as $rule => $expectation) {
					$this->addValidationRule($rule, $expectation);
				}
				break;

			case 'flags' :
				if (is_string($value)) {
					$value = string_to_tag_array($value);
				}
				$this->flags = $value;
				break;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getShortname() {
		return $this->shortname;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setType($type = '') {
		$this->type = $type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getType() {
		return ($this->type) ? $this->type : 'text';
	}

	/**
	 * {@inheritdoc}
	 */
	public function setValueType($value_type = '') {
		$this->value_type = $value_type;
		if (!$this->getValidationRule('type')) {
			$this->addValidationRule('type', $value_type);
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValueType() {
		return $this->value_type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValue() {
		return $this->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function viewInput($vars = array()) {
		$vars['field'] = $this;
		$data_type = $this->getDataType();
		return elgg_view("prototyper/input/$data_type", $vars);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInputView() {
		$view = (isset($this->input_view)) ? $this->input_view : "input/$this->type";
		return $view;
	}

	/**
	 * {@inheritdoc}
	 */
	public function viewOutput($vars = array()) {
		$vars['field'] = $this;
		$data_type = $this->getDataType();
		return elgg_view("prototyper/output/$data_type", $vars);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOutputView() {
		return (isset($this->output_view)) ? $this->output_view : "output/$this->type";
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasAccessInput() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFlags() {
		return (is_array($this->flags)) ? $this->flags : array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isRequired() {
		return ($this->input_vars->required);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isAdminOnly() {
		return ($this->admin_only);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isHiddenOnProfile() {
		return ($this->hide_on_profile);
	}
	/**
	 * {@inheritdoc}
	 */
	public function isMultiple() {
		return ($this->multiple);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInputVars(\ElggEntity $entity) {
		$this->input_vars->entity = ($entity->guid) ? $entity : null;
		$this->input_vars->required = $this->isRequired();

		if (!empty($this->input_vars->options_values) && is_array($this->input_vars->options_values)) {
			$lang = get_language();
			$options_values = array();

			foreach ($this->input_vars->options_values as $o_key => $o_value) {
				if (is_array($o_value)) {
					$o_value = elgg_extract($lang, $o_value, elgg_echo(implode(':', array_filter(array(
						'option',
						$this->entity_type,
						$this->entity_subtype,
						$this->getShortname(),
						$o_key,
					)))));
				}
				$options_values[$o_key] = $o_value;
			}

			if ($this->type == 'checkboxes' || $this->type == 'radio') {
				$this->input_vars->options = array_flip($options_values);
			}
			$this->input_vars->options_values = $options_values;
		}

		$vars = (array) $this->input_vars;

		$clean = array('ui_sections', 'relationship', 'inverse_relationship', 'bilateral');
		foreach ($clean as $key) {
			unset($vars[$key]);
		}
		
		return elgg_trigger_plugin_hook('input_vars', 'prototyper', array(
			'field' => $this,
			'entity' => $entity,
				), $vars);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOutputVars(\ElggEntity $entity) {
		$this->output_vars->entity = $entity;
		return (array) $this->output_vars;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLabel($lang = '', $raw = false) {

		$key = implode(':', array_filter(array(
			'label',
			$this->entity_type,
			$this->entity_subtype,
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
	 * {@inheritdoc}
	 */
	public function getHelp($lang = '', $raw = false) {

		$key = implode(':', array_filter(array(
			'help',
			$this->entity_type,
			$this->entity_subtype,
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
	 * {@inheritdoc}
	 */
	public function addValidationRule($rule, $expectation) {
		if ($rule && $expectation) {
			$this->validation_rules[$rule] = $expectation;
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValidationRule($rule) {
		if (isset($this->validation_rules[$rule])) {
			return $this->validation_rules[$rule];
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValidationRules() {
		return $this->validation_rules;
	}

	/**
	 * {@inheritdoc}
	 */
	public function applyValidationRules($value = '', ValidationStatus $validation = null, \ElggEntity $entity = null) {

		if (!$validation instanceof ValidationStatus) {
			$validation = new ValidationStatus;
		}

		$validation_rules = $this->getValidationRules();
		if (!empty($validation_rules)) {
			foreach ($validation_rules as $rule => $expectation) {
				$validation = elgg_trigger_plugin_hook("validate:$rule", 'prototyper', array(
					'rule' => $rule,
					'field' => $this,
					'value' => $value,
					'expectation' => $expectation,
					'entity' => $entity,
						), $validation);

				if (!$validation instanceof ValidationStatus) {
					elgg_log("'validate:$rule,'prototyper' hook must return an instance of ValidationStatus", 'ERROR');
					$validation = new ValidationStatus();
				}
			}
		}
		return $validation;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setValidation($status = true, $messages = array()) {
		$this->validation = new ValidationStatus($status, $messages);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValidation() {
		return ($this->validation instanceof ValidationStatus) ? $this->validation : new ValidationStatus();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isValid() {
		return $this->getValidation()->getStatus();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValidationMessages() {
		return $this->getValidation()->getMessages();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setStickyValue($value = '') {
		$this->sticky_value = $value;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStickyValue() {
		return $this->sticky_value;
	}

}
