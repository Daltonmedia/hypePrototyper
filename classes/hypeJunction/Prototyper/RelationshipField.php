<?php

/**
 * Handle relationship fields
 */

namespace hypeJunction\Prototyper;

use ElggEntity;
use stdClass;

class RelationshipField extends Field {

	/**
	 * Inverse relationship
	 * @var boolean 
	 */
	protected $inverse_relationship = false;

	/**
	 * Bilaterial relationship
	 * @var boolean
	 */
	protected $bilateral = false;

	/**
	 * Get new field instance
	 * @param string $shortname
	 * @param array|string $options
	 * @return \self
	 */
	public static function getInstance($shortname, $options = '') {
		$instance = new self($shortname, $options);
		if (isset($instance->input_vars->inverse_relationship)) {
			$instance->inverse_relationship = $instance->input_vars->inverse_relationship;
			unset($instance->input_vars->inverse_relationship);
		}
		if (isset($instance->input_vars->bilateral)) {
			$instance->inverse_relationship = $instance->input_vars->bilateral;
			unset($instance->input_vars->bilateral);
		}
		if (isset($instance->multiple)) {
			$instance->input_vars->multiple = $instance->multiple;
		}
		$instance->multiple = false;
		$instance->data_type = 'relationship';
		$instance->value_type = 'entity';

		return $instance;
	}

	/**
	 * Display access input
	 * @return boolean
	 */
	public function hasAccessInput() {
		return false;
	}

	/**
	 * Render an input view
	 * @param array $vars
	 * @return string
	 */
	function viewInput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('forms/prototyper/relationship', $vars);
	}

	/**
	 * Render an output
	 * @param array $vars
	 * @return string
	 */
	function viewOutput($vars = array()) {
		$vars['field'] = $this;
		return elgg_view('output/prototyper/relationship', $vars);
	}

	/**
	 * Get relationship values
	 * @return stdClass
	 */
	public function getValues() {
		$sticky = $this->getStickyValue();
		$values = array();
		if (!$sticky) {
			if ($this->getEntity()->guid) {
				$entities = elgg_get_entities_from_relationship(array(
					'relationship_guid' => $this->getEntity()->guid,
					'relationship' => $this->getShortname(),
					'inverse_relationship' => $this->inverse_relationship,
					'limit' => 0,
					'callback' => false,
				));
				if (is_array($entities) && count($entities)) {
					foreach ($entities as $entity) {
						$values[] = $entity->guid;
					}
				}
			}
		} else {
			$values = $sticky;
		}

		return $values;
	}

	/**
	 * Validate values submitted by the user
	 * @return ValidationStatus
	 */
	public function validate() {

		$validation = new ValidationStatus();

		$value = get_input($this->getShortname(), array());

		if ($this->isRequired() && (!$value || !count($value))) {
			$validation->setFail(elgg_echo('prototyper:validate:error:required', array($this->getLabel())));
		}

		if (is_array($value)) {
			foreach ($value as $val) {
				$validation = $this->applyValidationRules($val, $validation);
			}
		}

		return $validation;
	}

	/**
	 * Handle values submitted via the form
	 */
	public function handle() {

		$shortname = $this->getShortname();

		$current_relationships = elgg_get_entities_from_relationship(array(
			'relationship_guid' => $this->getEntity()->guid,
			'relationship' => $shortname,
			'inverse_relationship' => $this->inverse_relationship,
			'limit' => 0,
			'callback' => false,
		));

		$current_relationships_ids = array();
		if (is_array($current_relationships) && count($current_relationships)) {
			foreach ($current_relationships as $rel) {
				$current_relationships_ids[] = $rel->guid;
			}
		}

		$future_relationships_ids = get_input($this->getShortname(), array());

		if (!is_array($future_relationships_ids)) {
			$future_relationships_ids = array();
		}

		$params = array(
			'field' => $this,
			'entity' => $this->getEntity(),
			'relationship' => $shortname,
			'value' => $current_relationships_ids,
			'future_value' => $future_relationships_ids,
		);

		// Allow plugins to prevent relationship from being changed
		if (!elgg_trigger_plugin_hook('handle:relationship:before', 'prototyper', $params, true)) {
			return true;
		}

		$to_delete = array_diff($current_relationships_ids, $future_relationships_ids);
		foreach ($to_delete as $guid) {
			if (!$this->inverse_relationship || $this->bilateral) {
				remove_entity_relationship($this->getEntity()->guid, $shortname, $guid);
			}

			if ($this->inverse_relationship || $this->bilateral) {
				remove_entity_relationship($guid, $shortname, $this->getEntity()->guid);
			}
		}

		foreach ($future_relationships_ids as $guid) {
			if (!$this->inverse_relationship || $this->bilateral) {
				if (!check_entity_relationship($this->getEntity()->guid, $shortname, $guid)) {
					add_entity_relationship($this->getEntity()->guid, $shortname, $guid);
				}
			}

			if ($this->inverse_relationship || $this->bilateral) {
				if (!check_entity_relationship($guid, $shortname, $this->getEntity()->guid)) {
					add_entity_relationship($guid, $shortname, $this->getEntity()->guid);
				}
			}
		}

		$params = array(
			'field' => $this,
			'entity' => $this->getEntity(),
			'relationship_name' => $shortname,
			'value' => $future_relationships_ids,
			'previous_value' => $current_relationships_ids,
		);

		return elgg_trigger_plugin_hook('handle:relationship:after', 'prototyper', $params, true);
	}

}
