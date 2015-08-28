<?php

$entity = elgg_extract('entity', $vars);
$name = elgg_extract('name', $vars);
$icon_sizes = elgg_extract('data-icon-sizes', $vars);

if (empty($icon_sizes)) {
	return;
}

elgg_load_css('jquery.cropper');

if (\hypeJunction\Integration::isElggVersionBelow('1.9.0')) {
	elgg_load_js('jquery.cropper');
	elgg_load_js('prototyper_cropper');
} else {
	elgg_require_js('framework/prototyper_cropper');
}

$ratios = array();
foreach ($icon_sizes as $icon_size) {
	$ratios[] = (int) $icon_size['w'] / (int) $icon_size['h'];
}
$ratios = array_unique($ratios);
foreach ($ratios as $ratio) {
	$mod = elgg_format_element('p', array('class' => 'elgg-text-help'), elgg_echo('prototyper:ui:cropper_instructions'));
	foreach (array('x1', 'y1', 'x2', 'y2') as $coord) {
		$mod .= elgg_view('input/hidden', array(
			'name' => "image_upload_crop_coords[{$name}][{$ratio}][{$coord}]",
			'value' => (int) $entity->{"_coord_{$ratio}_{$coord}"},
			"data-$coord" => true,
			'data-ratio' => $ratio,
		));
	}
	echo elgg_format_element('div', array(
		'class' => 'prototyper-image-upload-cropper',
		'data-ratio' => $ratio,
			), $mod);
}


