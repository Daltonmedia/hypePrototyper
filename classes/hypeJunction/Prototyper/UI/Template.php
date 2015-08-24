<?php

namespace hypeJunction\Prototyper\UI;

class Template {

	protected $data_type;
	protected $input_type;
	
	protected $sections = array(
		'required' => true,
		'adminonly' => true,
		'access' => true,
		'multiple' => true,
		'label' => true,
		'help' => true,
		'optionsvalues' => false,
		'relationship' => false,
		'validation' => true,
		'value' => true,
	);

	public function __construct($data_type = 'metadata', $input_type = 'text', $params = array()) {
		$this->data_type = $data_type;
		$this->input_type = $input_type;
		$sections = (array) elgg_extract('ui_sections', $params, array());
		foreach ($this->sections as $name => $default) {
			$show = elgg_extract($name, $sections, $default);
			$this->$name = ($show) ? 'visible' : 'hidden';
		}
	}

}
