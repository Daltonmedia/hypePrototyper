<?php

namespace hypeJunction\Prototyper;

use ElggEntity;
use ElggGroup;
use ElggObject;
use ElggUser;
use InvalidParameterException;

/**
 * Entity factory
 */
class Entity {

	/**
	 * Consructs an ElggEntity from a guid or attributes
	 *
	 * @param mixed $guid       ElggEntity or GUID or null if using attributes
	 * @param mixed $attributes ElggEntity, GUID or entity attributes, including type and subtype
	 * @return ElggEntity
	 * @throws InvalidParameterException
	 */
	public static function factory($guid = null, $attributes = null) {

		if ($guid instanceof ElggEntity) {
			$entity = $guid;
		} else if (is_numeric($guid)) {
			$entity = get_entity($guid);
		} else if (is_array($attributes)) {
			if (isset($attributes['guid'])) {
				unset($attributes['guid']);
				$entity = get_entity($attributes['guid']);
			} else {
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
							$entity = new ElggObject();
							$entity->subtype = $subtype;
							break;

						case 'user' :
							$entity = new ElggUser();
							$entity->subtype = $subtype;
							break;

						case 'group' :
							$entity = new ElggGroup();
							$entity->subtype = $subtype;
							break;
					}
				}
			}
			foreach ($attributes as $key => $value) {
				if (in_array($key, self::getAttributeNames($entity))) {
					$entity->$key = $value;
				}
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
	public static function getAttributeNames($entity) {
		if (!$entity instanceof ElggEntity) {
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
