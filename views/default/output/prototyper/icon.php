<?php

namespace hypeJunction\Prototyper;

$field = elgg_extract('field', $vars);

if (!$field instanceof IconField) {
	return true;
}

$entity = $field->getEntity();
$name = $field->getShortname();

if (!$entity || !$name) {
	return true;
}

echo elgg_view_entity_icon($entity, $vars['size'], $vars);