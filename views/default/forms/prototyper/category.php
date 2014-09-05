<?php

namespace hypeJunction\Prototyper;

$field = elgg_extract('field', $vars);

if (!$field instanceof CategoryField) {
	return true;
}

$entity = $field->getEntity();
$name = $field->getShortname();

if (!$entity || !$name) {
	return true;
}

$label = $field->getLabel();
$help = $field->getHelp();
$required = $field->isRequired();
$multiple = $field->isMultiple();

if ($required) {
	$label_attrs = elgg_format_attributes(array(
		'class' => 'required',
		'title' => elgg_echo('prototyper:required')
	));
}

$input_vars = $field->getInputVars();
$input_vars['name'] = $name;
$value = $field->getValues();
if ($value) {
	$input_vars['value'] = $value;
}

$type = $field->getType();
$view = $field->getInputView();
$input = elgg_view($view, $input_vars);

if ($type == 'hidden') {
	echo $input;
	return true;
}
?>

<fieldset class="prototyper-fieldset prototyper-fieldset-category">
	<div class="elgg-head">
		<div class="prototyper-col-12">
			<?php
			if ($label) {
				echo "<label $label_attrs>$label</label>";
			}
			if ($help) {
				echo '<span class="prototyper-help">' . elgg_view_icon('question') . '<span class="prototyper-help-text">' . $help . '</span></span>';
			}
			?>
		</div>
	</div>
	<div class="elgg-body">
		<div class="prototyper-col-12">
			<?php
			echo $input;
			?>
		</div>
	</div>
</fieldset>

<?php
if ($field->isValid() === false) {
	echo '<ul class="prototyper-validation-error prototyper-col-12">';
	$messages = $field->getValidationMessages();
	if (!is_array($messages)) {
		$messages = array($messages);
	}
	foreach ($messages as $m) {
		echo '<li>' . $m . '</li>';
	}
	echo '</ul>';
}