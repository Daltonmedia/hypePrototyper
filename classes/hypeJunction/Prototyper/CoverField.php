<?php

/**
 * Handling of entity cover upload field
 */

namespace hypeJunction\Prototyper;

use hypeJunction\Filestore\CoverHandler;

class CoverField extends IconField {

	/**
	 * Get new field instance
	 * @param string $shortname
	 * @param array|string $options
	 * @return \self
	 */
	public static function getInstance($shortname, $options = '') {
		$instance = new self($shortname, $options);
		$instance->data_type = 'cover';

		return $instance;
	}

	/**
	 * Render an input field
	 * @param array $vars
	 * @return string
	 */
	public function viewInput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('forms/prototyper/cover', $vars);
	}

	/**
	 * Render an output
	 * @param array $vars
	 * @return string
	 */
	public function viewOutput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('output/prototyper/cover', $vars);
	}

	/**
	 * Get values for this fields
	 * @return mixed
	 */
	public function getValues() {
		$entity = $this->getEntity();
		return ($entity->covertime);
	}

	/**
	 * Handle values submitted via the form
	 * @return boolean
	 */
	public function handle() {

		$shortname = $this->getShortname();
		$future_value = $_FILES[$shortname];

		$value = $_FILES[$shortname];
		$error_type = elgg_extract('error', $value);

		$has_uploaded_file = ($error_type != UPLOAD_ERR_NO_FILE);

		if (!$has_uploaded_file) {
			return false;
		}

		$params = array(
			'field' => $this,
			'entity' => $this->getEntity(),
			'cover_name' => $shortname,
			'future_value' => $future_value,
		);

		// Allow plugins to prevent covers from being uploaded
		if (!elgg_trigger_plugin_hook('handle:cover:before', 'prototyper', $params, true)) {
			return true;
		}

		$result = CoverHandler::makeIcons($this->getEntity(), elgg_extract('tmp_name', $value));

		$params = array(
			'field' => $this,
			'entity' => $this->getEntity(),
			'cover_name' => $shortname,
			'value' => $future_value,
		);

		return elgg_trigger_plugin_hook('handle:cover:after', 'prototyper', $params, $result);
	}

}
