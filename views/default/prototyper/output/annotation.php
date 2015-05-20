<?php

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof hypeJunction\Prototyper\Elements\AnnotationField) {
	return;
}

$name = $field->getShortname();

if (!$entity || !$name) {
	return;
}

$label = $field->getLabel();
$view = $field->getOutputView();

$vars = array_merge($field->getInputVars($entity), $vars);

$annotations = $field->getValues($entity);

if (empty($annotations)) {
	return;
}

foreach ($annotations as $ann) {
	$vars['value'] = $ann->value;
	$output = elgg_view($view, $vars);
}

if (!$output) {
	return;
}

echo <<<__HTML
<div class="prototyper-output-annotation">
	<label class="prototyper-label">$label</label>
	<div class="elgg-output $class">$output</div>
</div>
__HTML;


