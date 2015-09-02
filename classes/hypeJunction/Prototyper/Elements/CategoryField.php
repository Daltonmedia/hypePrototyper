<?php

namespace hypeJunction\Prototyper\Elements;

class CategoryField extends RelationshipField {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function getValues(\ElggEntity $entity) {
		$sticky = $this->getStickyValue();
		if ($sticky) {
			return $sticky;
		}
		return hypeCategories()->model->getItemCategories($entity, array(), true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {

		// let hypeCategories handle the rest
		$shortname = $this->getShortname();
		if ($shortname !== 'categories') {
			set_input('categories', get_input($shortname));
		}

		// let the hooks handle the rest
		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getDataType() {
		return 'category';
	}

}
