<?php

namespace hypeJunction\Prototyper;

$entity = elgg_extract('entity', $vars);

echo '<div>';
echo '<label>' . elgg_echo("prototyper:settings:default_language") . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => "params[default_language]",
	'options_values' => get_installed_translations(),
	'value' => $entity->default_language
));
echo '</div>';
