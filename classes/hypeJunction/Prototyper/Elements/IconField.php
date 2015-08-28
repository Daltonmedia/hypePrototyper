<?php

namespace hypeJunction\Prototyper\Elements;

class IconField extends UploadField {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function getValues(\ElggEntity $entity) {
		return ($entity->icontime);
	}

	/**
	 * {@inheritdoc}
	 */
/**
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {

		$shortname = $this->getShortname();

		$icon_sizes = hypeApps()->iconFactory->getSizes($entity);
		$custom_icon_sizes = (array) $this->input_vars->{"icon_sizes"};
		$icon_sizes = array_merge($icon_sizes, $custom_icon_sizes);

		if (empty($icon_sizes)) {
			return $entity;
		}

		$image_upload_crop_coords = (array) get_input('image_upload_crop_coords', array());
		$ratio_coords = (array) elgg_extract($shortname, $image_upload_crop_coords, array());
		foreach ($icon_sizes as $icon_size) {
			$ratio = (int) $icon_size['w'] / (int) $icon_size['h'];
			$coords = $ratio_coords[(string) $ratio];

			$x1 = (int) elgg_extract('x1', $coords);
			$x2 = (int) elgg_extract('x2', $coords);
			$y1 = (int) elgg_extract('y1', $coords);
			$y2 = (int) elgg_extract('y2', $coords);

			if ($x2 <= $x1 || $y2 <= $y1) {
				continue;
			}

			list($master_width, $master_height) = getimagesize($_FILES[$shortname]['tmp_name']);

			$options = array(
				'icon_sizes' => array(
					$icon_size['name'] => $icon_size,
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

			foreach (array('x1', 'x2', 'y1', 'y2') as $c) {
				$entity->{"_coord_{$ratio}_{$coord}"} = elgg_extract($c, $coords);
				if ($ratio === 1) {
					$entity->$c = elgg_extract($c, $coords);
				}
			}

			hypeApps()->iconFactory->create($entity, $_FILES[$shortname]['tmp_name'], $options);
		}

		return $entity;
	}

}
