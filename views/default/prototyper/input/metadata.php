<?php
$field = elgg_extract('field', $vars);
$index = elgg_extract('index', $vars, '');
$entity = elgg_extract('entity', $vars);

if (!$field instanceof \hypeJunction\Prototyper\Elements\MetadataField) {
	return;
}

$name = $field->getShortname();

if (!$entity || !$name) {
	return;
}

$label = $field->getLabel();
$help = $field->getHelp();
$required = $field->isRequired();
$multiple = $field->isMultiple();
$type = $field->getType();

if ($required) {
	$label_attrs = elgg_format_attributes(array(
		'class' => 'required',
		'title' => elgg_echo('prototyper:required')
	));
}

$metadata = $field->getValues($entity);
if (empty($metadata)) {
	return;
}

if (($field->getValueType() == 'tags' || !$field->isMultiple()) && sizeof($metadata) > 1) {
	$shortname = $field->getShortname();
	$md = new \stdClass();
	$md->id = $metadata[0]->id;
	$md->name = $shortname;
	$value = $entity->$shortname;
	if (is_array($value)) {
		$md->value = implode(', ', $value);
	} else {
		$md->value = $value;
	}
	$md->access_id = $metadata[0]->access_id;
	$md->owner_guid = $metadata[0]->owner_guid;
	$metadata = array($md);
} else if (in_array($field->getValueType(), array('checkboxes', 'radio'))) {
	$shortname = $field->getShortname();
	$md = new \stdClass();
	$md->id = $metadata[0]->id;
	$md->name = $shortname;
	$value = $entity->$shortname;
	if (is_array($value)) {
		$md->value = $value;
	} else {
		$md->value = array($value);
	}
	$md->access_id = $metadata[0]->access_id;
	$md->owner_guid = $metadata[0]->owner_guid;
	$metadata = array($md);
}

echo elgg_view('prototyper/input/before', $vars);

foreach ($metadata as $md) {
	$hidden = elgg_view('input/hidden', array(
		'name' => "{$name}[id][{$index}]",
		'value' => $md->id,
		'data-reset' => true,
	));
	$hidden .= elgg_view('input/hidden', array(
		'name' => "{$name}[name][{$index}]",
		'value' => ($md->name) ? $md->name : $name,
	));
	$hidden .= elgg_view('input/hidden', array(
		'name' => "{$name}[owner_guid][{$index}]",
		'value' => ($md->owner_guid) ? $md->owner_guid : elgg_get_logged_in_user_guid(),
	));
	$input_vars = $field->getInputVars($entity);
	$input_vars['name'] = "{$name}[value][{$index}]";
	$input_vars['value'] = $md->value;
	$input_vars['data-reset'] = true;
	$input_vars['placeholder'] = $label;

	$type = $field->getType();
	$view = $field->getInputView();

	$input = elgg_view($view, $input_vars);

	$access_id = ($md->access_id) ? $md->access_id : ($entity->guid) ? $entity->access_id : get_default_access();
	$hidden .= elgg_view("input/hidden", array(
		'name' => "{$name}[access_id][{$index}]",
		'value' => $access_id,
	));

	if ($type == 'hidden') {
		echo $hidden . $input;
		continue;
	}
	?>

	<fieldset class="prototyper-fieldset prototyper-fieldset-metadata">
		<div class="elgg-head">
			<div class="prototyper-col-12">
				<?php
				if ($label) {
					echo "<label $label_attrs>$label</label>";
				}
				if ($multiple) {
					echo elgg_view('output/url', array(
						'text' => elgg_view_icon('prototyper-round-plus'),
						'href' => 'javascript:void(0);',
						'class' => 'prototyper-clone',
						'is_trusted' => true,
					));
					echo elgg_view('output/url', array(
						'text' => elgg_view_icon('prototyper-round-minus'),
						'href' => 'javascript:void(0);',
						'class' => 'prototyper-remove',
						'is_trusted' => true,
					));
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
				echo $hidden;
				echo $input;
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
}

echo elgg_view('prototyper/input/after', $vars);
