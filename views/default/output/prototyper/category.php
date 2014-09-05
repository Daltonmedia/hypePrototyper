<?php

namespace hypeJunction\Prototyper;

$field = elgg_extract('field', $vars);

if (!$field instanceof RelationshipField) {
	return true;
}

$entity = $field->getEntity();
$name = $field->getShortname();

if (!$entity || !$name) {
	return true;
}

elgg_require_js('framework/prototyper');

$label = $field->getLabel();
$view = $field->getOutputView();
$class = $field->getMicroformat();

$relationships = $field->getValues();
if (!count($relationships)) {
	return true;
}

if ($relationships) {
	foreach ($relationships as $guid) {
		$entity = get_entity($guid);
		if ($entity) {
			$entities[] = get_entity($guid);
		}
	}
}

if (empty($entities)) {
	return true;
}

elgg_push_context('widgets');
$vars['full_view'] = false;
$output = elgg_view_entity_list($entities, $vars);
elgg_pop_context();

if (!$output) {
	return true;
}

echo <<<__HTML
<div class="prototyper-output-category">
	<label class="prototyper-label">$label</label>
	<div class="elgg-output $class">$output</div>
</div>
__HTML;
