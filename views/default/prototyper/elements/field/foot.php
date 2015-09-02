<?php

use hypeJunction\Prototyper\Elements\Field;

$field = elgg_extract('field', $vars);
if (!$field instanceof Field) {
	return;
}

$errors = (array) $field->getValidationMessages();

$fieldset_foot = elgg_format_element('div', array(
	'class' => 'prototyper-validation-errors',
		), implode('; ', $errors));
?>

<div class="elgg-foot prototyper-fieldset-foot">
	<div class="prototyper-col-12">
		<?php echo $fieldset_foot ?>
	</div>
</div>