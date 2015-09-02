<?php

use hypeJunction\Prototyper\Elements\Field;

$field = elgg_extract('field', $vars);
if (!$field instanceof Field) {
	return;
}

$label = $field->getLabel();
$required = $field->isRequired();

if (!$label) {
	return;
}

$label_attrs = array();
if ($required) {
	$label_attrs = array(
		'class' => 'required',
		'title' => elgg_echo('prototyper:required')
	);
}

echo elgg_format_element('label', $label_attrs, $label);
