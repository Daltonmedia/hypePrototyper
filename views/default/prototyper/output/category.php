<?php

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof hypeJunction\Prototyper\Elements\CategoryField) {
	return;
}

$name = $field->getShortname();

if (!$entity || !$name) {
	return;
}

if (\hypeJunction\Integration::isElggVersionBelow('1.9.0')) {
	elgg_load_js('prototyper');
} else {
	elgg_require_js('framework/prototyper');
}

$label = $field->getLabel();
$view = $field->getOutputView();

$categories = array();
$relationships = $field->getValues($entity);
if ($relationships instanceof ElggBatch) {
	foreach ($relationships as $guid) {
		$categories[] = $guid;
	}
} else {
	$categories = $relationships;
}

if (!count($categories)) {
	return;
}

if ($categories) {
	foreach ($categories as $guid) {
		$entity = get_entity($guid);
		if ($entity) {
			$entities[] = get_entity($guid);
		}
	}
}

if (empty($entities)) {
	return;
}

elgg_push_context('widgets');
$vars['full_view'] = false;
$output = elgg_view_entity_list($entities, $vars);
elgg_pop_context();

if (!$output) {
	return;
}

echo <<<__HTML
<div class="prototyper-output-category">
	<label class="prototyper-label">$label</label>
	<div class="elgg-output $class">$output</div>
</div>
__HTML;
