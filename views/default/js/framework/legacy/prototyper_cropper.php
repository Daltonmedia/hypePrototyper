//<script>

	elgg.provide('elgg.prototyper_cropper');

	elgg.prototyper_cropper.init = function () {

		$(document).delegate('.prototyper-upload-input input[type="file"]', 'change', function (e) {

			var $elem = $(this);
			var $container = $elem.closest('.prototyper-upload-input');
			var $croppers = $container.find('.prototyper-image-upload-cropper');

			if (!$croppers.length) {
				return;
			}

			var file = $elem[0].files[0];

			if (file && file.type.match(/image.*/)) {
				$('body').addClass('elgg-state-loading');
				var reader = new FileReader();
				reader.onload = function (e) {
					$croppers.each(function () {
						var $cropper = $(this);
						var ratio = $cropper.data('ratio');

						$('img', $cropper).cropper('destroy');
						$('img', $cropper).remove();
						$cropper.removeClass('prototyper-has-preview');
						
						var img = new Image();
						img.src = reader.result;
						img.alt = file.name;

						$cropper.addClass('prototyper-has-preview');
						$(this).append($(img));
						
						if (typeof $.fn.cropper !== 'undefined') {
							$('img', $cropper).cropper({
								aspectRatio: ratio,
								autoCropArea: 0.90,
								done: function (data) {
									$('input[data-x1]', $cropper).val(data.x);
									$('input[data-x2]', $cropper).val((data.x + data.width));
									$('input[data-y1]', $cropper).val(data.y);
									$('input[data-y2]', $cropper).val((data.y + data.height));
								}
							});
						}
					});

					$('body').removeClass('elgg-state-loading');
				};

				reader.readAsDataURL(file);
			}
		});
	};

	elgg.register_hook_handler('init', 'system', elgg.prototyper_cropper.init);