<?php

if (\hypeJunction\Integration::isElggVersionBelow('1.9.0')) {
	elgg_load_js('prototyper');
} else {
	elgg_require_js('framework/prototyper');
}