<?php

namespace hypeJunction\Prototyper\Elements;

interface FieldProperties {

	/**
	 * Sets a protected property
	 * 
	 * @param string $name  Prop name
	 * @param mixed  $value Prop value
	 */
	public function set($name, $value = null);

	/**
	 * Get protected properties
	 *
	 * @param string $name Property name
	 * @return mixed
	 */
	public function get($name);

	/**
	 * Get field shortname
	 * @return string
	 */
	public function getShortname();

	/**
	 * Set input type
	 *
	 * @param string $type Type
	 * @return self
	 */
	public function setType($type = '');

	/**
	 * Get input type
	 * @return string
	 */
	public function getType();

	/**
	 * Get data type
	 * @return string
	 */
	public static function getDataType();

	/**
	 * Set value type
	 *
	 * @param string $value_type Value type
	 * @return self
	 */
	public function setValueType($value_type = '');

	/**
	 * Get value type
	 * @return string
	 */
	public function getValueType();

	/**
	 * Display access input
	 * @return boolean
	 */
	public function hasAccessInput();

	/**
	 * Returns field flags
	 * @return array
	 */
	public function getFlags();

	/**
	 * Is user input required
	 * @return boolean
	 */
	public function isRequired();

	/**
	 * Is this field only visible to admins
	 * @return boolean
	 */
	public function isAdminOnly();

	/**
	 * Is this field hidden from profile
	 * @return boolean
	 */
	public function isHiddenOnProfile();

	/**
	 * Allow cloning of the field
	 * @return boolean
	 */
	public function isMultiple();

	/**
	 * Get input label
	 *
	 * @param string  $lang Language code
	 * @param boolean $raw  Get raw language key
	 * @return string|false
	 */
	public function getLabel($lang = '', $raw = false);

	/**
	 * Get input help text
	 *
	 * @param string  $lang Language code
	 * @param boolean $raw  Get raw language key
	 * @return string|false
	 */
	public function getHelp($lang = '', $raw = false);
}
