<?php

use hypeJunction\Prototyper\Elements\Field;

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof Field) {
	return;
}

$input_view = $field->getInputView();
$input_vars = $field->getInputVars($entity);

$upload = elgg_extract('value', $input_vars);

$input = elgg_view($input_view, $input_vars);
$icon = '';
if ($upload && $upload->icontime) {
	$icon = elgg_view_entity_icon($upload, 'small');
}

echo elgg_view_image_block('', $input, array(
	'image_alt' => $icon,
	'class' => 'prototyper-upload-input',
));
