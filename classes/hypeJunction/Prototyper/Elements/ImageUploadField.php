<?php

namespace hypeJunction\Prototyper\Elements;

class ImageUploadField extends UploadField {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {

		$result = parent::handle($entity);

		if (!$result) {
			return $result;
		}

		$shortname = $this->getShortname();

		$image_upload_crop_coords = (array) get_input('image_upload_crop_coords', array());
		$coords = (array) elgg_extract($shortname, $image_upload_crop_coords);

		$x1 = (int) elgg_extract('x1', $coords);
		$x2 = (int) elgg_extract('x2', $coords);
		$y1 = (int) elgg_extract('y1', $coords);
		$y2 = (int) elgg_extract('y2', $coords);

		if ($x2 <= $x1 || $y2 <= $y1) {
			return $result;
		}

		$cropW = $this->input_vars->{"data-crop-ratio-w"};
		$cropH = $this->input_vars->{"data-crop-ratio-h"};
		$ratio = (int) $cropW / (int) $cropH;

		$large = $this->input_vars->{"data-crop-large-w"};
		$medium = $this->input_vars->{"data-crop-medium-w"};
		$small = $this->input_vars->{"data-crop-small-w"};

		list($master_width, $master_height) = getimagesize($_FILES[$shortname]['tmp_name']);
		
		$options = array(
			'icon_sizes' => array(
				'_small' => array(
					'w' => (int) $small,
					'h' => (int) round($small / $ratio),
					'square' => $ratio === 1,
					'upscale' => true,
					'croppable' => true,
					'metadata_name' => '_small_icon',
				),
				'_medium' => array(
					'w' => (int) $medium,
					'h' => (int) round($medium / $ratio),
					'square' => $ratio === 1,
					'upscale' => true,
					'croppable' => true,
					'metadata_name' => '_medium_icon',
				),
				'_large' => array(
					'w' => (int) $large,
					'h' => (int) round($large / $ratio),
					'square' => $ratio === 1,
					'upscacle' => true,
					'croppable' => true,
					'metadata_name' => '_large_icon',
				)
			),
			'coords' => array(
				'x1' => $x1,
				'x2' => $x2,
				'y1' => $y1,
				'y2' => $y2,
				'master_width' => $master_width,
				'master_height' => $master_height,
			)
		);

		hypeApps()->iconFactory->create($this->getValues($entity), $_FILES[$shortname]['tmp_name'], $options);
		
		return $result;
	}

}
