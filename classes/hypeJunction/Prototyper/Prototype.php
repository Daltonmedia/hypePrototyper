<?php

namespace hypeJunction\Prototyper;

use ElggEntity;

abstract class Prototype {

	const CONTEXT_ACTION = 'action';
	const CONTEXT_FORM = 'form';
	const CONTEXT_PROFILE = 'profile';

	/**
	 * Attributes of an entity
	 * @var array 
	 */
	protected $attributes = array();

	/**
	 * Entity
	 * @var Entity
	 */
	protected $entity;

	/**
	 * Registered action name
	 * @var string
	 */
	protected $action;

	/**
	 * Additional params
	 * @var array 
	 */
	protected $params = array();

	/**
	 * Fields
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Callback function to filter fields
	 * @var mixed
	 */
	protected $filter;

	/**
	 * Constructor
	 *
	 * @param string $action     Registered action name
	 * @param array  $attributes Attributes, include type and subtype
	 */
	public function __construct($action = '', $attributes = array()) {
		$this->action = $action;
		$this->attributes = $attributes;
	}

	/**
	 * Returns current context
	 * @return string Enumeration of "action", "form" or "profile"
	 */
	abstract function getHandler();

	/**
	 * Sets an action
	 * 
	 * @param string $action Action name
	 * @return self
	 */
	public function setAction($action = '') {
		$this->action = $action;
	}

	/**
	 * Returns action name
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * Sets an existing entity
	 *
	 * @param mixed $entity Entity or guid
	 * @return self
	 */
	public function setEntity($entity = null) {
		$this->entity = $entity;
	}

	/**
	 * Returns an entity
	 * @return ElggEntity
	 */
	public function getEntity() {
		if (!$this->entity instanceof ElggEntity) {
			$this->entity = Entity::factory($this->entity, $this->attributes);
		}
		return $this->entity;
	}

	/**
	 * Sets handler params
	 * 
	 * @param array $params Params
	 * @return self
	 */
	public function setParams(array $params = array()) {
		$this->params = $params;
	}

	/**
	 * Returns handler params
	 * @return type
	 */
	public function getParams() {
		return (is_array($this->params)) ? $this->params : array();
	}

	/**
	 * Adds a callback for filtering fields
	 *
	 * @param mixed $callable Callback function used as argument for array_filter()
	 * @return self
	 */
	public function setFilter($callable) {
		$this->filter = $callable;
		return self;
	}

	/**
	 * Removess the filter
	 * @return self
	 */
	public function removeFilter() {
		unset($this->filter);
		return $this;
	}

	/**
	 * Get form fields
	 * @return Field[]
	 */
	public function getFields() {
		$params = $this->getParams();
		$params['entity'] = $this->getEntity();
		$params['context'] = $this->getHandler();

		$fields = elgg_trigger_plugin_hook('prototype', $this->action, $params, array());

		$this->setFields($fields);
		$this->setAttributeFields();
		$this->filterFields();
		$this->sortFields();

		return $this->fields;
	}

	/**
	 * Adds a field to a form
	 *
	 * @param array  $options Field options
	 * @return Field
	 */
	public function addField($options = array()) {
		$field = Field::factory($options, $this->getEntity());
		if ($this->hasField($options['shortname'])) {
			$this->removeField($options['shortname']);
		}
		$this->fields[] = $field;
		return $field;
	}

	/**
	 * Removes a field from a from
	 *
	 * @param string $shortname Field name
	 * @return self
	 */
	public function removeField($shortname) {
		foreach ($this->fields as $key => $field) {
			if ($field instanceof Field && $field->getShortname() == $shortname) {
				unset($this->fields[$key]);
			}
		}
		return $this;
	}

	/**
	 * Checks if the form has a field
	 *
	 * @param string $shortname Field name
	 * @return bool
	 */
	public function hasField($shortname = '') {
		foreach ($this->fields as $field) {
			if ($field instanceof Field && $field->getShortname() == $shortname) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Store submitted sticky values
	 * @return bool
	 */
	public function saveStickyValues() {
		return elgg_make_sticky_form($this->getAction());
	}

	/**
	 * Clear sticky values
	 * @return type
	 */
	public function clearStickyValues() {
		return elgg_clear_sticky_form($this->getAction());
	}

	/**
	 * Get sticky values
	 * @return type
	 */
	public function getStickyValues() {
		return elgg_get_sticky_values($this->getAction());
	}

	/**
	 * Get form validation status
	 * @return type
	 */
	public function getValidationStatus() {

		$validation_status = null;

		if (isset($_SESSION['prototyper_validation'][$this->action])) {
			$validation_status = $_SESSION['prototyper_validation'][$this->action];
		}

		return $validation_status;
	}

	/**
	 * Save validation status of the field
	 * @param string $shortname
	 * @param ValidationStatus $validation
	 * @return void
	 */
	public function setFieldValidationStatus($shortname, ValidationStatus $validation) {

		if (!isset($_SESSION['prototyper_validation'][$this->action])) {
			$_SESSION['prototyper_validation'][$this->action] = array();
		}

		$_SESSION['prototyper_validation'][$this->action][$shortname] = array(
			'status' => $validation->getStatus(),
			'messages' => $validation->getMessages()
		);
	}

	/**
	 * Clear form validation
	 * @return void
	 */
	public function clearValidationStatus() {

		if (isset($_SESSION['prototyper_validation'][$this->action])) {
			unset($_SESSION['prototyper_validation'][$this->action]);
		}
	}

	/**
	 * Sets entity attribute fields
	 * @return self
	 */
	protected function setAttributeFields() {
		$attributes = Entity::getAttributeNames($this->getEntity());
		foreach ($attributes as $shortname) {
			if (!$this->hasField($shortname)) {
				$this->addField(array(
					'shortname' => $shortname,
					'type' => 'hidden',
					'priority' => 1,
				));
			}
		}
	}

	/**
	 * Set form fields
	 *
	 * @param array $fields
	 * @return Form
	 */
	protected function setFields(array $fields = array()) {

		foreach ($fields as $shortname => $field) {
			if (!isset($field['shortname'])) {
				$field['shortname'] = $shortname;
			}
			$this->addField($field);
		}
		return $this;
	}

	/**
	 * Filters fields using set filters
	 * @return self
	 */
	protected function filterFields() {

		if (is_callable($this->filter)) {
			$this->fields = array_filter($this->fields, $this->filter);
		}
		return $this;
	}

	/**
	 * Sort fields by priority
	 * @return self
	 */
	protected function sortFields() {
		uasort($this->fields, function($a, $b) {
			$priority_a = (int) $a->get('priority') ? : 500;
			$priority_b = (int) $b->get('priority') ? : 500;
			if ($priority_a == $priority_b) {
				return 0;
			}
			return ($priority_a < $priority_b) ? -1 : 1;
		});
		return $this;
	}

}
