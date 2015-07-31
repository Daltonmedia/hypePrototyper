<?php

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof hypeJunction\Prototyper\Elements\ImageField) {
	return;
}

$name = $field->getShortname();

if (!$entity || !$name) {
	return;
}
$guids = (array)$entity->$name;

foreach ($guids as $guid) {
	$file = get_entity($guid);
	if (!($file instanceof ElggFile)) {
		continue;
	}
	
	echo elgg_view('output/img', array(
		'src' => elgg_normalize_url('file/download/' . $guid)
	));
}