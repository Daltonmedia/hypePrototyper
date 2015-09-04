<?php

use hypeJunction\Prototyper\Elements\Field;

$field = elgg_extract('field', $vars);
if (!$field instanceof Field) {
	return;
}

$profile_body = '';

$shortname = $field->getShortname();
$type = $field->getType();
$data_type = $field->getDataType();

$views = array(
	"prototyper/elements/output/$data_type/$type/$shortname",
	"prototyper/elements/output/$data_type/$type",
	"prototyper/elements/output/$data_type",
	"prototyper/elements/output",
);

foreach ($views as $view) {
	if (elgg_view_exists($view)) {
		$profile_body .= elgg_view($view, $vars);
		break;
	}
}
if (!$profile_body) {
	return;
}
?>

<div class="prototyper-profile-body">
	<?php echo $profile_body ?>
</div>
