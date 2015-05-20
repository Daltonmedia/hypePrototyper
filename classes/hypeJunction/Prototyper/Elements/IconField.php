<?php

namespace hypeJunction\Prototyper\Elements;

class IconField extends Field {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function getValues(\ElggEntity $entity) {
		return ($entity->icontime);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isRequired() {
		$entity = $entity;
		if ($entity->icontime) {
			return false;
		}
		return parent::isRequired();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isMultiple() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasAccessInput() {
		return false;
	}

	/**
	 * {@inheritdoc}
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
			$error = hypeApps()->uploader->getFriendlyUploadError($error_type);
			if ($error) {
				$validation->setFail($error);
			} else {
				$validation = $this->applyValidationRules($value, $validation);
			}
		}

		return $validation;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {

		$shortname = $this->getShortname();
		$future_value = $_FILES[$shortname];

		$value = $_FILES[$shortname];
		$error_type = elgg_extract('error', $value);

		$has_uploaded_file = ($error_type != UPLOAD_ERR_NO_FILE);

		if (!$has_uploaded_file) {
			return $entity;
		}

		$params = array(
			'field' => $this,
			'entity' => $entity,
			'icon_name' => $shortname,
			'future_value' => $future_value,
		);

		// Allow plugins to prevent icons from being uploaded
		if (!elgg_trigger_plugin_hook('handle:icon:before', 'prototyper', $params, true)) {
			return $entity;
		}

		$result = hypeApps()->iconFactory->create($entity, elgg_extract('tmp_name', $value));

		$params = array(
			'field' => $this,
			'entity' => $entity,
			'icon_name' => $shortname,
			'value' => $future_value,
		);

		elgg_trigger_plugin_hook('handle:icon:after', 'prototyper', $params, $result);

		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getDataType() {
		return 'icon';
	}

}
