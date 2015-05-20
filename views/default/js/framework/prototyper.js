define(['elgg', 'jquery'], function(elgg, $) {

	$(document).on('click', '.prototyper-clone', function(e) {
		var $parent = $(this).closest('.prototyper-fieldset');
		var $clone = $parent.clone(true, false);
		$('[data-reset]', $clone).val('').trigger('reset');
		$parent.after($clone);

	});

	$(document).on('click', '.prototyper-remove', function(e) {
		var confirmText = $(this).attr('rel') || elgg.echo('question:areyousure');
		if (confirm(confirmText)) {
			var $parent = $(this).closest('.prototyper-fieldset');
			$parent.fadeOut().remove();
		}
	});

});