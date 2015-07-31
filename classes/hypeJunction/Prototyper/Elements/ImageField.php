<?php

namespace hypeJunction\Prototyper\Elements;

class ImageField extends Field {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function getValues(\ElggEntity $entity) {
		$name = $this->getShortname();
		return $entity->$name;
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

		$value = (array) get_input($shortname, array());
		$error_type = elgg_extract('error', $value);

		if ($this->isRequired() && !$value) {
			$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
		}

		return $validation;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {

		$shortname = $this->getShortname();
		
		$guids = (array) get_input($shortname, array());

		if ($guids) {
			foreach ($guids as $key => $guid) {
				$ia = elgg_set_ignore_access(true);
				$file = get_entity($guid);
				if (!($file instanceof \ElggFile)) {
					unset($guids[$key]);
					continue;
				}
				
				if ($file->access_id != ACCESS_PUBLIC) {
					$file->access_id = ACCESS_PUBLIC;
					$file->save();
				}
				elgg_set_ignore_access($ia);
			}
		}

		$entity->$shortname = $guids;

		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getDataType() {
		return 'image';
	}

}
