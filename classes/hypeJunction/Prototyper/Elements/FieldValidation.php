<?php

namespace hypeJunction\Prototyper\Elements;

interface FieldValidation {

	/**
	 * Validate user input values
	 * @return ValidationStatus
	 */
	public function validate();

	/**
	 * Add a validation rule to the field
	 *
	 * @param string $rule        Rule name
	 * @param mixed  $expectation Expectation
	 * @return self
	 */
	public function addValidationRule($rule, $expectation);

	/**
	 * Get rule expectations
	 *
	 * @param string $rule Rule name
	 * @return mixed
	 */
	public function getValidationRule($rule);

	/**
	 * Get validation rules
	 * @return array
	 */
	public function getValidationRules();

	/**
	 * Apply validation rules
	 *
	 * @param mixed            $value      Value to validate
	 * @param ValidationStatus $validation Current validation status
	 * @return ValidationStatus
	 */
	public function applyValidationRules($value = '', ValidationStatus $validation = null);

	/**
	 * Set validation status
	 *
	 * @param boolean $status
	 * @param boolean $messages
	 * @return self
	 */
	public function setValidation($status = true, $messages = array());

	/**
	 * Get validation status object
	 * @return ValidationStatus
	 */
	public function getValidation();

	/**
	 * Get validation status
	 * @return boolean
	 */
	public function isValid();

	/**
	 * Get validation messages
	 * @return array
	 */
	public function getValidationMessages();
}
