<?php

/**
 * Handle category fields
 */

namespace hypeJunction\Prototyper;

use ElggEntity;
use stdClass;

class CategoryField extends RelationshipField {

	/**
	 * Construct a new category field
	 * @param string $shortname
	 * @param ElggEntity $entity
	 * @param array $options
	 */
	function __construct($shortname, $entity, $options = '') {
		parent::__construct($shortname, $entity, $options);

		$this->inverse_relationship = true;
		$this->belateral = false;
		if (isset($this->multiple)) {
			$this->input_vars->multiple = true;
		}
		$this->data_type = 'category';
		$this->input_view = 'input/categories';
		$this->output_view = 'input/categories';
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
