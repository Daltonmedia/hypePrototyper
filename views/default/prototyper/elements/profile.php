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
	"prototyper/output/$data_type/$type/$shortname",
	"prototyper/output/$data_type/$type",
	"prototyper/output/$data_type",
);
foreach ($views as $view) {
	if (elgg_view_exists($view)) {
		echo elgg_view($view, $vars);
		return;
	}
}

$before = elgg_view('prototyper/profile/before', $vars);
$head = elgg_view('prototyper/elements/profile/head', $vars);
$body = elgg_view('prototyper/elements/profile/body', $vars);
$after = elgg_view('prototyper/profile/after', $vars);

if (empty($body)) {
	return;
}

$class = array(
	'prototyper-output',
	"prototyper-profile-{$data_type}",
	"prototyper-profile-profile-{$type}",
	"prototyper-profile-name-{$shortname}",
);

echo elgg_format_element('div', array(
	'class' => $class,
), $before . $head . $body . $after);