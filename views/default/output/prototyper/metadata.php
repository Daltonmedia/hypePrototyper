<?php

namespace hypeJunction\Prototyper;

$field = elgg_extract('field', $vars);

if (!$field instanceof MetadataField) {
	return true;
}

$entity = $field->getEntity();
$name = $field->getShortname();

if (!$entity || !$name) {
	return true;
}

$label = $field->getLabel();
$view = $field->getOutputView();
if (!elgg_view_exists($view)) {
	$view = 'output/longtext';
}
$type = $field->getType();
$class = $field->getMicroformat();

$vars = array_merge($field->getInputVars(), $vars);

$metadata = $field->getValues();

if (count($metadata) > 1) {
	foreach ($metadata as $md) {
		$values[] = $md->value;
	}
} else {
	$values = $metadata[0]->value;
}

if (empty($values)) {
	return true;
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
	return true;
}

echo <<<__HTML
<div class="prototyper-output-metadata">
	<label class="prototyper-label">$label</label>
	<div class="elgg-output $class">$output</div>
</div>
__HTML;

