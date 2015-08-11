<?php

namespace hypeJunction\Prototyper;

class UI {

	/**
	 * Config
	 * @var Config
	 */
	private $config;

	/**
	 * Constructor
	 *
	 * @param \hypeJunction\Prototyper\Config $config
	 */
	public function __construct(Config $config) {
		$this->config = $config;
	}

	/**
	 * Returns a new template instance
	 * 
	 * @param string $data_type Data type
	 * @param string $type      Input type
	 * @return \hypeJunction\Prototyper\UI\Template
	 */
	public function getTemplate($data_type, $type) {
		$options = (array) $this->config->getType($data_type, $type);
		return new UI\Template($data_type, $type, elgg_extract('ui_sections', $options, array()));
	}

	/**
	 * Returns all registered templates
	 * @return array
	 */
	public function getTemplates() {
		$tempates = array();
		$types = $this->config->getTypes();
		foreach ($types as $type => $type_options) {
			if ($type == 'attribute') {
				continue;
			}
			foreach ($type_options as $subtype => $subtype_options) {
				$templates[$type][$subtype] = elgg_extract('ui_sections', $subtype_options, array());
			}
		}
		return $templates;
	}

	/**
	 * Constructs a new prototype from admin interface form
	 * @return array
	 */
	public function buildPrototypeFromInput() {
		$language = $this->config->get('default_language', 'en');

		$attribute = get_input('attribute', array());
		$field = get_input('field', array());
		$temp = array();

		$sort_priority = 10;

		foreach ($field as $uid => $options) {

			$shortname = elgg_extract('shortname', $options, $uid);
			$shortname = preg_replace("/[^A-Za-z0-9_]/", "_", $shortname);
			$shortname = strtolower($shortname);

			list($data_type, $input_type) = explode('::', elgg_extract('dit', $options, ''));
			unset($options['dit']);
			
			$required = (bool) elgg_extract('required', $options, false);
			$multiple = (bool) elgg_extract('multiple', $options, false);
			$admin_only = (bool) elgg_extract('admin_only', $options, false);
			$show_access = (bool) elgg_extract('show_access', $options, false);

			$relationship = elgg_extract('relationship', $options, array());
			unset($options['reationship']);
			
			$inverse_relationship = (bool) elgg_extract('inverse_relationship', $relationship, false);
			$bilateral = (bool) elgg_extract('bileteral', $relationship, false);

			$value = elgg_extract('value', $options);

			$hide_label = (bool) elgg_extract('hide_label', $options, false);
			$label = ($hide_label) ? false : elgg_extract('label', $options, '');
			unset($options['hide_label']);

			$hide_help = (bool) elgg_extract('hide_help', $options, false);
			$help = ($hide_help) ? false : elgg_extract('help', $options, '');
			unset($options['hide_help']);
			
			$priority = elgg_extract('priority', $options, $sort_priority);
			$sort_priority += 10;

			$options_values = elgg_extract('options_values', $options, array());
			unset($options['options_values']);

			$options_values_config = array();
			for ($i = 0; $i < count($options_values['value']); $i++) {
				$o_value = (string) $options_values['value'][$i];
				$o_label = (string) $options_values['label'][$language][$i];
				$options_values_config[$o_value] = array($language => $o_label);
			}

			$validation = elgg_extract('validation', $options, array());
			unset($options['validation']);

			$validation_rules = array();
			for ($i = 0; $i < count($validation['rule']); $i++) {
				$v_rule = $validation['rule'][$i];
				$v_expectation = $validation['expectation'][$i];
				$validation_rules[$v_rule] = $v_expectation;
			}

			$icon_sizes = array();
			$icon_sizes_conf = elgg_extract('data-icon-sizes', $options);
			$system_icon_sizes = array_keys((array) $icon_sizes_conf);
			if (is_array($icon_sizes_conf) && !empty($icon_sizes_conf)) {
				$keys = array_keys($icon_sizes_conf['name']);
				foreach ($keys as $key) {
					$name = $icon_sizes_conf['name'][$key];
					$w = (int) $icon_sizes_conf['w'][$key];
					$h = (int) $icon_sizes_conf['h'][$key];
					if (!$name || !$w || !$h || in_array($name, $system_icon_sizes)) {
						continue;
					}
					$icon_sizes[$name] = array(
						'name' => $name,
						'w' => $w,
						'h' => $h,
						'square' => ($w / $h) === 1,
						'upscale' => true,
						'croppable' => true,
						'metadata_name' => "{$name}_icon",
					);
				}
			}
			unset($options['data-icon-sizes']);

			$temp[$shortname] = array(
				'type' => $input_type,
				'data_type' => $data_type,
				'required' => $required,
				'admin_only' => $admin_only,
				'multiple' => $multiple,
				'show_access' => $show_access,
				'inverse_relationship' => $inverse_relationship,
				'bilateral' => $bilateral,
				'value' => $value,
				'label' => $label,
				'help' => $help,
				'priority' => $priority,
				'options_values' => (!empty($options_values_config)) ? $options_values_config : null,
				'validation_rules' => array_filter($validation_rules),
				'data-icon-sizes' => $icon_sizes,
			);

			if (in_array($input_type, array('checkboxes', 'radio'))) {
				$temp[$shortname]['options'] = array_flip($options_values_config);
			}

			// add all other options submitted with the form
			$temp[$shortname] = array_merge($options, $temp[$shortname]);
		}

		$fields = array();

		$title = elgg_extract('title', $attribute);
		$description = elgg_extract('description', $attribute);
		$access_id = elgg_extract('access_id', $attribute);

		if ($title && !array_key_exists('title', $temp)) {
			$fields['title'] = array(
				'required' => true,
			);
		}
		if ($description && !array_key_exists('description', $temp)) {
			$fields['description'] = array(
				'type' => 'longtext',
			);
		}

		foreach ($temp as $shortname => $options) {
			$fields[$shortname] = $options;
		}

		if ($access_id && !array_key_exists('access_id', $temp)) {
			$fields['access_id'] = array(
				'type' => 'access',
			);
		}
		
		return $fields;
	}

}
