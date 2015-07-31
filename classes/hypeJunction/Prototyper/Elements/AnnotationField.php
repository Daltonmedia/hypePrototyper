<?php

namespace hypeJunction\Prototyper\Elements;

class AnnotationField extends Field {

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
				$ann = new \stdClass();
				$ann->id = $sticky['id'][$i];
				$ann->name = $sticky['name'][$i];
				$ann->value = $sticky['value'][$i];
				$ann->access_id = $sticky['access_id'][$i];
				$ann->owner_guid = $sticky['owner_guid'][$i];

				$values[$i] = $ann;
			}
		} else if ($entity->guid) {
			$values = elgg_get_annotations(array(
				'guids' => (int) $entity->guid,
				'metadata_names' => $this->getShortname(),
				'limit' => 0,
			));
		}

		if (empty($values)) {
			$ann = new \stdClass();
			$ann->value = $this->getDefaultValue();
			$values = array($ann);
		} else if (($this->getValueType() == 'tags' || !$this->isMultiple()) && sizeof($values) > 1) {
			$shortname = $this->getShortname();
			$ann = new \stdClass();
			$ann->id = $values[0]->id;
			$ann->name = $shortname;
			$value = $entity->$shortname;
			if (is_array($value)) {
				$ann->value = implode(', ', $value);
			} else {
				$ann->value = $value;
			}
			$ann->access_id = $values[0]->access_id;
			$ann->owner_guid = $values[0]->owner_guid;
			$values = array($ann);
		} else if (in_array($this->getValueType(), array('checkboxes', 'radio'))) {
			$shortname = $this->getShortname();
			$ann = new \stdClass();
			$ann->id = $values[0]->id;
			$ann->name = $shortname;
			$value = $entity->$shortname;
			if (is_array($value)) {
				$ann->value = $value;
			} else {
				$ann->value = array($value);
			}
			$ann->access_id = $values[0]->access_id;
			$ann->owner_guid = $values[0]->owner_guid;
			$values = array($ann);
		}

		return array_values($values);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate(\ElggEntity $entity) {

		$validation = new ValidationStatus();

		$annotation = get_input($this->getShortname(), array());
		$keys = array_keys(elgg_extract('value', $annotation, array()));

		if (empty($keys)) {
			if ($this->isRequired()) {
				$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
			}
		} else {
			foreach ($keys as $i) {
				if ($annotation['name'][$i] == $this->getShortname()) {
					if (is_string($annotation['value'][$i])) {
						$value = strip_tags($annotation['value'][$i]);
					} else {
						$value = $annotation['value'][$i];
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

		$current_annotations = elgg_get_annotations(array(
			'guids' => (int) $entity->guid,
			'annotation_names' => $shortname,
		));

		if (is_array($current_annotations) && count($current_annotations)) {
			foreach ($current_annotations as $ann) {
				$current_annotations_ids[] = $ann->id;
			}
		}

		if (!is_array($current_annotations_ids)) {
			$current_annotations_ids = array();
		}

		$future_annotations = get_input($this->getShortname(), array());

		$params = array(
			'field' => $this,
			'entity' => $entity,
			'annotation_name' => $shortname,
			'value' => $current_annotations,
			'future_value' => $future_annotations,
		);

		// Allow plugins to prevent annotation from being changed
		if (!elgg_trigger_plugin_hook('handle:annotation:before', 'prototyper', $params, true)) {
			return $entity;
		}

		$future_annotations_ids = elgg_extract('id', $future_annotations, array());

		$to_delete = array_diff($current_annotations_ids, $future_annotations_ids);
		foreach ($to_delete as $id) {
			elgg_delete_annotation_by_id($id);
		}

		$keys = array_keys(elgg_extract('name', $future_annotations, array()));

		$ids = array();
		foreach ($keys as $i) {

			$id = $future_annotations['id'][$i];
			$name = $future_annotations['name'][$i];
			$value = $future_annotations['value'][$i];
			if ($this->getValueType() == 'tags') {
				$value = string_to_tag_array($value);
			}
			$access_id = $future_annotations['access_id'][$i];
			$owner_guid = $future_annotations['owner_guid'][$i];

			if (!is_array($value)) {
				if ($id) {
					update_annotation($id, $name, $value, '', $owner_guid, $access_id);
				} else {
					$id = create_annotation($entity->guid, $name, $value, '', $owner_guid, $access_id);
				}
				$ids[] = $id;
			} else {
				if ($id) {
					elgg_delete_annotation_by_id($id);
				}
				foreach ($value as $val) {
					$ids[] = create_annotation($entity->guid, $name, $val, '', $owner_guid, $access_id);
				}
			}
		}

		$params = array(
			'field' => $this,
			'entity' => $entity,
			'annotation_name' => $shortname,
			'value' => (count($ids)) ? elgg_get_annotations(array('annotation_ids' => $ids)) : array(),
			'previous_value' => $current_annotations,
		);

		elgg_trigger_plugin_hook('handle:annotation:after', 'prototyper', $params, true);

		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getDataType() {
		return 'annotation';
	}

}
