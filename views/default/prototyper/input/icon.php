<?php
$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof hypeJunction\Prototyper\Elements\IconField) {
	return;
}

$name = $field->getShortname();

if (!$entity || !$name) {
	return;
}

$label = $field->getLabel();
$help = $field->getHelp();
$required = $field->isRequired();

if ($required) {
	$label_attrs = elgg_format_attributes(array(
		'class' => 'required',
		'title' => elgg_echo('prototyper:required')
	));
}

$input_vars = $field->getInputVars($entity);
$input_vars['name'] = $name;
$input_vars['value'] = $field->getValues($entity);

$type = $field->getType();
$view = $field->getInputView();
$input = elgg_view($view, $input_vars);

echo elgg_view('prototyper/input/before', $vars);
?>
<fieldset class="prototyper-fieldset prototyper-fieldset-icon">
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
			if ($entity->icontime) {
				$icon = elgg_view_entity_icon($entity, 'small');
			}
			echo elgg_view_image_block('', $input, array(
				'image_alt' => $icon,
				'class' => 'prototyper-upload-input prototyper-icon-input',
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
