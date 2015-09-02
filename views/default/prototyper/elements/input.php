<?php

use hypeJunction\Prototyper\Elements\Field;

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof Field) {
	return;
}

$input_view = $field->getInputView();
$input_vars = $field->getInputVars($entity);

echo elgg_view($input_view, $input_vars);
