<?php

namespace hypeJunction\Prototyper;

/**
 * Prototype service
 */
class Prototype {

	const COOKIE_NAME = 'elgg_hP';

	/**
	 * Config
	 * @var Config
	 */
	private $config;

	/**
	 * Entity factory service
	 * @var EntityFactory
	 */
	private $entityFactory;

	/**
	 * Field factory service
	 * @var FieldFactory
	 */
	private $fieldFactory;

	/**
	 * Constructor
	 *
	 * @param \hypeJunction\Prototyper\Config $config
	 * @param \hypeJunction\Prototyper\EntityFactory $entityFactory
	 * @param \hypeJunction\Prototyper\FieldFactory $fieldFactory
	 */
	public function __construct(Config $config, EntityFactory $entityFactory, FieldFactory $fieldFactory) {
		$this->config = $config;
		$this->entityFactory = $entityFactory;
		$this->fieldFactory = $fieldFactory;
	}

	/**
	 * Returns a field collection
	 *
	 * @param mixed  $entity ElggEntity or an array of entity attributes
	 * @param string $action Action name (used as a plugin hook type)
	 * @param array  $params Additional context params to pass to the hook
	 * @return \hypeJunction\Prototyper\Elements\FieldCollection
	 */
	public function fields($entity = array(), $action = 'all', array $params = array()) {

		$fieldCollection = array();

		$entity = $this->entityFactory->build($entity);
		if ($entity instanceof \ElggEntity) {
			$params['entity'] = $entity;
			$fields = (array) elgg_trigger_plugin_hook('prototype', $action, $params, array());

			$attribute_names = $this->entityFactory->getAttributeNames($entity);
			if (!$entity->guid) {
				$fields['type'] = array('type' => 'hidden');
				$fields['subtype'] = array('type' => 'hidden');
				$fields['owner_guid'] = array('type' => 'hidden');
				$fields['container_guid'] = array('type' => 'hidden');
			} else {
				$fields['guid'] = array('type' => 'hidden');
			}

			foreach ($fields as $shortname => $field) {
				$field['entity_type'] = $entity->getType();
				$field['entity_subtype'] = $entity->getSubtype();
				if (empty($field['shortname'])) {
					$field['shortname'] = $shortname;
				}

				if (in_array($shortname, $attribute_names)) {
					$field['data_type'] = 'attribute';
					$field['class_name'] = Elements\AttributeField::CLASSNAME;
				}

				$fieldObj = $this->fieldFactory->build($field);
				if ($fieldObj instanceof Elements\Field) {
					$fieldCollection[] = $fieldObj;
				}
			}
		}

		return new Elements\FieldCollection($fieldCollection);
	}

	/**
	 * Store submitted sticky values
	 *
	 * @param string $action Action name
	 * @return bool
	 */
	public function saveStickyValues($action = '') {
		return elgg_make_sticky_form($action);
	}

	/**
	 * Clear sticky values
	 *
	 * @param string $action Action name
	 * @return type
	 */
	public function clearStickyValues($action = '') {
		return elgg_clear_sticky_form($action);
	}

	/**
	 * Get sticky values
	 *
	 * @param string $action Action name
	 * @return mixed
	 */
	public function getStickyValues($action = '') {
		return elgg_get_sticky_values($action);
	}

	/**
	 * Get form validation status
	 *
	 * @param string $action Action name
	 * @return type
	 */
	public function getValidationStatus($action = '') {

		$validation_status = null;

		if (isset($_SESSION['prototyper_validation'][$action])) {
			$validation_status = $_SESSION['prototyper_validation'][$action];
		}

		return $validation_status;
	}

	/**
	 * Save validation status of the field
	 *
	 * @param string                    $action     Action name
	 * @param string                    $shortname  Field name
	 * @param Elements\ValidationStatus $validation Status
	 * @return void
	 */
	public function setFieldValidationStatus($action = '', $shortname = '', Elements\ValidationStatus $validation = null) {

		if (!isset($_SESSION['prototyper_validation'][$action])) {
			$_SESSION['prototyper_validation'][$action] = array();
		}

		$_SESSION['prototyper_validation'][$action][$shortname] = array(
			'status' => $validation->getStatus(),
			'messages' => $validation->getMessages()
		);
	}

	/**
	 * Clear form validation
	 *
	 * @param string $action Action name
	 * @return void
	 */
	public function clearValidationStatus($action = '') {
		if (isset($_SESSION['prototyper_validation'][$action])) {
			unset($_SESSION['prototyper_validation'][$action]);
		}
	}

}
