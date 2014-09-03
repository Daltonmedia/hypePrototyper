<?php

/**
 * Handling of entity icon upload field
 */

namespace hypeJunction\Prototyper;

use ElggEntity;
use hypeJunction\Filestore\IconHandler;

class IconField extends Field {

	/**
	 * Construct a new field
	 * @param string $shortname
	 * @param ElggEntity $entity
	 * @param array $options
	 */
	function __construct($shortname, $entity, $options = '') {
		parent::__construct($shortname, $entity, $options);
		$this->type = 'file';
		$this->value_type = 'image';
		$this->data_type = 'icon';
		$this->output_view = false;
		$this->addValidationRule('value_type', 'image');
	}

	/**
	 * Render an input field
	 * @param array $vars
	 * @return string
	 */
	public function viewInput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('forms/prototyper/icon', $vars);
	}

	/**
	 * Render an output
	 * @param array $vars
	 * @return string
	 */
	public function viewOutput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('output/prototyper/icon', $vars);
	}

	/**
	 * Get values for this fields
	 * @return mixed
	 */
	public function getValues() {
		$entity = $this->getEntity();
		return ($entity->icontime);
	}

	/**
	 * Do not require the icon field if icon has already been uploaded
	 * @return boolean
	 */
	public function isRequired() {
		$entity = $this->getEntity();
		if ($entity->icontime) {
			return false;
		}
		return parent::isRequired();
	}
	/**
	 * Allow multiple
	 * @return boolean
	 */
	public function isMultiple() {
		return false;
	}

	/**
	 * Display access input
	 * @return boolean
	 */
	public function hasAccessInput() {
		return false;
	}

	/**
	 * Validate values
	 * @return ValidationStatus
	 */
	public function validate() {

		$shortname = $this->getShortname();
		$validation = new ValidationStatus();

		$value = $_FILES[$shortname];
		$error_type = elgg_extract('error', $value);

		$has_uploaded_file = ($error_type != UPLOAD_ERR_NO_FILE);
		if (!$has_uploaded_file) {
			if ($this->isRequired()) {
				$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
			}
		} else {
			$error = elgg_get_friendly_upload_error($error_type);
			if ($error) {
				$validation->setFail($error);
			} else {
				$validation = $this->applyValidationRules($value, $validation);
			}
		}

		return $validation;
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
			'icon_name' => $shortname,
			'future_value' => $future_value,
		);

		// Allow plugins to prevent icons from being uploaded
		if (!elgg_trigger_plugin_hook('handle:icon:before', 'prototyper', $params, true)) {
			return true;
		}
		
		$result = IconHandler::makeIcons($this->getEntity(), elgg_extract('tmp_name', $value));

		$params = array(
			'field' => $this,
			'entity' => $this->getEntity(),
			'icon_name' => $shortname,
			'value' => $future_value,
		);

		return elgg_trigger_plugin_hook('handle:icon:after', 'prototyper', $params, $result);
	}

}
