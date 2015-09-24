<?php

elgg_load_css('lightbox');
elgg_load_js('lightbox');

$entity = elgg_extract('entity', $vars);
$edit_mode = elgg_extract('edit_mode', $vars, false);
$list_type = elgg_extract('list_type', $vars, 'gallery');
$size = elgg_extract('size', $vars, 'medium');

if (!$entity instanceof ElggFile) {
	return;
}

$image = elgg_view('output/img', array(
	'src' => $entity->getIconURL($size),
	'class' => 'prototyper-multi-item-preview',
	'alt' => $entity->title
		));

echo elgg_view('output/url', array(
	'text' => $image,
	'href' => $entity->getIconURL('master'),
	'class' => 'elgg-lightbox-photo',
	'rel' => $entity->container_guid,
));

if ($edit_mode) {
	echo elgg_view('output/url', array(
		'text' => elgg_view_icon('cursor-drag-arrow'),
		'class' => 'prototyper-multi-item-drag',
		'href' => false,
	));
	echo elgg_view('output/url', array(
		'text' => elgg_view_icon('delete'),
		'class' => 'prototyper-multi-item-delete',
		'href' => 'action/file/delete?guid=' . $entity->guid,
		'confirm' => true,
	));
}

