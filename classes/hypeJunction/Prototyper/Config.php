<?php

namespace hypeJunction\Prototyper;

/**
 * Config
 */
class Config extends \hypeJunction\Config {

	/**
	 * Input type definitions
	 * @var array
	 */
	private $types = array();

	/**
	 * Validation rule definitions
	 * @var array
	 */
	private $validation_rules = array();

	/**
	 * {@inheritdoc}
	 */
	public function getDefaults() {
		return array(
			'default_language' => 'en',
		);
	}

	/**
	 * Define an input type
	 * 
	 * @param string $type      Input type
	 * @param string $classname Class name
	 * @param array  $options   Default options
	 * @return void
	 */
	public function registerType($type, $classname, $options = array()) {

		if (!class_exists($classname) || !is_callable(array($classname, 'getDataType'))) {
			return;
		}

		$data_type = call_user_func(array($classname, 'getDataType'));

		$options = (array) $options;
		$options['type'] = $type;
		$options['class_name'] = $classname;
		$options['data_type'] = $data_type;
		
		$this->types[$data_type][$type] = $options;
		
	}

	/**
	 * Returns a handler classname
	 *
	 * @param string $data_type Registered data type
	 * @param string $type      Registered input type
	 * @return boolean|array
	 */
	public function getType($data_type = 'metadata', $type = 'text') {
		if (isset($this->types[$data_type][$type])) {
			return $this->types[$data_type][$type];
		}
		return false;
	}

	/**
	 * Returns all registered types
	 * @return array
	 */
	public function getTypes() {
		return $this->types;
	}

	/**
	 * Registers a new validation rule for UI
	 *
	 * @param string $rule    Rule name
	 * @param mixed  $options Available options
	 */
	public function registerValidationRule($rule, $options = '') {
		$this->validation_rules[$rule] = $options;
	}

	/**
	 * Returns validation rules
	 * @return array
	 */
	public function getValidationRules() {
		return $this->validation_rules;
	}
}
