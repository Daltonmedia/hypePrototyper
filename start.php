<?php

/**
 * Form Prototyper
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 */

try {
	require_once __DIR__ . '/lib/autoloader.php';
	hypePrototyper()->boot();
} catch (Exception $ex) {
	register_error($msg);
}