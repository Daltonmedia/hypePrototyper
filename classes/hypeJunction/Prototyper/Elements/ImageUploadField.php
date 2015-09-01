<?php

namespace hypeJunction\Prototyper\Elements;

class ImageUploadField extends UploadField {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {

		$this->tmp_icon_sizes = array();

		// make sure we do not duplicate icon creation
		elgg_register_plugin_hook_handler('entity:icon:sizes', 'object', array($this, 'getIconSizes'), 999);

		$result = parent::handle($entity);

		elgg_unregister_plugin_hook_handler('entity:icon:sizes', 'object', array($this, 'getIconSizes'));

		if (!$result) {
			return $entity;
		}

		$shortname = $this->getShortname();
		$upload = $this->getValues($entity);

		$icon_sizes = hypeApps()->iconFactory->getSizes($upload);
		$custom_icon_sizes = (array) $this->input_vars->{"icon_sizes"};
		$icon_sizes = array_merge($icon_sizes, $custom_icon_sizes);

		if (empty($icon_sizes)) {
			return $entity;
		}

		$image_upload_crop_coords = (array) get_input('image_upload_crop_coords', array());
		$ratio_coords = (array) elgg_extract($shortname, $image_upload_crop_coords, array());

		list($master_width, $master_height) = getimagesize($_FILES[$shortname]['tmp_name']);

		foreach ($icon_sizes as $icon_name => $icon_size) {
			$ratio = (int) $icon_size['w'] / (int) $icon_size['h'];
			$coords = (array) elgg_extract("$ratio", $ratio_coords, array());

			$x1 = (int) elgg_extract('x1', $coords);
			$x2 = (int) elgg_extract('x2', $coords);
			$y1 = (int) elgg_extract('y1', $coords);
			$y2 = (int) elgg_extract('y2', $coords);

			if ($x2 <= $x1 || $y2 <= $y1) {
				// do not crop
				$this->tmp_coords = false;
			} else {
				$this->tmp_coords = $coords;
				$this->tmp_coords['master_width'] = $master_width;
				$this->tmp_coords['master_height'] = $master_height;
			}

			if (!isset($icon_size['name'])) {
				$icon_size['name'] = $icon_name;
			}
			$this->tmp_icon_sizes = array(
				$icon_size['name'] => $icon_size,
			);
			$options = array(
				'icon_sizes' => $this->tmp_icon_sizes,
				'coords' => $this->tmp_coords,
			);

			elgg_register_plugin_hook_handler('entity:icon:sizes', 'object', array($this, 'getIconSizes'), 999);
			if (hypeApps()->iconFactory->create($upload, $_FILES[$shortname]['tmp_name'], $options)) {
				foreach (array('x1', 'x2', 'y1', 'y2') as $c) {
					$upload->{"_coord_{$ratio}_{$coord}"} = elgg_extract($c, $coords, 0);
					if ($ratio === 1) {
						$upload->$c = elgg_extract($c, $coords, 0);
					}
				}
			}
			
			elgg_unregister_plugin_hook_handler('entity:icon:sizes', 'object', array($this, 'getIconSizes'));
		}

		$upload->icontime = time();

		return $entity;
	}

	/**
	 * Callback for icon size hook
	 * We do not want to regenerate default icons
	 * @return array
	 */
	public function getIconSizes() {
		return $this->tmp_icon_sizes;
	}

}
