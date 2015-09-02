<?php

$field = elgg_extract('field', $vars);

if (!$field instanceof \hypeJunction\Prototyper\Elements\Field) {
	return;
}

$shortname = $field->getShortname();
$type = $field->getType();
$data_type = $field->getDataType();

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

