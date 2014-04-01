
##WordPress Settings API Wrapper Class

_**Note: This is not a WordPress Plugin. This is a PHP Library for creating option pages for WordPress themes and plugins.**_

Creating Options page for WordPress is now hassel free. With this PHP class you can create Top level menu pages and sub menu pages.

This class uses WordPress native Settings API and can be used in both Themes and Plugins.


You can add 10 different types of Input fields and Section also it supports WordPress tabbed interface.

###Types of Inputs Suported
1. Text
2. Textarea
3. Checkbox
4. Radio
5. Select
6. Multi-Select
7. Multi-Checkbox
8. Upload
9. Color
10. Editor

###Screenshot

![WP Settings API Screenshot](https://raw.github.com/harishdasari/WP-Settings-API-Wrapper-Class/master/screenshot.png)


###Installation
Copy the Directory `hd-wp-settings-api` into your theme or plugin folder.

Include the following code in your theme `functions.php` file or plugin file.

	require_once( 'hd-wp-settings-api/class-hd-wp-settings-api.php' );

###Usage
First you need to create

1. Defining options for Menu Page
2. Defining input fields array
3. Initializing Settings API Class using above options.

####Defining Options for Menu Page
To create a top level menu page use following

	$example_options = array(
		'page_title'  => 'Example Options Page',
		'menu_title'  => 'Example Options',
		'menu_slug'   => 'hd_example_options',
		'capability'  => 'manage_options',
		'icon'        => 'dashicons-admin-generic',
		'position'    => 61
	);

**OR**

To Create a sub menu page use following

	$example_options = array(
		'page_title'  => 'Example Options Page',
		'menu_title'  => 'Example Options',
		'parent_slug' => 'themes.php',
		'menu_slug'   => 'hd_example_options',
		'capability'  => 'manage_options',
	);

####Creating Input Field Options
First create an empty array

	$example_fields = array();

Add **input** field options and give unique `option name` as key.

	$example_fields = array(
		'hd_text_setting' => array(
			'title'   => 'Text Input',
			'type'    => 'text',
			'default' => 'Hello World!',
			'desc'    => 'Example Text Input',
			'sanit'   => 'nohtml',
		)
	);

Alternatively you can add **Sections** and **Tabs** as well.

####Initialize
Initialize the Settings Class using above defined options and fields.

	$example_settings = new HD_WP_Settings_API( $example_options, $example_fields );


Full list of input field emamples, sections and tabs.

1. **To add Text Input**

		'hd_text_setting' => array(
			'title'   => 'Text Input',
			'type'    => 'text',
			'default' => 'Hello World!',
			'desc'    => 'Example Text Input',
			'sanit'   => 'nohtml',
		)

2. **To add Textarea Input**

		'hd_textarea_setting' => array(
			'title'   => 'Textarea Input',
			'type'    => 'textarea',
			'default' => 'Hello World!',
			'desc'    => 'Example Textarea Input',
			'sanit'   => 'nohtml',
		)

3. **To add Checkbox Input**

		'hd_checkbox_setting' => array(
			'title'   => 'Checkbox Input',
			'type'    => 'checkbox',
			'default' => 1,
			'desc'    => 'Example Checkbox Input',
			'sanit'   => 'nohtml',
		)

4. **To add Radio Input**

		'hd_radio_setting' => array(
			'title'   => 'Radio Input',
			'type'    => 'radio',
			'default' => 'one',
			'choices' => array(
				'one'   => 'Option 1',
				'two'   => 'Option 2',
				'three' => 'Option 3'
			),
			'desc'    => 'Example Radio Input',
			'sanit'   => 'nohtml',
		)

5. **To add Select Input**

		'hd_select_setting' => array(
			'title'   => 'Select Input',
			'type'    => 'select',
			'default' => 'two',
			'choices' => array(
				'one'   => 'Option 1',
				'two'   => 'Option 2',
				'three' => 'Option 3'
			),
			'desc'    => 'Example Select Input',
			'sanit'   => 'nohtml',
		)

6. **To add Multi-Select Input**

		'hd_multiselect_setting' => array(
			'title'   => 'Multi Select Input',
			'type'    => 'select',
			'default' => array( 'one', 'three' ),
			'choices' => array(
				'one'   => 'Option 1',
				'two'   => 'Option 2',
				'three' => 'Option 3'
			),
			'multiple' => true,
			'desc'     => 'Example Multi Select Input',
			'sanit'    => 'nohtml',
		)

7. **To add Multi-Checkbox Input**

		'hd_multicheck_setting' => array(
			'title'   => 'Multi Checkbox Input',
			'type'    => 'multicheck',
			'default' => array( 'one', 'three' ),
			'choices' => array(
				'one'   => 'Option 1',
				'two'   => 'Option 2',
				'three' => 'Option 3'
			),
			'desc'    => 'Example Multi Checkbox Input',
			'sanit'   => 'nohtml',
		)

8. **To add Upload Input**

		'hd_upload_setting' => array(
			'title'   => 'Upload Input',
			'type'    => 'upload',
			'default' => '',
			'desc'    => 'Example Upload Input',
			'sanit'   => 'url',
		)

9. **To add Color Input**

		'hd_color_setting' => array(
			'title'   => 'Color Input',
			'type'    => 'color',
			'default' => '#ffffff',
			'desc'    => 'Example Color Input',
			'sanit'   => 'color',
		)

10. **To add TinyMCE Editor Input**

		'hd_editor_setting' => array(
			'title'   => 'Editor Input',
			'type'    => 'editor',
			'default' => '',
			'desc'    => 'Example Editor Input',
			'sanit'   => 'nohtml',
		)

11. **To add Section**

		'hd_section_id' => array(
			'title'   => 'Example Section',
			'type'    => 'section',
			'desc'    => 'Section Description goes here',
		)

12. **To add Tab**

		'hd_tab_id' => array(
			'title' => 'Tab Title',
			'type'  => 'tab',
		)


###Full Example

	<?php

	require_once( 'hd-wp-settings-api/hd-wp-settings-api.php' );

	$example_options = array(
		'page_title'  => 'Example Options Page',
		'menu_title'  => 'Example Options',
		'parent_slug' => 'themes.php',
		'menu_slug'   => 'hd_example_options',
		'capability'  => 'manage_options',
		'icon'        => 'dashicons-admin-generic',
	);

	$example_fields = array(
		'hd_text_setting' => array(
			'title'   => 'Text Input',
			'type'    => 'text',
			'default' => 'Hello World!',
			'desc'    => 'Example Text Input',
			'sanit'   => 'nohtml',
		),
		'hd_textarea_setting' => array(
			'title'   => 'Textarea Input',
			'type'    => 'textarea',
			'default' => 'Hello World!',
			'desc'    => 'Example Textarea Input',
			'sanit'   => 'nohtml',
		),
		'hd_section_1' => array(
			'title'   => 'Example Section',
			'type'    => 'section',
			'desc'    => 'Section Description goes here',
		),
		'hd_checkbox_setting' => array(
			'title'   => 'Checkbox Input',
			'type'    => 'checkbox',
			'default' => 1,
			'desc'    => 'Example Checkbox Input',
			'sanit'   => 'nohtml',
		),
		'hd_radio_setting' => array(
			'title'   => 'Radio Input',
			'type'    => 'radio',
			'default' => 'one',
			'choices' => array(
				'one'   => 'Option 1',
				'two'   => 'Option 2',
				'three' => 'Option 3'
			),
			'desc'    => 'Example Radio Input',
			'sanit'   => 'nohtml',
		),
		'hd_tab_1'  => array(
			'title' => 'Tab 1',
			'type' => 'tab',
		),
		'hd_select_setting' => array(
			'title'   => 'Select Input',
			'type'    => 'select',
			'default' => 'two',
			'choices' => array(
				'one'   => 'Option 1',
				'two'   => 'Option 2',
				'three' => 'Option 3'
			),
			'desc'    => 'Example Select Input',
			'sanit'   => 'nohtml',
		),
		'hd_multiselect_setting' => array(
			'title'   => 'Multi Select Input',
			'type'    => 'select',
			'default' => array( 'one', 'three' ),
			'choices' => array(
				'one'   => 'Option 1',
				'two'   => 'Option 2',
				'three' => 'Option 3'
			),
			'multiple' => true,
			'desc'     => 'Example Multi Select Input',
			'sanit'    => 'nohtml',
		),
		'hd_multicheck_setting' => array(
			'title'   => 'Multi Checkbox Input',
			'type'    => 'multicheck',
			'default' => array( 'one', 'three' ),
			'choices' => array(
				'one'   => 'Option 1',
				'two'   => 'Option 2',
				'three' => 'Option 3'
			),
			'desc'    => 'Example Multi Checkbox Input',
			'sanit'   => 'nohtml',
		),
		'hd_tab_2' => array(
			'title' => 'Tab 2',
			'type'  => 'tab',
		),
		'hd_upload_setting' => array(
			'title'   => 'Upload Input',
			'type'    => 'upload',
			'default' => '',
			'desc'    => 'Example Upload Input',
			'sanit'   => 'url',
		),
		'hd_color_setting' => array(
			'title'   => 'Color Input',
			'type'    => 'color',
			'default' => '#ffffff',
			'desc'    => 'Example Color Input',
			'sanit'   => 'color',
		),
		'hd_editor_setting' => array(
			'title'   => 'Editor Input',
			'type'    => 'editor',
			'default' => '',
			'desc'    => 'Example Editor Input',
			'sanit'   => 'nohtml',
		),
	);

	$example_settings = new HD_WP_Settings_API( $example_options, $example_fields );


### Actions and Filters

**Actions**


1. `add_action( 'hd_settings_api_page_before', 'function_name' );`

	Callback arguments : `$hook_suffix`, `$options`, `$fields`

2. `add_action( 'hd_settings_api_page_before', 'function_name' );`

	Callback arguments : `$hook_suffix`, `$options`, `$fields`

3. `add_action( 'hd_settings_api_tab_before', 'function_name' );`

	Callback arguments : `$hook_suffix`, `$active_tab_id`, `$options`, `$fields`

4. `add_action( 'hd_settings_api_tab_after', 'function_name' );`

	Callback arguments : `$hook_suffix`, `$active_tab_id`, `$options`, `$fields`



**Filters**

1. `add_filter( 'hd_settings_api_save_button_text', 'function_name' );`

	Callback arguments : `$button_text`

2. `add_filter( 'hd_settings_api_sanitize_option', 'function_name' );`

	Callback arguments : `$new_value`, `$field`, `$setting`

3. `add_filter( 'hd_html_helper_input_field', 'function_name' );`

	Callback arguments : `$input_html`, `$field`, `$show_help`


Note: where `function_name` is a callback function

###License
GNU General Public License v2.0 or later | [http://www.opensource.org/licenses/gpl-license.php](http://www.opensource.org/licenses/gpl-license.php)

<hr/>

Please post your suggetions and requests in issues, and also help me to imrpove this documenration.

Thank You <br/>
-- _Harish Dasari_ <br/>
[@harishdasari](http://twitter.com/harishdasari)
