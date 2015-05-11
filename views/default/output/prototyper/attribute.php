<?php

namespace hypeJunction\Prototyper;

$field = elgg_extract('field', $vars);

if (!$field instanceof AttributeField) {
	return true;
}

$entity = $field->getEntity();
$name = $field->getShortname();

if (!$entity || !$name) {
	return true;
}

$label = $field->getLabel();
$view = $field->getOutputView();
$type = $field->getType();

$value = $field->getValues();
$class = $field->getMicroformat();

if (!$value) {
	return true;
}

$vars['value'] = $value;
$output = elgg_view($view, $vars);

if (!$output) {
	return true;
}

echo <<<__HTML
<div class="prototyper-output-attribute">
	<label class="prototyper-label">$label</label>
	<div class="elgg-output $class">$output</div>
</div>
__HTML;
