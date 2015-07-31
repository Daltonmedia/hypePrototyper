<?php

namespace hypeJunction\Prototyper\Elements;

class ActionController {

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
	 * Full action script
	 * Validates the input, updates the entity and forwards users with feedback
	 * @return void
	 */
	public function handle() {

		try {
			if ($this->validate()) {
				$result = $this->update();
			}
		} catch (\hypeJunction\Exceptions\ActionValidationException $ex) {
			register_error(elgg_echo('prototyper:validate:error'));
			forward(REFERER);
		} catch (\IOException $ex) {
			register_error(elgg_echo('prototyper:io:error', array($ex->getMessage())));
			forward(REFERER);
		} catch (\Exception $ex) {
			register_error(elgg_echo('prototyper:handle:error', array($ex->getMessage())));
			forward(REFERER);
		}

		if ($result) {
			system_message(elgg_echo('prototyper:action:success'));
			forward($this->getEntity()->getURL());
		} else {
			register_error(elgg_echo('prototyper:action:error'));
			forward(REFERER);
		}
	}

	/**
	 * Validate user input
	 * @return bool
	 * @throws \hypeJunction\Exceptions\ActionValidationException
	 */
	public function validate() {

		hypePrototyper()->prototype->saveStickyValues($this->action);

		$valid = true;

		foreach ($this->fields as $field) {
			if (!$field instanceof Field) {
				continue;
			}

			$validation = $field->validate($this->entity);
			hypePrototyper()->prototype->setFieldValidationStatus($this->action, $field->getShortname(), $validation);

			if (!$validation->isValid()) {
				$valid = false;
			}
		}
		
		if (!$valid) {
			throw new \hypeJunction\Exceptions\ActionValidationException("Invalid input");
		}

		hypePrototyper()->prototype->clearStickyValues($this->action);
		return true;
	}

	/**
	 * Updates entity information with user input values
	 * @return \ElggEntity|false
	 */
	public function update() {

		hypePrototyper()->prototype->saveStickyValues($this->action);

		// first handle attributes
		foreach ($this->fields as $field) {
			if ($field->getDataType() == 'attribute') {
				$this->entity = $field->handle($this->entity);
			}
		}

		if (!$this->entity->save()) {
			return false;
		}

		foreach ($this->fields as $field) {
			if ($field->getDataType() !== 'attribute') {
				$this->entity = $field->handle($this->entity);
			}
		}

		if (!$this->entity->save()) {
			return false;
		}

		hypePrototyper()->prototype->clearStickyValues($this->action);
		return $this->entity;
	}

}
