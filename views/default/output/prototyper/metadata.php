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
$type = $field->getType();
$class = $field->getMicroformat();

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

$vars['value'] = $values;
$output = elgg_view($view, $vars);

if (!$output) {
	return true;
}

echo <<<__HTML
<div class="prototyper-output-metadata">
	<b class="prototyper-label">$label</b>
	<span class="$class">$output</span>
</div>
__HTML;

