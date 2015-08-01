<?php

namespace hypeJunction\Prototyper\Elements;

class MetadataField extends Field {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function getValues(\ElggEntity $entity) {
		$values = array();
		$sticky = $this->getStickyValue();

		if ($sticky) {
			$keys = array_keys($sticky['value']);
			foreach ($keys as $i) {
				$md = new \stdClass();
				$md->id = $sticky['id'][$i];
				$md->name = $sticky['name'][$i];
				$md->value = $sticky['value'][$i];
				$md->access_id = $sticky['access_id'][$i];
				$md->owner_guid = $sticky['owner_guid'][$i];

				$values[$i] = $md;
			}
		} else if ($entity->guid) {
			$values = elgg_get_metadata(array(
				'guids' => (int) $entity->guid,
				'metadata_names' => $this->getShortname(),
				'limit' => 0,
			));
		}

		if (empty($values)) {
			$md = new \stdClass();
			$md->value = $this->getDefaultValue();
			$values = array($md);
		} else if (($this->getValueType() == 'tags' || !$this->isMultiple()) && sizeof($values) > 1) {
			$shortname = $this->getShortname();
			$md = new \stdClass();
			$md->id = $values[0]->id;
			$md->name = $shortname;
			$value = $entity->$shortname;
			if (is_array($value)) {
				$md->value = implode(', ', $value);
			} else {
				$md->value = $value;
			}
			$md->access_id = $values[0]->access_id;
			$md->owner_guid = $values[0]->owner_guid;
			$values = array($md);
		} else if (in_array($this->getValueType(), array('checkboxes', 'radio'))) {
			$shortname = $this->getShortname();
			$md = new \stdClass();
			$md->id = $values[0]->id;
			$md->name = $shortname;
			$value = $entity->$shortname;
			if (is_array($value)) {
				$md->value = $value;
			} else {
				$md->value = array($value);
			}
			$md->access_id = $values[0]->access_id;
			$md->owner_guid = $values[0]->owner_guid;
			$values = array($md);
		}

		return array_values($values);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate(\ElggEntity $entity) {

		$validation = new ValidationStatus();

		$metadata = get_input($this->getShortname(), array());
		$keys = array_keys(elgg_extract('value', $metadata, array()));

		if (empty($keys)) {
			if ($this->isRequired()) {
				$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
			}
		} else {
			foreach ($keys as $i) {
				if ($metadata['name'][$i] == $this->getShortname()) {
					if (is_string($metadata['value'][$i])) {
						$value = strip_tags($metadata['value'][$i]);
					} else {
						$value = $metadata['value'][$i];
					}
					if (is_null($value) || $value == '') {
						if ($this->isRequired()) {
							$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
						}
					} else {
						$validation = $this->applyValidationRules($value, $validation, $entity);
					}
				}
			}
		}

		return $validation;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {

		$shortname = $this->getShortname();

		if ($entity->guid) {
			$current_metadata = elgg_get_metadata(array(
				'guids' => (int) $entity->guid,
				'metadata_names' => $shortname,
				'limit' => 0,
			));

			if (!empty($current_metadata)) {
				foreach ($current_metadata as $md) {
					$current_metadata_ids[] = $md->id;
				}
			}
		}

		if (empty($current_metadata_ids)) {
			$current_metadata_ids = array();
		}

		$future_metadata = get_input($this->getShortname(), array());

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

		$future_metadata_ids = elgg_extract('id', $future_metadata, array());

		$to_delete = array_diff($current_metadata_ids, $future_metadata_ids);
		if (!empty($to_delete)) {
			elgg_delete_metadata(array(
				'guids' => (int) $entity->guid,
				'metadata_ids' => $to_delete,
				'limit' => 0,
			));
		}

		$keys = array_keys(elgg_extract('name', $future_metadata, array()));

		$ids = array();
		foreach ($keys as $i) {

			$id = $future_metadata['id'][$i];
			$name = $future_metadata['name'][$i];
			$value = $future_metadata['value'][$i];
			if ($this->getValueType() == 'tags') {
				$value = string_to_tag_array($value);
			}
			$access_id = $future_metadata['access_id'][$i];
			$owner_guid = $future_metadata['owner_guid'][$i];

			if (!is_array($value)) {
				if ($id) {
					update_metadata($id, $name, $value, '', $owner_guid, $access_id);
				} else {
					$id = create_metadata($entity->guid, $name, $value, '', $owner_guid, $access_id, true);
				}
				$ids[] = $id;
			} else {
				if ($id) {
					elgg_delete_metadata_by_id($id);
				}
				foreach ($value as $val) {
					$ids[] = create_metadata($entity->guid, $name, $val, '', $owner_guid, $access_id, true);
				}
			}
		}

		$params = array(
			'field' => $this,
			'entity' => $entity,
			'metadata_name' => $shortname,
			'value' => (count($ids)) ? elgg_get_metadata(array('metadata_ids' => $ids)) : array(),
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
