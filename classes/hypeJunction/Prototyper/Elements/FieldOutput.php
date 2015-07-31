<?php

namespace hypeJunction\Prototyper\Elements;

interface FieldOutput {

	/**
	 * Render output
	 *
	 * @param array $vars Output view vars
	 * @return string
	 */
	public function viewOutput($vars = array());

		/**
	 * Get name of the output view
	 * @return string
	 */
	public function getOutputView();

		/**
	 * Get vars to the be passed to the output
	 *
	 * @param \ElggEntity $entity Entity
	 * @return array
	 */
	public function getOutputVars(\ElggEntity $entity);

}
