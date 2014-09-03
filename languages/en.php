<?php

namespace hypeJunction\Prototyper;

$english = array(

	'prototyper:required' => 'Required',

	'prototyper:validate:error' => 'There were some problems with your submission. Please fix the errors and retry.',
	'prototyper:validate:error:required' => 'Required field %s can not be empty',
	'prototyper:validate:error:value_type:number' => 'Field %s expects a numeric value',
	'prototyper:validate:error:value_type:entity' => 'Field %s expects a valid entity',
	'prototyper:validate:error:value_type:image' => 'Field %s expects an image file',
	'prototyper:validate:error:min' => 'Field %s expects a minimum value of %s',
	'prototyper:validate:error:max' => 'Field %s expects a maximum value of %s',
	'prototyper:validate:error:minlength' => 'Field %s can not be shorter than %s characters',
	'prototyper:validate:error:maxlength' => 'Field %s can not be longer than %s characters',
	'prototyper:handle:error' => 'Submission failed with the following error: %s',
	
	'prototyper:action:error' => 'Action could not be successfully completed',
	'prototyper:action:success' => 'Action was successful',
);

add_translation('en', $english);
