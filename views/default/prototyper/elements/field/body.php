<?php

use hypeJunction\Prototyper\Elements\Field;

$field = elgg_extract('field', $vars);
if (!$field instanceof Field) {
	return;
}

$fieldset_body = '';

$shortname = $field->getShortname();
$type = $field->getType();
$data_type = $field->getDataType();

$views = array(
	"prototyper/elements/input/$data_type/$type/$shortname",
	"prototyper/elements/input/$data_type/$type",
	"prototyper/elements/input/$data_type",
	"prototyper/elements/input",
);

foreach ($views as $view) {
	if (elgg_view_exists($view)) {
		$fieldset_body .= elgg_view($view, $vars);
		break;
	}
}
if (!$fieldset_body) {
	return;
}
?>

<div class="elgg-body prototyper-fieldset-body">
	<div class="prototyper-col-12">
		<?php echo $fieldset_body ?>
	</div>
</div>
