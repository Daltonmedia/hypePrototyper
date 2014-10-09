<?php

/**
 * Constructs a new entity prototype for use with forms and fields
 */
namespace hypeJunction\Prototyper;

use ElggEntity;
use ElggGroup;
use ElggObject;
use ElggUser;
use Exception;

class Prototype {

	/**
	 * Entity prototype
	 * @var ElggEntity
	 */
	protected static $entity;

	/**
	 * Additional params
	 * @var array 
	 */
	protected $params;

	/**
	 * Construct a new prototype
	 * Either GUID or type/subtype pair is required
	 *
	 * @param integer $guid		GUID of an existing entity to load or an existing entity
	 * @param array $attributes	Attributes of an entity to construct for new entities
	 * @param array $params		Additional params to pass to the hook
	 * @throws Exception
	 */
	public function __construct($guid = null, array $attributes = array(), $params = array()) {

		if ($guid) {
			if (elgg_instanceof($guid)) {
				$entity = $guid;
			} else {
				$entity = get_entity($guid);
			}
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
			foreach ($attributes as $key => $value) {
				if (in_array($key, self::getAttributeNames($entity))) {
					$entity->$key = $value;
				}
			}
		}

		if (!elgg_instanceof($entity)) {
			throw new Exception(get_class($this) . ' unable to construct a valid Elgg Entity from provided $guid and/or $type/$subtype pair');
		}

		self::$entity = $entity;
		$this->params = $params;
	}

	/**
	 * Get prototyped entity
	 * @return ElggObject|ElggGroup|ElggUser
	 */
	public static function getEntity() {
		return self::$entity;
	}

	/**
	 * Get entity type
	 * @return string
	 */
	public function getType() {
		return $this->getEntity()->getType();
	}

	/**
	 * Get entity subtype
	 * @return string
	 */
	public function getSubtype() {
		return $this->getEntity()->getSubtype();
	}

	/**
	 * Build a new form for a given action
	 * @param string $action
	 * @return Form
	 */
	public function form($action) {
		return Form::getInstance($action, $this->params);
	}

	/**
	 * Logic to apply in the action
	 * Validates and handles values supplied via the form and applies them to the entity
	 * @param string $action
	 * @param boolean $forward
	 * @return boolean|void		Returns TRUE|FALSE if $forward is set to true, otherwise points the header to a new location
	 */
	public function action($action, $forward = false) {
		$form = $this->form($action);
		$form->saveStickyValues();
		
		if ($form->validate() === false) {
			register_error(elgg_echo('prototyper:validate:error'));
			forward(REFERER);
		}

		$result = $form->handle();
		if ($result) {
			system_message(elgg_echo('prototyper:action:success'));
			$forward_url = $form->getEntity()->getURL();
		} else {
			register_error(elgg_echo('prototyper:action:error'));
			$forward_url = REFERER;
		}
		
		$form->clearStickyValues();

		if (!$forward) {
			return $result;
		}

		if ($forward === true) {
			forward($forward_url);
		}

		forward($forward);
		
	}

	function profile($action, $params = array()) {
		$form = $this->form($action);
		return $form->viewProfile($params);
	}
	/**
	 * Get names of attributes for the entity
	 * @param ElggEntity $entity
	 * @return array
	 */
	static function getAttributeNames($entity) {
		if (!elgg_instanceof($entity)) {
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
