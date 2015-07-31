<?php

namespace hypeJunction\Prototyper;

class ActionController {

	private $config;
	private $prototype;
	private $entityFactory;

	/**
	 * Constructor
	 *
	 * @param Config $config
	 */
	public function __construct(Config $config, Prototype $prototype, EntityFactory $entityFactory) {
		$this->config = $config;
		$this->prototype = $prototype;
		$this->entityFactory = $entityFactory;
	}

	/**
	 * Returns a form element object
	 *
	 * @param mixed  $entity ElggEntity or an array of entity attributes
	 * @param string $action Action name (used as a plugin hook type)
	 * @param array  $params Additional context params to pass to the hook
	 * @return Elements\ActionController
	 */
	public function with($entity = array(), $action = 'all', array $params = array()) {

		$entity = $this->entityFactory->build($entity);
		$fields = $this->prototype->fields($entity, $action, $params);
		return new Elements\ActionController($entity, $action, $fields);
	}
}
