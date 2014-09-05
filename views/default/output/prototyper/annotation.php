<?php

namespace hypeJunction\Prototyper;

$field = elgg_extract('field', $vars);

if (!$field instanceof AnnotationField) {
	return true;
}

$entity = $field->getEntity();
$name = $field->getShortname();

if (!$entity || !$name) {
	return true;
}

$label = $field->getLabel();
$view = $field->getOutputView();
$class = $field->getMicroformat();

$annotations = $field->getValues();

if (empty($annotations)) {
	return true;
}

foreach ($annotations as $ann) {
	$vars['value'] = $ann->value;
	$output = elgg_view($view, $vars);
}

if (!$output) {
	return true;
}

echo <<<__HTML
<div class="prototyper-output-annotation">
	<label class="prototyper-label">$label</label>
	<div class="elgg-output $class">$output</div>
</div>
__HTML;


