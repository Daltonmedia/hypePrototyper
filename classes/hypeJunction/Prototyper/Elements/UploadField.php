<?php

namespace hypeJunction\Prototyper\Elements;

use ElggEntity;
use ElggFile;

class UploadField extends Field {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function getValues(ElggEntity $entity) {
		$files = elgg_get_entities_from_metadata(array(
			'types' => 'object',
			'subtypes' => 'file',
			'container_guids' => (int) $entity->guid,
			'metadata_name_value_pairs' => array(
				'name' => 'prototyper_field',
				'value' => $this->getShortname(),
			),
			'limit' => 1,
		));
		return ($files) ? $files[0] : false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate(ElggEntity $entity) {

		$shortname = $this->getShortname();
		$validation = new ValidationStatus();

		$value = elgg_extract($shortname, $_FILES, array());
		$error_type = elgg_extract('error', $value);

		$has_uploaded_file = $error_type != UPLOAD_ERR_NO_FILE;
		if (!$has_uploaded_file) {
			if ($this->isRequired() && empty($this->getValues($entity))) {
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
	public function handle(ElggEntity $entity) {

		$shortname = $this->getShortname();
		$value = elgg_extract($shortname, $_FILES, array());
		$error_type = elgg_extract('error', $value);

		$has_uploaded_file = $error_type != UPLOAD_ERR_NO_FILE;
		if (!$has_uploaded_file) {
			return $entity;
		}

		$future_value = $value['tmp_name'];
		
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

		$previous_upload = $this->getValues($entity);
		if ($previous_upload instanceof ElggFile) {
			$previous_upload->delete();
		}
		
		$result = hypeApps()->uploader->handle($shortname, array(
			'container_guid' => $entity->guid,
			'origin' => 'prototyper',
			'prototyper_field' => $shortname,
			'access_id' => $entity->access_id,
		));
		/* @var $result ElggFile[] */

		$future_value = $result[0];

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
