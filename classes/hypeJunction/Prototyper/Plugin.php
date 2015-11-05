<?php

namespace hypeJunction\Prototyper;

/**
 * Plugin DI wrapper
 *
 * @property-read \ElggPlugin                               $plugin
 * @property-read \hypeJunction\Prototyper\Config           $config
 * @property-read \hypeJunction\Prototyper\HookHandlers     $hooks
 * @property-read \hypeJunction\Prototyper\UI               $ui
 * @property-read \hypeJunction\Prototyper\EntityFactory    $entityFactory
 * @property-read \hypeJunction\Prototyper\FieldFactory     $fieldFactory
 * @property-read \hypeJunction\Prototyper\Prototype        $prototype
 * @property-read \hypeJunction\Prototyper\Form             $form
 * @property-read \hypeJunction\Prototyper\ActionController $action
 * @property-read \hypeJunction\Prototyper\Profile          $profile
 */
final class Plugin extends \hypeJunction\Plugin {

	/**
	 * Instance
	 * @var self
	 */
	static $instance;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(\ElggPlugin $plugin) {

		$this->setValue('plugin', $plugin);

		$this->setFactory('config', function (Plugin $p) {
			return new \hypeJunction\Prototyper\Config($p->plugin);
		});
		$this->setFactory('hooks', function (Plugin $p) {
			return new \hypeJunction\Prototyper\HookHandlers($p->config);
		});
		$this->setFactory('ui', function(Plugin $p) {
			return new \hypeJunction\Prototyper\UI($p->config);
		});

		$this->setClassName('entityFactory', '\hypeJunction\Prototyper\EntityFactory');

		$this->setFactory('fieldFactory', function(Plugin $p) {
			return new \hypeJunction\Prototyper\FieldFactory($p->config, $p->entityFactory);
		});

		$this->setFactory('prototype', function(Plugin $p) {
			return new \hypeJunction\Prototyper\Prototype($p->config, $p->entityFactory, $p->fieldFactory);
		});

		$this->setFactory('form', function(Plugin $p) {
			return new \hypeJunction\Prototyper\Form($p->config, $p->prototype, $p->entityFactory);
		});

		$this->setFactory('action', function(Plugin $p) {
			return new \hypeJunction\Prototyper\ActionController($p->config, $p->prototype, $p->entityFactory);
		});

		$this->setFactory('profile', function(Plugin $p) {
			return new \hypeJunction\Prototyper\Profile($p->config, $p->prototype, $p->entityFactory);
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public static function factory() {
		if (null === self::$instance) {
			$plugin = elgg_get_plugin_from_id('hypePrototyper');
			self::$instance = new self($plugin);
		}
		return self::$instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
		elgg_register_event_handler('init', 'system', array($this, 'init'));
	}

	/**
	 * Init callback
	 */
	public function init() {

		elgg_extend_view('css/elgg', 'css/framework/prototyper/stylesheet');
		elgg_extend_view('css/admin', 'css/framework/prototyper/stylesheet');

		elgg_register_css('jquery.cropper', '/mod/hypePrototyper/vendors/jquery.cropper/cropper.min.css');

		elgg_extend_view('prototyper/input/before', 'prototyper/elements/js');

		if (\hypeJunction\Integration::isElggVersionBelow('1.9.0')) {
			// Prototyper interface
			elgg_register_simplecache_view('js/framework/legacy/prototyper');
			elgg_register_js('prototyper', elgg_get_simplecache_url('js', 'framework/legacy/prototyper'), 'footer');

			elgg_register_js('jquery.cropper', '/mod/hypePrototyper/vendors/jquery.cropper/cropper.min.js', 'footer');

			elgg_register_simplecache_view('js/framework/legacy/prototyper_cropper');
			elgg_register_js('prototyper_cropper', elgg_get_simplecache_url('js', 'framework/legacy/prototyper_cropper'), 'footer');
		} else {
			elgg_define_js('cropper', array(
				'src' => '/mod/hypePrototyper/vendors/jquery.cropper/cropper.min.js',
				'deps' => array('jquery'),
			));
		}

		elgg_extend_view('input/file', 'prototyper/ui/cropper');

		hypePrototyper()->config->registerType('title', Elements\AttributeField::CLASSNAME, array(
			'shortname' => 'title',
			'input_view' => 'input/text',
			'output_view' => 'output/text',
			'value_type' => 'text',
			'required' => true,
		));
		hypePrototyper()->config->registerType('name', Elements\AttributeField::CLASSNAME, array(
			'shortname' => 'name',
			'input_view' => 'input/text',
			'output_view' => 'output/text',
			'value_type' => 'text',
			'required' => true,
		));

		hypePrototyper()->config->registerType('description', Elements\AttributeField::CLASSNAME, array(
			'shortname' => 'description',
			'input_view' => 'input/longtext',
			'output_view' => 'output/longtext',
			'value_type' => 'text',
		));

		hypePrototyper()->config->registerType('access', Elements\AttributeField::CLASSNAME, array(
			'shortname' => 'access_id',
			'value' => get_default_access(),
			'input_view' => 'input/access',
			'output_view' => 'output/access',
			'value_type' => 'int',
			'required' => true,
		));

		hypePrototyper()->config->registerType('text', Elements\MetadataField::CLASSNAME);

		hypePrototyper()->config->registerType('plaintext', Elements\MetadataField::CLASSNAME, array(
			'value_type' => 'text',
		));
		hypePrototyper()->config->registerType('longtext', Elements\MetadataField::CLASSNAME, array(
			'value_type' => 'text',
		));
		hypePrototyper()->config->registerType('hidden', Elements\MetadataField::CLASSNAME, array(
			'label' => false,
			'help' => false,
			'ui_sections' => array(
				'label' => false,
				'help' => false,
		)));

		hypePrototyper()->config->registerType('select', Elements\MetadataField::CLASSNAME, array(
			'ui_sections' => array(
				'optionsvalues' => true,
				'multiple' => true,
				'value' => false,
			)
		));

		hypePrototyper()->config->registerType('access', Elements\MetadataField::CLASSNAME);

		hypePrototyper()->config->registerType('checkboxes', Elements\MetadataField::CLASSNAME, array(
			'ui_sections' => array(
				'optionsvalues' => true,
				'value' => false,
			)
		));

		hypePrototyper()->config->registerType('radio', Elements\MetadataField::CLASSNAME, array(
			'ui_sections' => array(
				'multiple' => false,
				'optionsvalues' => true,
		)));

		hypePrototyper()->config->registerType('tags', Elements\MetadataField::CLASSNAME);

		hypePrototyper()->config->registerType('date', Elements\MetadataField::CLASSNAME, array(
			'timestamp' => false,
		));

		hypePrototyper()->config->registerType('time', Elements\MetadataField::CLASSNAME, array(
			'input_view' => 'input/prototyper/time',
			'format' => 'g:ia',
			'interval' => 900, // 15min
		));

		hypePrototyper()->config->registerType('email', Elements\MetadataField::CLASSNAME);

		hypePrototyper()->config->registerType('url', Elements\MetadataField::CLASSNAME);

		hypePrototyper()->config->registerType('stars', Elements\MetadataField::CLASSNAME, array(
			'value_type' => 'number',
			'ui_sections' => array(
				'validation' => false,
			)
		));

		hypePrototyper()->config->registerType('userpicker', Elements\RelationshipField::CLASSNAME, array(
			'value_type' => 'guid',
			'inverse_relationship' => false,
			'bilateral' => false,
			'ui_sections' => array(
				'relationship' => true,
				'value' => false,
			)
		));

		hypePrototyper()->config->registerType('friendspicker', Elements\RelationshipField::CLASSNAME, array(
			'value_type' => 'guid',
			'inverse_relationship' => false,
			'bilateral' => false,
			'ui_sections' => array(
				'relationship' => true,
				'value' => false,
			)
		));

		if (elgg_is_active_plugin('hypeCategories')) {
			hypePrototyper()->config->registerType('category', Elements\CategoryField::CLASSNAME, array(
				'value_type' => 'guid',
				'inverse_relationship' => false,
				'bilateral' => false,
				'multiple' => true,
				'ui_sections' => array(
					'multiple' => true,
					'relationship' => false,
					'value' => false,
				)
			));
		}

		hypePrototyper()->config->registerType('icon', Elements\IconField::CLASSNAME, array(
			'accept' => 'image/*',
			'value_type' => 'image',
			'input_view' => 'input/file',
			'output_view' => 'icon/default',
			'validation_rules' => array(
				'type' => 'image',
			),
			'ui_sections' => array(
				'value' => false,
			)
		));

		hypePrototyper()->config->registerType('upload', Elements\UploadField::CLASSNAME, array(
			'input_view' => 'input/file',
			'ui_sections' => array(
				'value' => false,
				'validation' => false,
			)
		));

		if (elgg_is_active_plugin('hypeDropzone')) {
			elgg_register_action('prototyper/multi/sort', $this->plugin->getPath() . 'actions/prototyper/multi/sort.php');
			hypePrototyper()->config->registerType('multi_upload', Elements\MultiUploadField::CLASSNAME, array(
				'multiple' => true,
				'input_view' => 'input/prototyper/multi_upload',
				'validation_rules' => array(
					'type' => 'guid',
				),
				'ui_sections' => array(
					'value' => false,
					'multiple' => false,
					'validation' => false,
				)
			));
		}

		hypePrototyper()->config->registerType('image_upload', Elements\ImageUploadField::CLASSNAME, array(
			'multiple' => false,
			'accept' => 'image/*',
			'value_type' => 'image',
			'input_view' => 'input/file',
			'validation_rules' => array(
				'type' => 'image',
			),
			'ui_sections' => array(
				'value' => false,
			)
		));

		elgg_extend_view('elements/forms/label', 'prototyper/input/before', 100);
		elgg_extend_view('elements/forms/help', 'prototyper/elements/field/foot');
		elgg_extend_view('elements/forms/help', 'prototyper/input/after');
	}

}
