<?php

namespace hypeJunction\Prototyper;

class Form extends Prototype {

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
		$i = 0;
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
			$output .= $field->viewInput(array(
				'index' => $i,
			));
			$i++;
		}
		return $output;
	}

	/**
	 * View form
	 * @param array $params
	 * @return string
	 */
	function view() {
		return elgg_view('input/form', $this->getFormAttributes());
	}

	/**
	 * Get attributes of the form
	 * @param array $params
	 * @return array
	 */
	public function getFormAttributes() {
		$params = $this->getParams();
		$params['body'] = $this->viewBody();
		$params['enctype'] = $this->getEncoding();
		if (!isset($params['action'])) {
			$params['action'] = 'action/' . $this->getAction();
		}

		return $params;
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
			if (!$field instanceof Field) {
				continue;
			}
			if ($field->getType() == 'file' || $field->getValueType() == 'file') {
				return true;
			}
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHandler() {
		return self::CONTEXT_FORM;
	}
}
