<?php

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof hypeJunction\Prototyper\Elements\IconField) {
	return;
}

$name = $field->getShortname();

if (!$entity || !$name) {
	return;
}

echo elgg_view_entity_icon($entity, $vars['size'], $vars);