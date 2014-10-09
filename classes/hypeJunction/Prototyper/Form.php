<?php

/**
 * Renders, validates and handles forms based on a set of configuration options
 * supplied via 'pepare','form:$action' hook
 */

namespace hypeJunction\Prototyper;

use ElggEntity;
use Exception;

class Form {

	/**
	 * Action name
	 * @var string
	 */
	protected $action;

	/**
	 * Fields
	 * @var array
	 */
	protected $fields;

	/**
	 * Construct a new form
	 * @param string $action		A registered action
	 * @param array $params			Additional params
	 * @throws Exception
	 */
	private function __construct($action, $params = array()) {

		if (!$action || !elgg_action_exists($action)) {
			throw new Exception(get_class($this) . ' expects a valid registered action');
		}

		$this->action = $action;

		if (!elgg_instanceof($this->getEntity())) {
			throw new Exception(get_class($this) . ' should be called after a prototype has been instantiated');
		}

		if (!is_array($params)) {
			$params = array();
		}
		$params['entity'] = $this->getEntity();

		$fields = elgg_trigger_plugin_hook('prototype', "$action", $params, array());

		if (!is_array($fields)) {
			throw new Exception(get_class($this) . " expects a an array of fields returned from 'prototype','$this->action' hook");
		}

		$this->setFields($fields);
	}

	/**
	 * Construct a new form
	 * @param string $action		A registered action
	 * @param array $params			Additional params
	 * @throws Exception
	 */
	public static function getInstance($action, $params = array()) {
		return new self($action, $params);
	}

	/**
	 * Set form fields
	 * @param array $fields
	 * @return Form
	 */
	private function setFields($fields = array()) {

		$entity_attributes = array('guid', 'type', 'subtype', 'owner_guid', 'container_guid', 'access_id');
		foreach ($entity_attributes as $attr) {
			if (!array_key_exists($attr, $fields)) {
				$attr_fields[$attr] = 'hidden';
			}
		}

		if (!is_array($fields)) {
			$fields = array();
		}

		$fields = array_merge($attr_fields, $fields);
		if (is_array($fields)) {
			foreach ($fields as $shortname => $field) {
				$this->addField($shortname, $field);
			}
		}
		return $this;
	}

	/**
	 * Get entity
	 * @return ElggEntity
	 */
	public function getEntity() {
		return Prototype::getEntity();
	}

	/**
	 * Get form fields
	 * @return Field[]
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Add a field to a form
	 * @param string $shortname		Unique field identifier
	 * @param array $options		Options describing the field
	 * @return Field
	 */
	public function addField($shortname, $options = array()) {
		try {
			if (is_array($options)) {
				$data_type = elgg_extract('data_type', $options, 'metadata');
			} else {
				$data_type = 'metadata';
			}

			switch ($data_type) {

				default :
					$class_name = elgg_extract('class_name', $options);
					if (class_exists($class_name)) {
						$field = $class_name::getInstance($shortname, $options);
					}
					break;

				case 'attribute' :
				case 'metadata' :
					if (in_array($shortname, Prototype::getAttributeNames($this->getEntity()))) {
						$field = AttributeField::getInstance($shortname, $options);
					} else {
						$field = MetadataField::getInstance($shortname, $options);
					}
					break;

				case 'annotation' :
					$field = AnnotationField::getInstance($shortname, $options);
					break;

				case 'relationship' :
					$field = RelationshipField::getInstance($shortname, $options);
					break;

				case 'category' :
					$field = CategoryField::getInstance($shortname, $options);
					break;

				case 'icon' :
					$field = IconField::getInstance($shortname, $options);
					break;

				case 'cover' :
					$field = CoverField::getInstance($shortname, $options);
					break;
			}

			$this->fields[$shortname] = $field;
		} catch (Exception $e) {
			elgg_log($e->getMessage());
		}

		return $field;
	}

