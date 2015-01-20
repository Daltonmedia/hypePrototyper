<?php

/**
 * Handle annotation fields
 */
namespace hypeJunction\Prototyper;

use ElggAnnotation;
use stdClass;

class AnnotationField extends Field {

	/**
	 * Get new field instance
	 * @param string $shortname
	 * @param array|string $options
	 * @return \self
	 */
	public static function getInstance($shortname, $options = '') {
		$instance = new self($shortname, $options);
		$instance->data_type = 'annotation';

		return $instance;
	}

	/**
	 * Render an input view
	 * @param array $vars
	 * @return string
	 */
	function viewInput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('forms/prototyper/annotation', $vars);
	}

	/**
	 * Render an output view
	 * @param array $vars
	 * @return string
	 */
	function viewOutput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('output/prototyper/annotation', $vars);
	}

	/**
	 * Get annotation values
	 * @return stdClass
	 */
	public function getValues() {
		$sticky = $this->getStickyValue();
		if (!$sticky) {
			$values = elgg_get_annotations(array(
				'guids' => sanitize_int($this->getEntity()->guid),
				'annotation_names' => $this->getShortname(),
				'limit' => 0,
			));
			if (!is_array($values) || !count($values)) {
				$values = array(new ElggAnnotation);
			}
		} else {
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
		}
		return $values;
	}

	/**
	 * Validate values submitted by the user
	 * @return ValidationStatus
	 */
	public function validate() {

		$validation = new ValidationStatus();

		$annotation = get_input($this->getShortname(), array());
		$entries = count(elgg_extract('value', $annotation, array()));

		if ($this->isRequired() && !$entries) {
			$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
		}

		for ($i = 0; $i < $entries; $i++) {
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

		$current_annotation = elgg_get_annotations(array(
			'guids' => $this->getEntity()->guid,
			'annotation_names' => $shortname,
		));

		if (is_array($current_annotation) && count($current_annotation)) {
			foreach ($current_annotation as $md) {
				$current_annotation_ids[] = $md->id;
			}
		}

		if (!is_array($current_annotation_ids)) {
			$current_annotation_ids = array();
		}

		$future_annotation = get_input($this->getShortname(), array());

		$params = array(
			'field' => $this,
			'entity' => $this->getEntity(),
			'annotation_name' => $shortname,
			'value' => $current_annotation,
			'future_value' => $future_annotation,
		);

		// Allow plugins to prevent annotation from being changed
		if (!elgg_trigger_plugin_hook('handle:annotation:before', 'prototyper', $params, true)) {
			return true;
		}

		$future_annotation_ids = elgg_extract('id', $future_annotation, array());


		$to_delete = array_diff($current_annotation_ids, $future_annotation_ids);
		foreach ($to_delete as $id) {
			elgg_delete_annotation_by_id($id);
		}

		$entries = count($future_annotation['value']);

		$ids = array();
		for ($i = 0; $i < $entries; $i++) {

			$id = $future_annotation['id'][$i];
			$name = $future_annotation['name'][$i];
			$value = $future_annotation['value'][$i];
			$access_id = $future_annotation['access_id'][$i];
			$owner_guid = $future_annotation['owner_guid'][$i];

			if (!is_array($value)) {
				if ($id) {
					update_annotation($id, $name, $value, '', $owner_guid, $access_id);
				} else {
					$id = create_annotation($this->getEntity()->guid, $name, $value, '', $owner_guid, $access_id, true);
				}
				$ids[] = $id;
			} else {
				if ($id) {
					elgg_delete_annotation_by_id($id);
				}
				foreach ($value as $val) {
					$ids[] = create_annotation($this->getEntity()->guid, $name, $val, '', $owner_guid, $access_id, true);
				}
			}
		}

		$params = array(
			'field' => $this,
			'entity' => $this->getEntity(),
			'annotation_name' => $shortname,
			'value' => (count($ids)) ? elgg_get_annotations(array('ids' => $ids)) : array(),
			'previous_value' => $current_annotation,
		);

		return elgg_trigger_plugin_hook('handle:annotation:after', 'prototyper', $params, true);
	}


}
