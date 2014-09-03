<?php

/**
 * Handling of entity attribute fields
 */
namespace hypeJunction\Prototyper;

use ElggEntity;

class AttributeField extends Field {

	/**
	 * Construct a new field
	 * @param string $shortname
	 * @param ElggEntity $entity
	 * @param array $options
	 */
	function __construct($shortname, $entity, $options = '') {
		parent::__construct($shortname, $entity, $options);
		$this->data_type = 'attribute';
	}

	/**
	 * Render an input field
	 * @param array $vars
	 * @return string
	 */
	public function viewInput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('forms/prototyper/attribute', $vars);
	}

	/**
	 * Render an output
	 * @param array $vars
	 * @return string
	 */
	public function viewOutput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('output/prototyper/attribute', $vars);
	}

	/**
	 * Get values for this fields
	 * @return mixed
	 */
	public function getValues() {
		$entity = $this->getEntity();
		$name = $this->getShortname();

		switch ($name) {
			default :
				return $entity->$name;

			case 'type' :
				return $entity->getType();

			case 'subtype' :
				return $entity->getSubtype();
		}
	}

	/**
	 * Allow multiple
	 * @return boolean
	 */
	public function isMultiple() {
		return false;
	}

	/**
	 * Display access input
	 * @return boolean
	 */
	public function hasAccessInput() {
		return false;
	}

	/**
	 * Validate values
	 * @return ValidationStatus
	 */
	public function validate() {

		$validation = new ValidationStatus();

		$value = get_input($this->getShortname());
		if (is_string($value)) {
			$value = strip_tags($value);
		}
		if (is_null($value) || $value == '') {
			if ($this->isRequired()) {
				$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
			}
		} else {
			$validation = $this->applyValidationRules($value, $validation);
		}

		return $validation;
	}

	/**
	 * Handle values submitted via the form
	 * @return boolean
	 */
	public function handle() {

		$shortname = $this->getShortname();
		$future_value = get_input($shortname);

		if (is_null($future_value)) {
			return false;
		}

		$value = $this->getValues();

		$params = array(
			'field' => $this,
			'entity' => $this->entity,
			'attribute_name' => $shortname,
			'value' => $value,
			'future_value' => $future_value,
		);

		// Allow plugins to prevent attributes from being changed
		if (!elgg_trigger_plugin_hook('handle:attribute:before', 'prototyper', $params, true)) {
			return true;
		}

		switch ($shortname) {

			case 'guid' :
			case 'type' :
			case 'subtype' :
			case 'time_created' :
			case 'time_updated' :
			case 'last_action' :
			case 'enabled' :
				// skip
				break;

			case 'site_guid' :
			case 'owner_guid' :
			case 'container_guid' :
				$this->entity->$shortname = (int) $future_value;
				break;

			default :
				$this->entity->$shortname = $future_value;
				break;
		}

		$params = array(
			'field' => $this,
			'entity' => $this->entity,
			'attribute_name' => $shortname,
			'value' => $future_value,
			'previous_value' => $value,
		);

		return elgg_trigger_plugin_hook('handle:attribute:after', 'prototyper', $params, true);
	}

}
