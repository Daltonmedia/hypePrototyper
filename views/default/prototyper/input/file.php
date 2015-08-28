<?php
$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof hypeJunction\Prototyper\Elements\UploadField) {
	return;
}

if ($field->getType() == 'icon') {
	echo elgg_view('prototyper/input/icon', $vars);
	return;
}

$name = $field->getShortname();

if (!$entity || !$name) {
	return;
}

$input_vars = $field->getInputVars($entity);
$input_vars['name'] = $name;
$upload = $field->getValues($entity);
$input_vars['value'] = $upload instanceof ElggFile;

$label = $field->getLabel();
$help = $field->getHelp();
$required = $field->isRequired() && !$input_vars['value'];
$input_vars['required'] = $required;

if ($required) {
	$label_attrs = elgg_format_attributes(array(
		'class' => 'required',
		'title' => elgg_echo('prototyper:required')
	));
}

$type = $field->getType();
$view = $field->getInputView();
$input = elgg_view($view, $input_vars);

echo elgg_view('prototyper/input/before', $vars);
?>
<fieldset class="prototyper-fieldset prototyper-fieldset-upload">
	<div class="elgg-head">
		<div class="prototyper-col-12">
			<?php
			if ($label) {
				echo "<label $label_attrs>$label</label>";
			}
			echo elgg_view('prototyper/elements/help', array(
				'value' => $help,
				'field' => $field,
			));
			?>
		</div>
	</div>
	<div class="elgg-body">
		<div class="prototyper-col-12">
			<?php
			$icon = '';
			if ($upload && $upload->icontime) {
				$icon = elgg_view_entity_icon($upload, 'small');
			}
			echo elgg_view_image_block('', $input, array(
				'image_alt' => $icon,
				'class' => 'prototyper-icon-input prototyper-upload-input',
			));
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
			?>
		</div>
	</div>
</fieldset>

<?php
echo elgg_view('prototyper/input/after', $vars);
