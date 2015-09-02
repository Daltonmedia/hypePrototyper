<?php

$fieldset_head = '';

$label = elgg_view('prototyper/elements/label', $vars);
if ($label) {
	$fieldset_head .= elgg_format_element('div', array(
		'class' => 'prototyper-field-label',
	), $label);
}

$help = elgg_view('prototyper/elements/help', $vars);
if ($help) {
	$fieldset_head .= elgg_format_element('div', array(
		'class' => 'prototyper-field-help',
	), $help);
}

if (!$fieldset_head) {
	return;
}
?>

<div class="elgg-head prototyper-fieldset-head">
	<div class="prototyper-col-12">
		<?php echo $fieldset_head ?>
	</div>
</div>