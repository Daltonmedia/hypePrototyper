<?php

/**
 * Handle category fields
 */

namespace hypeJunction\Prototyper;

use ElggEntity;
use stdClass;

class CategoryField extends RelationshipField {

		/**
	 * Get new field instance
	 * @param string $shortname
	 * @param array|string $options
	 * @return \self
	 */
	public static function getInstance($shortname, $options = '') {
		$instance = new self($shortname, $options);
		$instance->inverse_relationship = true;
		$instance->belateral = false;
		if (isset($instance->multiple)) {
			$instance->input_vars->multiple = true;
		}
		$instance->data_type = 'category';
		$instance->input_view = 'input/categories';
		$instance->output_view = 'input/categories';

		return $instance;
	}

	/**
	 * Display access input
	 * @return boolean
	 */
	public function hasAccessInput() {
		return false;
	}

	/**
	 * Render an input view
	 * @param array $vars
	 * @return string
	 */
	function viewInput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('forms/prototyper/category', $vars);
	}

	/**
	 * Render an output
	 * @param array $vars
	 * @return string
	 */
	function viewOutput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('output/prototyper/category', $vars);
	}

	/**
	 * Get relationship values
	 * @return stdClass
	 */
	public function getValues() {
		$sticky = $this->getStickyValue();
		$values = array();
		if ($sticky) {
			return $sticky;
		}
		return $values;
	}

	/**
	 * Handle values submitted via the form
	 */
	public function handle() {

		// let hypeCategories handle the rest
		$shortname = $this->getShortname();
		if ($shortname !== 'categories') {
			set_input('categories', get_input($shortname));
		}
		return true;
	}

}
