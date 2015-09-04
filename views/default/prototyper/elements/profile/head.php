<?php

$profile_head = '';

$label = elgg_view('prototyper/elements/label', $vars);
if ($label) {
	$profile_head .= elgg_format_element('div', array(
		'class' => 'prototyper-field-label',
	), $label);
}

//$help = elgg_view('prototyper/elements/help', $vars);
//if ($help) {
//	$profile_head .= elgg_format_element('div', array(
//		'class' => 'prototyper-field-help',
//	), $help);
//}

if (!$profile_head) {
	return;
}
?>

<div class="prototyper-profile-head">
	<?php echo $profile_head ?>
</div>