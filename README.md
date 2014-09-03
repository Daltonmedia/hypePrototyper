hypePrototyper
==============

A set of developer and administrator tools for prototyping and handling
entity forms.


## Introduction

This library attempts to create an easy way to build forms. The motivation is to
provide an easy way to construct and render a form from a set of configuration
options, as well as to provide an easy way to validate and handle the form in
an action.


## Developer Notes

### Render a form

Forms are tied to registered actions. To render a form for 'profile/edit' for a
logged in user, you would use:

```php

$user = elgg_get_logged_in_user_entity();

$prototype = new Prototype($user);
echo $prototype->form('profile/edit')->viewBody();
```

You would then register a plugin hook for ```"prototype","$action"``` and
provide a list of fields, which can vary based on entity properties:

```php

elgg_register_plugin_hook_handler('prototype', 'profile/edit', 'prepare_profile_edit_form');

function prepare_profile_edit_form($hook, $type, $return, $params) {

	if (!is_array($return)) {
		$return = array();
	}

	$entity = elgg_extract('entity', $params);

	$fields = array(
		'name' => array(
			'type' => 'text',
			'validation_rules' => array(
				'max_length' => 50
			)
		),
		'briefdescription' => 'longtext',
		'interests' => array(
			'type' => 'tags',
			'required' => true,
			'show_access' => ACCESS_PRIVATE,
		),
		'favorite_foods' => array(
			'type' => 'text',
			'multiple' => true,
			'show_access' => true,
		),
		'eye_color' => array(
			'type' => 'dropdown',
			'label' => elgg_echo('eye_color'),
			'options_values' => array(
				'blue' => elgg_echo('blue'),
				'brown' => elgg_echo('brown'),
			),
		),
		'looking_for' => array(
			'type' => 'checkboxes',
			'label' => false,
			'help' => false,
			'options' => 'profile_get_looking_for_options'
		),
		'height' => array(
			'type' => 'text',
			'value_type' => 'number',
			'multiple' => false,
			'show_access' => false,
			'required' => true,
			'validation_rules' => array(
				'min' => 25,
				'max' => 50,
				'minlength' => 2,
				'maxlength' => 4,
			),
		),
		'empathy' => array(
			'type' => 'stars',
			'data_type' => 'annotation',
			'min' => 0,
			'max' => 10,
		),
		'spouse' => array(
			'type' => 'autocomplete',
			'data_type' => 'relationship',
			'value_type' => 'guid',
			'inverse_relationship' => false,
			'bilateral' => true,
			'match_on' => 'friends',
		),
		'icon' => array(
			'data_type' => 'icon',
		),
	);

	return array_merge($return, $fields);
}

```

Fields are defined as ```$shortname => $value``` pairs, where the ```$shortname``` is a
name of an attribute, metadata, annotation etc. and ```$value``` is
a string that describes the input type (e.g. text, dropdown etc) or an array
with the following properties:

* ```type``` - type of an input, used as elgg_view("input/$type") (default ```text```)
* ```data_type``` - a model used to store and retrieve values (default ```metadata```)
	> ```attribute``` - an entity attribute, e.g. guid
	> ```metadata``` - an entity metadata
	> ```annotation``` - an entity annotation
	> ```relationship``` - an entity relationship
	> ```icon``` - an entity icon
	> ```cover``` - an entity cover image
	> ```category``` - entity categories (hypeCategories)
* ```value_type``` - type of value if different from ```type```, e.g when a text input expects a number
* ```input_view``` - view used to dipslay an input, if different from "input/$type"
* ```output_view``` - view used to dipslay an output, if different from "output/$type"
* ```required``` - whether or not a user input is requried (default ```false```)
* ```show_access``` - whether or not to display an access input (default ```false```)
	This allows users to specify an access level for the metadata, annotation or attachment created
* ```label``` - what label to display with the input field (default ```true```)
	> ```true``` - set to ```elgg_echo("label:$type:$subtype:$shortname")```;
	> ```false``` - do not display a label
	> any other custom string
* ```help``` - what help text to display with the input field (default ```true```)
	> ```true``` - set to ```elgg_echo("help:$type:$subtype:$shortname")```;
	> ```false``` - do not display help text
	> any other custom string
* ```multiple``` - whether or not a user can clone the field and add multiple values (default ```false```)
* ```validation_rules``` - an array of rule => expecation pairs
	Preset validators are max, min, maxlength, minlength
	You can define custom validation rules and use ```'validate:rule','prototyper'``` to validate the values
* ```options``` and ```options_values``` can be defined as a callback function name
* all other options will be passed to the input view, so you can add ```class``` for example

The following options are available for ```relationship``` data type:
* ```inverse_relationship``` - store as inverse relationship
* ```bilateral``` - make it a bilateral relationship (two relationships will be added)

The following fields will be appended to all forms:
* ```guid```
* ```type```
* ```subtype```

### Handle form

Now in your action file, all you need to do is use:

```php

$guid = get_input('guid');

$prototype = new Prototype($guid);
$result = $prototype->action('profile/edit', true);

```

The above will validate the form and add all values to the entity based on the
```data_type``` you have specified. If the form validation fails, the user
will be forwarded back to the form (forms are made sticky) and failing validation
rules will be explained.