	/**
	 * Remove a field from the from
	 * @param string $shortname
	 * @return Form
	 */
	public function removeField($shortname) {
		if (isset($this->fields[$shortname])) {
			unset($this->fields[$shortname]);
		}
		return $this;
	}

	/**
	 * Render form body
	 * @return string
	 */
	public function viewBody() {

		// Get sticky values
		$sticky_values = $this->getStickyValues();
		$this->clearStickyValues();

		// Get validation errors and messages
		$validation_status = $this->getValidationStatus($this->action);
		$this->clearValidationStatus();

		$fields = $this->getFields();

		// Prepare fields
		foreach ($fields as $field) {

			if (!$field instanceof Field) {
				continue;
			}
			
			if ($field->getInputView() === false) {
				continue;
			}
			$shortname = $field->getShortname();
			if (isset($sticky_values[$shortname])) {
				$field->setStickyValue($sticky_values[$shortname]);
			}

			if (isset($validation_status[$shortname])) {
				$field->setValidation($validation_status[$shortname]['status'], $validation_status[$shortname]['messages']);
			}
			$output .= $field->viewInput();
		}
		return $output;
	}

	/**
	 * View form
	 * @param array $params
	 * @return string
	 */
	function view($params = array()) {
		return elgg_view('input/form', $this->getFormAttributes($params));
	}

	/**
	 * Render an entity profile
	 * @param array $params
	 * @return string
	 */
	public function viewProfile($params = array()) {
		$fields = $this->getFields();

		$outputs = array();
		$output = '';

		// Prepare fields
		foreach ($fields as $field) {
			if ($field->getOutputView() === false) {
				continue;
			}
			if ($field->getType() == 'hidden') {
				continue;
			}
			$outputs[] = $field->viewOutput($params);
		}

		$even_odd = null;
		foreach ($outputs as $op) {
			if (!$op) {
				continue;
			}
			$even_odd = ( 'odd' != $even_odd ) ? 'odd' : 'even';
			$output .= "<div class=\"prototyper-output $even_odd\">$op</div>";
		}

		return $output;
	}

	/**
	 * Get attributes of the form
	 * @param array $params
	 * @return array
	 */
	public function getFormAttributes($params = array()) {

		$params['body'] = $this->viewBody();
		$params['enctype'] = $this->getEncoding();
		if (!isset($params['action'])) {
			$params['action'] = 'action/' . $this->getAction();
		}

		return $params;
	}

	/**
	 * Get a form action
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * Get form encoding
	 * @return string
	 */
	public function getEncoding() {
		if ($this->isMultipart()) {
			return 'multipart/form-data';
		}
		return 'application/x-www-form-urlencoded';
	}

	/**
	 * Check if form contains file inputs
	 * @return boolean
	 */
	function isMultipart() {

		foreach ($this->fields as $field) {
			if ($field->getType() == 'file' || $field->getValueType() == 'file') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Validate the form before proceeding with the action
	 * @return bool
	 */
	function validate() {

		$valid = true;
		$fields = $this->getFields();
		foreach ($fields as $field) {
			$validation = $field->validate();
			$this->setFieldValidationStatus($field->getShortname(), $validation);
			if (!$validation->isValid()) {
				$valid = false;
			}
		}
		return $valid;
	}

	/**
	 * Handle submitted values
	 * @return bool
	 */
	public function handle() {

		$fields = $this->getFields();

		// first handle attributes
		foreach ($fields as $field) {
			if ($field->getDataType() == 'attribute') {
				$field->handle();
			}
		}

		try {
			$guid = $this->getEntity()->save();
		} catch (Exception $e) {
			register_error(elgg_echo('prorotyper:handle:error', array($e->getMessage())));
			forward();
		}

		// first handle attributes
		foreach ($fields as $field) {
			if ($field->getDataType() != 'attribute') {
				$field->handle();
			}
		}

		return ($guid);
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

}
