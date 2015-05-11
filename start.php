<?php

/**
 * Form Prototyper
 *
 * @package hypeJunction
 * @subpackage Prototyper
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 */

namespace hypeJunction\Prototyper;

const PLUGIN_ID = 'hypePrototyper';

require_once __DIR__ . '/vendor/autoload.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

function init() {
	
	elgg_extend_view('css/elgg', 'css/framework/prototyper/stylesheet.css');
	elgg_extend_view('css/admin', 'css/framework/prototyper/stylesheet.css');
	
}
