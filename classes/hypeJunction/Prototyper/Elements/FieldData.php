<?php

namespace hypeJunction\Prototyper\Elements;

interface FieldData {

	/**
	 * Returns default value
	 * @return mixed
	 */
	public function getDefaultValue();

	/**
	 * Extract field values from an entity
	 *
	 * @param \ElggEntity $entity Entity
	 * @return mixed
	 */
	public function getValues(\ElggEntity $entity);

	/**
	 * Apply input values to an entity
	 *
	 * @param \ElggEntity $entity Entity
	 * @return \ElggEntity
	 */
	public function handle(\ElggEntity $entity);
}
