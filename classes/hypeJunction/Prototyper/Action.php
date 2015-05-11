<?php

namespace hypeJunction\Prototyper;

use Exception;
use Field;
use IOException;

class Action extends Prototype {

	/**
	 * Validates the fields
	 *
	 * @param Field[] $fields Fields to validate
	 * @return bool
	 */
	public function validate($fields = array()) {

		$valid = true;

		foreach ($fields as $field) {
			if (!$field instanceof Field) {
				continue;
			}

			$validation = $field->validate();
			$this->setFieldValidationStatus($field->getShortname(), $validation);

			if (!$validation->isValid()) {
				$valid = false;
			}
		}

		return $valid;
	}

	/**
	 * Handles an action and provides user feedback
	 * @return void
	 */
	public function handle() {

		$this->saveStickyValues();

		try {
			$result = $this->update();
		} catch (ValidationException $ex) {
			register_error(elgg_echo('prototyper:validate:error'));
			forward(REFERER);
		} catch (IOException $ex) {
			register_error(elgg_echo('prototyper:io:error', array($ex->getMessage())));
			forward(REFERER);
		} catch (Exception $ex) {
			register_error(elgg_echo('prototyper:handle:error', array($ex->getMessage())));
			forward(REFERER);
		}

		if ($result) {
			$this->clearStickyValues();
			system_message(elgg_echo('prototyper:action:success'));
			forward($this->getEntity()->getURL());
		} else {
			register_error(elgg_echo('prototyper:action:error'));
			forward(REFERER);
		}
	}

	/**
	 * Updates entity information with user input values
	 *
	 * @return bool
	 * @throws ValidationException
	 */
	public function update() {

		$fields = $this->getFields();
		if ($this->validate($fields) === false) {
			throw new ValidationException('Invalid user input');
		}

		$this->getEntity()->save();
		$fields = $this->getFields();

		// first handle attributes
		foreach ($fields as $field) {
			if ($field->getDataType() == 'attribute') {
				$field->handle();
			}
		}

		foreach ($fields as $field) {
			if ($field->getDataType() != 'attribute') {
				$field->handle();
			}
		}

		$guid = $this->getEntity()->save();

		return ($guid);
	}

	/**
	 * Returns prototype handler
	 * @return string
	 */
	public function getHandler() {
		return self::CONTEXT_ACTION;
	}

}
