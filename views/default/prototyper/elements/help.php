<?php
use hypeJunction\Prototyper\Elements\Field;

$field = elgg_extract('field', $vars);
if (!$field instanceof Field) {
	return;
}

$help = $field->getHelp();
if (!$help) {
	return;
}

echo elgg_format_element('div', array(
	'class' => 'elgg-text-help',
		), $help);
