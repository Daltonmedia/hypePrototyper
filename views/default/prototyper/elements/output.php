<?php

use hypeJunction\Prototyper\Elements\Field;

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof Field) {
	return;
}

$output_view = $field->getOutputView();
$output_vars = $field->getOutputVars($entity);

echo elgg_view($output_view, $output_vars);
