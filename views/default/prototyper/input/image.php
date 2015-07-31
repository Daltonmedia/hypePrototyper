<?php

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof hypeJunction\Prototyper\Elements\ImageField) {
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
$input_vars['multiple'] = true;
$input_vars['max'] = $field->isMultiple() ? 10 : 1;
$input_vars['accept'] = 'image/*';

$type = $field->getType();
$view = $field->getInputView();
$input = elgg_view($view, $input_vars);

$image_data = array(
	'data-crop' => $input_vars['crop'] ? 1 : 0,
	'data-crop-w' => $input_vars['crop_ratio_w'],
	'data-crop-h' => $input_vars['crop_ratio_h']
);

echo elgg_view('prototyper/input/before', $vars);
?>
<fieldset class="prototyper-fieldset prototyper-fieldset-image" <?php echo elgg_format_attributes($image_data); ?>>
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
			echo $input;
			?>
		</div>
		<div class="prototyper-col-12">
			<?php
			$guids = (array) $entity->$name;
			foreach ($guids as $g) {
				$file = get_entity($g);
				if (!($file instanceof ElggFile)) {
					continue;
				}
				echo '<div class="prototyper-image-preview elgg-col elgg-col-1of6">';
				echo elgg_view('output/url', array(
					'text' => elgg_view_icon('delete'),
					'href' => 'javascript:void(0)'
				));
				echo elgg_view('output/img', array(
					'src' => $file->getIconURL('medium')
				));
				echo elgg_view('input/hidden', array(
					'name' => $name . '[]',
					'value' => $g
				));
				echo '</div>';
			}
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
echo elgg_view('prototyper/input/after', $vars);

?>
<script>
		/**
		 * Image cropping js
		 */

		$('.elgg-input-dropzone').live('initialize', function(e) {
			// add some handlers
			$(this).dropzone.live('addFile', function(e) {
				console.log('file added');
			});
		});
</script>