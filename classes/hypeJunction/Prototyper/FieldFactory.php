<?php

namespace hypeJunction\Prototyper;

class FieldFactory {

	private $config;

	/**
	 * Constructor
	 *
	 * @param Config $config
	 */
	public function __construct(Config $config) {
		$this->config = $config;
	}

	/**
	 * Builds a field from an array of options
	 * 
	 * @param mixed $options Options
	 * @return Elements\Field|false
	 */
	public function build($options = array()) {

		if (is_string($options)) {
			$options = array(
				'type' => $options,
			);
		} else if (!is_array($options)) {
			$options = array(
				'type' => 'text',
			);
		}

		if (empty($options['type'])) {
			$options['type'] = 'text';
		}

		if (empty($options['data_type'])) {
			$options['data_type'] = 'metadata';
		}

		$defaults = (array) $this->config->getType($options['data_type'], $options['type']);

		$options = array_merge($defaults, $options);

		$classname = elgg_extract('class_name', $options);
		if (class_exists($classname)) {
			return new $classname($options);
		}

		return false;
	}

}
