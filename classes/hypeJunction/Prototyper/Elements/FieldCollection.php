<?php

namespace hypeJunction\Prototyper\Elements;

class FieldCollection extends \hypeJunction\Prototyper\Structs\ArrayCollection {

	/**
	 * Sort by priority
	 * @return self
	 */
	public function sort() {
		$this->uasort(function($a, $b) {
			$priority_a = (int) $a->get('priority') ? : 500;
			$priority_b = (int) $b->get('priority') ? : 500;
			if ($priority_a == $priority_b) {
				return 0;
			}
			return ($priority_a < $priority_b) ? -1 : 1;
		});
		return $this;
	}

}
