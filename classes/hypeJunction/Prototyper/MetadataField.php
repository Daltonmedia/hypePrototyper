<?php

/**
 * Handle metadata fields
 */

namespace hypeJunction\Prototyper;

use ElggMetadata;
use stdClass;

class MetadataField extends Field {

	/**
	 * {@inheritdoc}
	 */
	public static function factory($options = array(), $entity = null) {
		$shortname = elgg_extract('shortname', $options);

		$instance = new self($shortname);
		$instance->setEntity($entity);
		$instance->setOptions($options);

		$instance->data_type = 'metadata';

		return $instance;
	}

	/**
	 * Render an input view
	 * @param array $vars
	 * @return string
	 */
	public function viewInput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('forms/prototyper/metadata', $vars);
	}

	/**
	 * Render an output
	 * @param array $vars
	 * @return string
	 */
	public function viewOutput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('output/prototyper/metadata', $vars);
	}

	/**
	 * Get metadata values
	 * @return stdClass
	 */
	public function getValues() {
		$sticky = $this->getStickyValue();

		if ($sticky) {
			$entries = count($sticky['value']);
			for ($i = 0; $i < $entries; $i++) {
				$md = new stdClass();
				$md->id = $sticky['id'][$i];
				$md->name = $sticky['name'][$i];
				$md->value = $sticky['value'][$i];
				$md->access_id = $sticky['access_id'][$i];
				$md->owner_guid = $sticky['owner_guid'][$i];

				$values[$i] = $md;
			}
		} else if ($this->getEntity()->guid) {
			$values = elgg_get_metadata(array(
				'guids' => (int) $this->getEntity()->guid,
				'metadata_names' => $this->getShortname(),
				'limit' => 0,
			));
		}

		if (!is_array($values) || !count($values)) {
			$values = array(new ElggMetadata);
		} else if (($this->getValueType() == 'tags' || is_array($values)) && !$this->isMultiple()) {
			$shortname = $this->getShortname();
			$md = new stdClass();
			$md->id = $values[0]->id;
			$md->name = $shortname;
			$value = $this->getEntity()->$shortname;
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
			$md = new stdClass();
			$md->id = $values[0]->id;
			$md->name = $shortname;
			$value = $this->getEntity()->$shortname;
			if (is_array($value)) {
				$md->value = $value;
			} else {
				$md->value = array($value);
			}
			$md->access_id = $values[0]->access_id;
			$md->owner_guid = $values[0]->owner_guid;
			$values = array($md);
		}
		return $values;
	}

	/**
	 * Validate values submitted by the user
	 * @return ValidationStatus
	 */
	public function validate() {

		$validation = new ValidationStatus();

		$metadata = get_input($this->getShortname(), array());
		$entries = count(elgg_extract('value', $metadata, array()));

		if ($this->isRequired() && !$entries) {
			$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
		}

		for ($i = 0; $i < $entries; $i++) {
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
					$validation = $this->applyValidationRules($value, $validation);
				}
			}
		}

		return $validation;
	}

	/**
	 * Handle values submitted via the form
	 */
	public function handle() {

		$shortname = $this->getShortname();

		$current_metadata = elgg_get_metadata(array(
			'guids' => (int) $this->getEntity()->guid,
			'metadata_names' => $shortname,
		));

		if (is_array($current_metadata) && count($current_metadata)) {
			foreach ($current_metadata as $md) {
				$current_metadata_ids[] = $md->id;
			}
		}

		if (!is_array($current_metadata_ids)) {
			$current_metadata_ids = array();
		}

		$future_metadata = get_input($this->getShortname(), array());

		$params = array(
			'field' => $this,
			'entity' => $this->getEntity(),
			'metadata_name' => $shortname,
			'value' => $current_metadata,
			'future_value' => $future_metadata,
		);

		// Allow plugins to prevent metadata from being changed
		if (!elgg_trigger_plugin_hook('handle:metadata:before', 'prototyper', $params, true)) {
			return true;
		}

		$future_metadata_ids = elgg_extract('id', $future_metadata, array());

		$to_delete = array_diff($current_metadata_ids, $future_metadata_ids);
		foreach ($to_delete as $id) {
			elgg_delete_metadata_by_id($id);
		}

		$entries = count($future_metadata['value']);
		$keys = array_keys($future_metadata['name']);

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
					$id = create_metadata($this->getEntity()->guid, $name, $value, '', $owner_guid, $access_id, true);
				}
				$ids[] = $id;
			} else {
				if ($id) {
					elgg_delete_metadata_by_id($id);
				}
				foreach ($value as $val) {
					$ids[] = create_metadata($this->getEntity()->guid, $name, $val, '', $owner_guid, $access_id, true);
				}
			}
		}

		$params = array(
			'field' => $this,
			'entity' => $this->getEntity(),
			'metadata_name' => $shortname,
			'value' => (count($ids)) ? elgg_get_metadata(array('metadata_ids' => $ids)) : array(),
			'previous_value' => $current_metadata,
		);
		
		return elgg_trigger_plugin_hook('handle:metadata:after', 'prototyper', $params, true);
	}

}
