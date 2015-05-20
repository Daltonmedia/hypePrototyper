<?php

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof \hypeJunction\Prototyper\Elements\AttributeField) {
	return;
}

$name = $field->getShortname();

if (!$entity || !$name) {
	return;
}

$label = $field->getLabel();
$view = $field->getOutputView();
$type = $field->getType();

$value = $field->getValues($entity);

if (!$value) {
	return;
}

$vars['value'] = $value;
$output = elgg_view($view, $vars);

if (!$output) {
	return;
}

echo <<<__HTML
<div class="prototyper-output-attribute">
	<label class="prototyper-label">$label</label>
	<div class="elgg-output $class">$output</div>
</div>
__HTML;
