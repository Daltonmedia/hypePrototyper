//<script>

	elgg.provide('elgg.prototyper');

	elgg.prototyper.init = function () {
		$('.prototyper-clone').live('click', function (e) {
			var $parent = $(this).closest('.prototyper-fieldset');
			var $clone = $parent.clone(true, false);
			$('[data-reset]', $clone).val('').trigger('reset');
			$parent.after($clone);

		});

		$('.prototyper-remove').live('click', function (e) {
			var confirmText = $(this).attr('rel') || elgg.echo('question:areyousure');
			if (confirm(confirmText)) {
				var $parent = $(this).closest('.prototyper-fieldset');
				$parent.fadeOut().remove();
			}
		});
		
		
		// images js
		$('.prototyper-image-preview a .elgg-icon-delete').live('click', function() {
			if (confirm(elgg.echo('question:areyousure'))) {
				$(this).parents('.prototyper-image-preview').eq(0).remove();
			}
		});
	};

	elgg.register_hook_handler('init', 'system', elgg.prototyper.init);
