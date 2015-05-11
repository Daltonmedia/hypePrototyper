<?php

namespace hypeJunction\Prototyper;

$field = elgg_extract('field', $vars);
$index = elgg_extract('index', $vars, '');

if (!$field instanceof MetadataField) {
	return true;
}

$entity = $field->getEntity();
$name = $field->getShortname();

if (!$entity || !$name) {
	return true;
}

elgg_require_js('framework/prototyper');

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

$metadata = $field->getValues();

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
	$input_vars = $field->getInputVars();
	$input_vars['name'] = "{$name}[value][{$index}]";
	if ($md->id) {
		$input_vars['value'] = $md->value;
	}
	$input_vars['data-reset'] = true;
	$type = $field->getType();
	$view = $field->getInputView();

	$input = elgg_view($view, $input_vars);

	$show_access = $field->hasAccessInput();

	$access = '';
	if (is_int($show_access)) {
		$access .= '<span class="elgg-access">' . get_readable_access_level($access_id) . '</span>';
		$access_id = $show_access;
		$access_type = 'hidden';
	} else {
		$access_id = ($md->access_id) ? $md->access_id : ($entity->guid) ? $entity->access_id : get_default_access();
		if ($show_access === true && $type !== 'hidden') {
			$access_type = 'access';
		} else {
			$access_type = 'hidden';
		}
	}
	$access .= elgg_view("input/$access_type", array(
		'name' => "{$name}[access_id][{$index}]",
		'value' => $access_id,
	));

	if ($type == 'hidden') {
		echo $hidden . $access . $input;
		continue;
	}
	?>

	<fieldset class="prototyper-fieldset prototyper-fieldset-metadata">
		<div class="elgg-head">
			<div class="prototyper-col-9">
				<?php
				if ($label) {
					echo "<label $label_attrs>$label</label>";
				}
				if ($help) {
					echo '<span class="prototyper-help">' . elgg_view_icon('prototyper-question') . '<span class="prototyper-help-text">' . $help . '</span></span>';
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
				?>
			</div>
			<div class="prototyper-col-3 prototyper-access">
				<?php
				echo '<span>' . $access . '</span>';
				?>
			</div>
		</div>
		<div class="elgg-body">
			<div class="prototyper-col-12">
				<?php
				echo $hidden;
				echo $input;
				?>
			</div>
		</div>
	</fieldset>
	<?php
}

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