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

		elgg_extend_view('prototyper/input/before', 'prototyper/elements/js');

		if (\hypeJunction\Integration::isElggVersionBelow('1.9.0')) {
			// Prototyper interface
			elgg_register_simplecache_view('js/framework/legacy/prototyper');
			elgg_register_js('prototyper', elgg_get_simplecache_url('js', 'framework/legacy/prototyper'), 'footer');
		}

		hypePrototyper()->config->registerType('text', Elements\MetadataField::CLASSNAME);
		hypePrototyper()->config->registerType('text', Elements\AnnotationField::CLASSNAME);

		hypePrototyper()->config->registerType('plaintext', Elements\MetadataField::CLASSNAME, array(
			'value_type' => 'text',
		));
		hypePrototyper()->config->registerType('longtext', Elements\MetadataField::CLASSNAME, array(
			'value_type' => 'text',
		));
		hypePrototyper()->config->registerType('hidden', Elements\MetadataField::CLASSNAME, array(
			'multiple' => false,
			'required' => false,
			'show_access' => false,
			'label' => false,
			'help' => false,
			'ui_sections' => array(
				'required' => false,
				'access' => false,
				'multiple' => false,
				'label' => false,
				'help' => false,
		)));

		hypePrototyper()->config->registerType('select', Elements\MetadataField::CLASSNAME, array(
			'ui_sections' => array(
				'optionsvalues' => true,
			)
		));

		hypePrototyper()->config->registerType('access', Elements\MetadataField::CLASSNAME, array(
			'ui_sections' => array(
				'optionsvalues' => false,
			)
		));

		hypePrototyper()->config->registerType('checkboxes', Elements\MetadataField::CLASSNAME, array(
			'ui_sections' => array(
				'multiple' => false,
				'optionsvalues' => true,
			)
		));

		hypePrototyper()->config->registerType('radio', Elements\MetadataField::CLASSNAME, array(
			'ui_sections' => array(
				'multiple' => false,
				'optionsvalues' => true,
		)));

		hypePrototyper()->config->registerType('tags', Elements\MetadataField::CLASSNAME, array(
			'ui_sections' => array(
				'multiple' => false,
		)));

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
		hypePrototyper()->config->registerType('stars', Elements\AnnotationField::CLASSNAME, array(
			'value_type' => 'number',
			'ui_sections' => array(
				'validation' => false,
			)
		));

		hypePrototyper()->config->registerType('userpicker', Elements\RelationshipField::CLASSNAME, array(
			'value_type' => 'guid',
			'inverse_relationship' => false,
			'bilateral' => false,
			'multiple' => false,
			'show_access' => false,
			'ui_sections' => array(
				'access' => false,
				'multiple' => false,
				'relationship' => true,
			)
		));

		hypePrototyper()->config->registerType('friendspicker', Elements\RelationshipField::CLASSNAME, array(
			'value_type' => 'guid',
			'inverse_relationship' => false,
			'bilateral' => false,
			'multiple' => false,
			'show_access' => false,
			'ui_sections' => array(
				'access' => false,
				'multiple' => false,
				'relationship' => true,
			)
		));

		if (elgg_is_active_plugin('hypeCategories')) {
			hypePrototyper()->config->registerType('category', Elements\CategoryField::CLASSNAME, array(
				'value_type' => 'guid',
				'inverse_relationship' => false,
				'bilateral' => false,
				'multiple' => true,
				'show_access' => false,
				'ui_sections' => array(
					'access' => false,
					'multiple' => true,
					'relationship' => false,
				)
			));
		}

		hypePrototyper()->config->registerType('icon', Elements\IconField::CLASSNAME, array(
			'accept' => 'image/*',
			'value_type' => 'image',
			'multiple' => false,
			'show_access' => false,
			'input_view' => 'input/file',
			'output_view' => false,
			'ui_sections' => array(
				'value' => false,
				'access' => false,
				'multiple' => false,
			)
		));

		hypePrototyper()->config->registerType('upload', Elements\UploadField::CLASSNAME, array(
			'multiple' => false,
			'show_access' => false,
			'input_view' => 'input/file',
			'ui_sections' => array(
				'value' => true,
				'access' => false,
				'multiple' => false,
				'validation' => true
			)
		));

		hypePrototyper()->config->registerType('image_upload', Elements\ImageUploadField::CLASSNAME, array(
			'multiple' => false,
			'accept' => 'image/*',
			'show_access' => false,
			'input_view' => 'input/file',
			'validation_rules' => array(
				'type' => 'image',
			),
			'ui_sections' => array(
				'value' => true,
				'access' => false,
				'multiple' => false,
				'validation' => true
			)
		));
	}

}
