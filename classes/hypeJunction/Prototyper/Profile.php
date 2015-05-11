<?php

namespace hypeJunction\Prototyper;

class Profile extends Prototype {

	/**
	 * Render an entity profile
	 * @return string HTML
	 */
	public function view() {

		$fields = $this->getFields();

		$output = '';

		// Prepare fields
		foreach ($fields as $field) {
			if ($field->getOutputView() === false) {
				continue;
			}
			if ($field->getType() == 'hidden') {
				continue;
			}
			$field_view = $field->viewOutput($this->getParams());
			if ($field_view) {
				$output .= elgg_format_element('div', array(
					'class' => 'prototyper-output',
						), $field_view);
			}
		}

		return $output;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHandler() {
		return self::CONTEXT_PROFILE;
	}

}
