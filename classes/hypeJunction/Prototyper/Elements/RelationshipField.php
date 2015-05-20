<?php

namespace hypeJunction\Prototyper\Elements;

class RelationshipField extends Field {

	const CLASSNAME = __CLASS__;

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
	 * Display access input
	 * @return boolean
	 */
	public function hasAccessInput() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValues(\ElggEntity $entity) {
		$sticky = $this->getStickyValue();
		$values = array();
		if (!$sticky) {
			if ($entity->guid) {
				$entities = elgg_get_entities_from_relationship(array(
					'relationship_guid' => $entity->guid,
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {

		$shortname = $this->getShortname();

		$current_relationships = elgg_get_entities_from_relationship(array(
			'relationship_guid' => (int) $entity->guid,
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
			'entity' => $entity,
			'relationship' => $shortname,
			'value' => $current_relationships_ids,
			'future_value' => $future_relationships_ids,
		);

		// Allow plugins to prevent relationship from being changed
		if (!elgg_trigger_plugin_hook('handle:relationship:before', 'prototyper', $params, true)) {
			return $entity;
		}

		$to_delete = array_diff($current_relationships_ids, $future_relationships_ids);
		foreach ($to_delete as $guid) {
			if (!$this->inverse_relationship || $this->bilateral) {
				remove_entity_relationship($entity->guid, $shortname, $guid);
			}

			if ($this->inverse_relationship || $this->bilateral) {
				remove_entity_relationship($guid, $shortname, $entity->guid);
			}
		}

		foreach ($future_relationships_ids as $guid) {
			if (!$this->inverse_relationship || $this->bilateral) {
				if (!check_entity_relationship($entity->guid, $shortname, $guid)) {
					add_entity_relationship($entity->guid, $shortname, $guid);
				}
			}

			if ($this->inverse_relationship || $this->bilateral) {
				if (!check_entity_relationship($guid, $shortname, $entity->guid)) {
					add_entity_relationship($guid, $shortname, $entity->guid);
				}
			}
		}

		$params = array(
			'field' => $this,
			'entity' => $entity,
			'relationship_name' => $shortname,
			'value' => $future_relationships_ids,
			'previous_value' => $current_relationships_ids,
		);

		elgg_trigger_plugin_hook('handle:relationship:after', 'prototyper', $params, true);

		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getDataType() {
		return 'relationship';
	}

}
