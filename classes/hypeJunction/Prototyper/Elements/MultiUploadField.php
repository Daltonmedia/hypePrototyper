<?php

namespace hypeJunction\Prototyper\Elements;

use ElggEntity;
use ElggFile;

class MultiUploadField extends Field {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function getValues(ElggEntity $entity) {
		return elgg_get_entities_from_metadata(array(
			'container_guids' => (int) $entity->guid,
			'metadata_name_value_pairs' => array(
				'name' => 'prototyper_field',
				'value' => $this->getShortname(),
			),
			'order_by_metadata' => array(
				'name' => 'priority',
				'direction' => 'ASC',
				'as' => 'integer',
			),
			'limit' => 0,
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate(ElggEntity $entity) {

		$validation = new ValidationStatus();

		$value = get_input($this->getShortname(), array());

		if ($this->isRequired() && (!$value || !count($value))) {
			$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
		}

		if (is_array($value)) {
			foreach ($value as $val) {
				$validation = $this->applyValidationRules($val, $validation, $entity);
			}
		}

		return $validation;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(ElggEntity $entity) {

		$shortname = $this->getShortname();
		
		$guids = (array) get_input($shortname);

		if (!empty($_FILES[$shortname])) {
			$uploads = hypeApps()->uploader->handle($shortname, array(
				'container_guid' => $entity->guid,
				'subtype' => $this->input_vars->subtype ? : 'file',
				'access_id' => $entity->access_id,
				'origin' => 'prototyper',
				'prototyper_field' => $shortname,
			));
			foreach ($uploads as $upload) {
				$guids[] = $upload->file->guid;
			}
		}

		foreach ($guids as $guid) {
			$file = get_entity($guid);
			if ($file) {
				$file->container_guid = $entity->guid;
				$file->access_id = $entity->access_id;
				$file->origin = 'prototyper';
				$file->prototyper_field = $shortname;
				if (!isset($file->priority)) {
					$file->priority = 0;
				}
				$file->save();
			}
		}

		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getDataType() {
		return 'file';
	}

}
