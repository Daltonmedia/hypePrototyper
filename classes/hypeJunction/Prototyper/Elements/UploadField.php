<?php

namespace hypeJunction\Prototyper\Elements;

class UploadField extends Field {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function getValues(\ElggEntity $entity) {
		return elgg_get_entities_from_metadata(array(
			'types' => 'object',
			'subtypes' => 'file',
			'container_guids' => (int) $entity->guid,
			'metadata_name_value_pairs' => array(
				'name' => 'prototyper_field',
				'value' => $this->getShortname(),
			)
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate(\ElggEntity $entity) {

		$shortname = $this->getShortname();
		$validation = new ValidationStatus();

		$value = $_FILES[$shortname];
		$error_type = elgg_extract('error', $value);

		$has_uploaded_file = ($error_type != UPLOAD_ERR_NO_FILE);
		if (!$has_uploaded_file) {
			if ($this->isRequired() && empty($entity->{"upload:$shortname"})) {
				$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
			}
		} else {
			$error = hypeApps()->uploader->getFriendlyUploadError($error_type);
			if ($error) {
				$validation->setFail($error);
			} else {
				$validation = $this->applyValidationRules($value, $validation, $entity);
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
			'upload_name' => $shortname,
			'future_value' => $future_value,
		);

		// Allow plugins to prevent files from being uploaded
		if (!elgg_trigger_plugin_hook('handle:upload:before', 'prototyper', $params, true)) {
			return $entity;
		}

		$result = hypeApps()->uploader->handle($shortname, array(
			'container_guid' => $entity->guid,
			'origin' => 'prototyper',
			'prototyper_field' => $shortname,
			'access_id' => $entity->access_id,
		));
		/* @var $result \ElggFile[] */

		$future_value = $result[0];

		if ($future_value instanceof \ElggFile) {
			$entity->{"upload:$shortname"} = time();
		}

		$params = array(
			'field' => $this,
			'entity' => $entity,
			'upload_name' => $shortname,
			'value' => $future_value,
		);

		elgg_trigger_plugin_hook('handle:upload:after', 'prototyper', $params, $result);

		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getDataType() {
		return 'file';
	}

}
