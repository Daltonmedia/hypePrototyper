<?php

namespace hypeJunction\Prototyper\Elements;

class Profile {

	/**
	 * Elgg entity
	 * @var \ElggEntity 
	 */
	private $entity;

	/**
	 * Action name
	 * @var string 
	 */
	private $action;

	/**
	 * Collection of fields
	 * @var FieldCollection
	 */
	private $fields;

	/**
	 * Constructor
	 *
	 * @param \ElggEntity     $entity Entity
	 * @param string          $action Action name
	 * @param FieldCollection $fields Fields
	 */
	public function __construct(\ElggEntity $entity, $action, FieldCollection $fields) {
		$this->entity = $entity;
		$this->action = $action;
		$this->fields = $fields;
	}

	/**
	 * Filter fields
	 * 
	 * @param callable $filter
	 * @return self
	 */
	public function filter(callable $filter) {
		$this->fields = $this->fields->filter($filter);
		return $this;
	}

	/**
	 * Render an entity profile
	 *
	 * @param array $vars Vars to pass to teach field view
	 * @return string HTML
	 */
	public function view($vars = array()) {

		$output = '';

		$vars['entity'] = $this->entity;

		foreach ($this->fields as $field) {
			if (!$field instanceof Field) {
				continue;
			}
			if ($field->getOutputView() === false) {
				continue;
			}
			if ($field->getType() == 'hidden' || $field->getValueType() == 'hidden') {
				continue;
			}
			
			if ($field->isHiddenOnProfile()) {
				continue;
			}

			$output .= $field->viewOutput($vars);
		}

		return $output;
	}

}
