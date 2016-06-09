<?php

use hypeJunction\Prototyper\Elements\Field;

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof Field) {
	return;
}

$output_view = $field->getOutputView();
$output_vars = $field->getOutputVars($entity);

$value = elgg_extract('value', $output_vars);
if (empty($value)) {
	return;
}

switch ($output_view) {
	case 'output/url' :
		unset($output_vars['value']);
		$output_vars['href'] = $value;
		break;
}

echo elgg_view($output_view, $output_vars);
