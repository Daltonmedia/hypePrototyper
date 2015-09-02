<?php

namespace hypeJunction\Prototyper\Elements;

class MetadataField extends Field {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function getValues(\ElggEntity $entity) {

		$values = $this->getStickyValue();

		if (!$values) {
			$shortname = $this->getShortname();
			$values = isset($entity->$shortname) ? $entity->$shortname : $this->getDefaultValue();
		}

		return $values;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate(\ElggEntity $entity) {

		$validation = new ValidationStatus();

		$values = get_input($this->getShortname(), '');

		if (is_array($values)) {
			foreach ($values as $key => $value) {
				if (is_null($value) || $value == '') {
					unset($values[$key]);
				}
			}
		}

		if (empty($values) && $this->isRequired()) {
			$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
		} else {
			if ($this->isMultiple()) {
				$values = (array) $values;
				foreach ($values as $value) {
					$validation = $this->applyValidationRules($value, $validation, $entity);
				}
			} else {
				$validation = $this->applyValidationRules($values, $validation, $entity);
			}
			
		}

		return $validation;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {

		$shortname = $this->getShortname();

		$current_metadata = $entity->$shortname;
		$future_metadata = get_input($shortname);

		if ($this->getType() == 'tags' && is_string($future_metadata)) {
			$future_metadata = string_to_tag_array($future_metadata);
		}
		
		$params = array(
			'field' => $this,
			'entity' => $entity,
			'metadata_name' => $shortname,
			'value' => $current_metadata,
			'future_value' => $future_metadata,
		);

		// Allow plugins to prevent metadata from being changed
		if (!elgg_trigger_plugin_hook('handle:metadata:before', 'prototyper', $params, true)) {
			return $entity;
		}

		$entity->$shortname = $future_metadata;

		$params = array(
			'field' => $this,
			'entity' => $entity,
			'metadata_name' => $shortname,
			'value' => $entity->$shortname,
			'previous_value' => $current_metadata,
		);

		elgg_trigger_plugin_hook('handle:metadata:after', 'prototyper', $params, true);

		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getDataType() {
		return 'metadata';
	}

}
