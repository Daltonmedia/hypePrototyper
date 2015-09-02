<?php

use hypeJunction\Prototyper\Elements\Field;

$field = elgg_extract('field', $vars);
if (!$field instanceof Field) {
	return;
}

$shortname = $field->getShortname();
$type = $field->getType();
$data_type = $field->getDataType();

// Backward compatibility
$views = array(
	"prototyper/input/$data_type/$type/$shortname",
	"prototyper/input/$data_type/$type",
	"prototyper/input/$data_type",
);
foreach ($views as $view) {
	if (elgg_view_exists($view)) {
		echo elgg_view($view, $vars);
		return;
	}
}

$before = elgg_view('prototyper/input/before', $vars);
$head = elgg_view('prototyper/elements/field/head', $vars);
$body = elgg_view('prototyper/elements/field/body', $vars);
$foot = elgg_view('prototyper/elements/field/foot', $vars);
$after = elgg_view('prototyper/input/after', $vars);

if ($type == 'hidden') {
	echo $body;
	return;
}

$class = array(
	'prototyper-fieldset',
	"prototyper-fieldset-{$data_type}",
	"prototyper-fieldset-input-{$type}",
	"prototyper-fieldset-name-{$shortname}",
);

if (!$field->isValid()) {
	$class[] = 'prototyper-fieldset-has-errors';
}

echo elgg_format_element('fieldset', array(
	'class' => $class,
), $before . $head . $body . $foot . $after);