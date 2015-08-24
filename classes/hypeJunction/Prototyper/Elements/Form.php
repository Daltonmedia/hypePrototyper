<?php

namespace hypeJunction\Prototyper\Elements;

class Form {

	/**
	 * Elgg entity
	 * @var \ElggEntity 
	 */
	private $entity;

	/**
	 * Action name
	 * @var string 
	 */
	private $action;

	/**
	 * Collection of fields
	 * @var FieldCollection
	 */
	private $fields;

	/**
	 * Constructor
	 *
	 * @param \ElggEntity     $entity Entity
	 * @param string          $action Action name
	 * @param FieldCollection $fields Fields
	 */
	public function __construct(\ElggEntity $entity, $action, FieldCollection $fields) {
		$this->entity = $entity;
		$this->action = $action;
		$this->fields = $fields;
	}

	/**
	 * Filter fields
	 *
	 * @param callable $filter
	 * @return self
	 */
	public function filter(callable $filter) {
		$this->fields = $this->fields->filter($filter);
		return $this;
	}

	/**
	 * Returns form body HTML
	 * @return string
	 */
	public function viewBody() {

		// Get sticky values
		$sticky_values = hypePrototyper()->prototype->getStickyValues($this->action);
		hypePrototyper()->prototype->clearStickyValues($this->action);

		// Get validation errors and messages
		$validation_status = hypePrototyper()->prototype->getValidationStatus($this->action);
		hypePrototyper()->prototype->clearValidationStatus($this->action);

		// Prepare fields
		$i = 0;
		foreach ($this->fields as $field) {

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

			$output .= $field->viewInput(array(
				'index' => $i,
				'entity' => $this->entity,
			));

			$i++;
		}

		$submit = elgg_view('input/submit', array(
			'value' => elgg_echo('save')
		));

		$output .= elgg_format_element('div', array(
			'class' => 'elgg-foot',
				), $submit);

		return $output;
	}

	/**
	 * View full form
	 *
	 * @param array $vars View vars
	 * @return string
	 */
	function view(array $vars = array()) {
		$attrs = $this->getFormAttributes();
		return elgg_view('input/form', array_merge($attrs, $vars));
	}

	/**
	 * Returns form attributes
	 * @return array
	 */
	public function getFormAttributes() {
		return array(
			'body' => $this->viewBody(),
			'enctype' => $this->getEncoding(),
			'action' => "action/$this->action",
		);
	}

	/**
	 * Returns form encoding
	 * @return string
	 */
	public function getEncoding() {
		if ($this->isMultipart()) {
			return 'multipart/form-data';
		}
		return 'application/x-www-form-urlencoded';
	}

	/**
	 * Checks if the form contains file inputs
	 * @return boolean
	 */
	function isMultipart() {
		foreach ($this->fields as $field) {
			if (!$field instanceof Field) {
				continue;
			}
			if ($field->getType() == 'file' || $field->getValueType() == 'file' || $field->getDataType()) {
				return true;
			}
		}
		return false;
	}

}
