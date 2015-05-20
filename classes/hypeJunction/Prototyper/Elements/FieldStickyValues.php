<?php

namespace hypeJunction\Prototyper\Elements;

interface FieldStickyValues {

	/**
	 * Set a sticky value
	 *
	 * @param mixed $value
	 * @return self
	 */
	public function setStickyValue($value = '');

	/**
	 * Get a sticky value
	 *
	 * @return mixed
	 */
	public function getStickyValue();
}
