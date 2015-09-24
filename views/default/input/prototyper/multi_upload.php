<?php

$values = (array) elgg_extract('value', $vars, array());
unset($vars['value']);

if (!empty($values)) {
	$files = array();
	foreach ($values as $value) {
		if (is_numeric($value)) {
			$value = get_entity($value);
		}
		if ($value instanceof ElggFile) {
			$files[] = $value;
		}
	}

	if (!empty($files)) {
		elgg_require_js('framework/prototyper_multi_upload');
		echo elgg_view_entity_list($files, array(
			'full_view' => false,
			'list_type' => 'gallery',
			'gallery_class' => 'prototyper-multi-gallery prototyper-multi-edit-mode',
			'size' => 'small',
			'edit_mode' => true,
			'item_view' => 'prototyper/ui/file',
		));
	}
}

echo elgg_view('input/dropzone', $vars);