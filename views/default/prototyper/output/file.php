<?php

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof hypeJunction\Prototyper\Elements\UploadField) {
	return;
}

$name = $field->getShortname();

if (!$entity || !$name) {
	return;
}

$label = $field->getLabel();
$view = $field->getOutputView();

$uploads = $field->getValues($entity);
if (empty($uploads)) {
	return;
}

elgg_push_context('widgets');
$vars['full_view'] = false;
$output = elgg_view_entity_list($uploads, $vars);
elgg_pop_context();

if (!$output) {
	return;
}

echo <<<__HTML
<div class="prototyper-output-upload">
	<label class="prototyper-label">$label</label>
	<div class="elgg-output $class">$output</div>
</div>
__HTML;
