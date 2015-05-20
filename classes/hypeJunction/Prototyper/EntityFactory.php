<?php

namespace hypeJunction\Prototyper;

/**
 * Entity factory
 */
class EntityFactory {

	/**
	 * Returns an entity from its guid
	 * 
	 * @param int $guid GUID
	 * @return \ElggEntity|false
	 */
	public function get($guid) {
		return get_entity($guid);
	}

	/**
	 * Builds an ElggEntity from a set of attributes
	 *
	 * @param mixed $attributes ElggEntity, GUID or entity attributes, including type and subtype
	 * @return ElggEntity
	 */
	public function build($attributes = null) {

		if ($attributes instanceof \ElggEntity) {
			return $attributes;
		}

		if (is_numeric($attributes)) {
			return $this->get($attributes);
		}

		$attributes = (array) $attributes;

		if (!empty($attributes['guid'])) {
			return $this->get($attributes['guid']);
		}


		$type = elgg_extract('type', $attributes, 'object');
		$subtype = elgg_extract('subtype', $attributes, ELGG_ENTITIES_ANY_VALUE);

		unset($attributes['type']);
		unset($attributes['subtype']);

		$class = get_subtype_class($type, $subtype);
		if (class_exists($class)) {
			$entity = new $class();
		} else {
			switch ($type) {
				case 'object' :
					$entity = new \ElggObject();
					$entity->subtype = $subtype;
					break;

				case 'user' :
					$entity = new \ElggUser();
					$entity->subtype = $subtype;
					break;

				case 'group' :
					$entity = new \ElggGroup();
					$entity->subtype = $subtype;
					break;
			}
		}

		foreach ($attributes as $key => $value) {
			if (in_array($key, $this->getAttributeNames($entity))) {
				$entity->$key = $value;
			}
		}

		return $entity;
	}

	/**
	 * Returns attribute names for an entity
	 *
	 * @param ElggEntity $entity Entity
	 * @return array
	 */
	public function getAttributeNames($entity) {
		
		if (!$entity instanceof \ElggEntity) {
			return array();
		}

		$default = array(
			'guid',
			'type',
			'subtype',
			'owner_guid',
			'container_guid',
			'site_guid',
			'access_id',
			'time_created',
			'time_updated',
			'last_action',
			'enabled',
		);

		switch ($entity->getType()) {
			case 'user';
				$attributes = array(
					'name',
					'username',
					'email',
					'language',
					'banned',
					'admin',
					'password',
					'salt'
				);
				break;

			case 'group' :
				$attributes = array(
					'name',
					'description',
				);
				break;

			case 'object' :
				$attributes = array(
					'title',
					'description',
				);
				break;
		}

		return array_merge($default, $attributes);
	}

}
