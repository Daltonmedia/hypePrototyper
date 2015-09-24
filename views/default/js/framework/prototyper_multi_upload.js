define(['elgg', 'jquery', 'elgg/spinner'], function (elgg, $, spinner) {

	if (typeof $.fn.sortable === 'function') {
		$('.prototyper-multi-edit-mode').sortable({
			items: 'li.elgg-item',
			connectWith: '.prototyper-multi-edit-mode',
			handle: '.elgg-icon-cursor-drag-arrow',
			forcePlaceholderSize: true,
			placeholder: 'elgg-widget-placeholder',
			opacity: 0.8,
			revert: 500,
			stop: function (event, ui) {
				var data = ui.item
						.closest('.prototyper-multi-edit-mode')
						.sortable('serialize');
				elgg.action('action/prototyper/multi/sort?' + data, {
//					beforeSend: function () {
//						spinner.start();
//					},
//					complete: function () {
//						spinner.stop();
//					}
				});
				ui.item.css('top', 0);
				ui.item.css('left', 0);
			}
		});
	}

	$(document).on('click', '.prototyper-multi-item-delete', function (e) {
		e.preventDefault();
		var $elem = $(this);
		elgg.action($(this).attr('href'), {
			success: function (data) {
				if (data.status >= 0) {
					$elem.closest('li').fadeOut().remove();
				}
			},
			beforeSend: function () {
				spinner.start();
			},
			complete: function () {
				spinner.stop();
			}
		});
	});
});
