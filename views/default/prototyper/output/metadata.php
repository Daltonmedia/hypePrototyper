<?php

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof \hypeJunction\Prototyper\Elements\MetadataField) {
	return;
}

$name = $field->getShortname();

if (!$entity || !$name) {
	return;
}

$label = $field->getLabel();
$view = $field->getOutputView();
if (!elgg_view_exists($view)) {
	$view = 'output/longtext';
}
$type = $field->getType();

$vars = array_merge($field->getInputVars($entity), $vars);

$metadata = $field->getValues($entity);

if (count($metadata) > 1) {
	foreach ($metadata as $md) {
		$values[] = $md->value;
	}
} else {
	$values = $metadata[0]->value;
}

if (empty($values)) {
	return;
}

if (is_array($values) && $type !== 'tags') {
	foreach ($values as $value) {
		$vars['value'] = $value;
		$output .= '<div>' . elgg_view($view, $vars) . '</div>';
	}
} else {
	$vars['value'] = $values;
	$output = elgg_view($view, $vars);
}

if (!$output) {
	return;
}

echo <<<__HTML
<div class="prototyper-output-metadata">
	<label class="prototyper-label">$label</label>
	<div class="elgg-output $class">$output</div>
</div>
__HTML;

