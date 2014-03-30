<?php
/*
WordPress Settings API Wrapper Class
URI: http://github.com/harishdasari
Author: Harish Dasari
Author URI: http://twitter.com/harishdasari
Version: 1.1
*/

/*=================================================================================
	WordPress Settings API Wrapper Class
 =================================================================================*/

require_once( 'class-hd-html-helper.php' );

if ( ! class_exists( 'HD_WP_Settings_API' ) ) :
/**
 * WordPress Settings API Wrapper Class
 *
 * @version 1.1
 * @author  Harish Dasari
 * @link    http://github.com/harishdasari
 */
class HD_WP_Settings_API {

	/**
	 * Holds Options for Menu Page
	 * @var array
	 */
	var $options         = array();

	/**
	 * Holds Settings fields data
	 * @var array
	 */
	var $fields          = array();

	/**
	 * Holds Tab Options
	 * @var array
	 */
	var $tabs            = array();

	/**
	 * Holds Section ID to addubg settings fields
	 * @var string
	 */
	var $current_section = 'default';

	/**
	 * Holds Tab ID to adding settings sections and settings fields
	 * @var boolean/string
	 */
	var $current_tab     = false;

	/**
	 * Holds Active Tab ID
	 * @var boolean/string
	 */
	var $active_tab      = false;

	/**
	 * Holds Current field data to adding settings field
	 * @var mixed
	 */
	var $current_field   = false;

	/**
	 * Holds Menu page $hook_suffix
	 * @var boolean/string
	 */
	var $hook_suffix     = false;

	/**
	 * Holds instance of HD_HTML_Helper class
	 * @var object
	 */
	var $html_helper;

	/**
	 * Holds Current Folder Path
	 * @var string
	 */
	var $dir_path;

	/**
	 * Holds Current Folder URI
	 * @var string
	 */
	var $dir_uri;

	/**
	 * Constructor
	 *
	 * @param array $options
	 * @param array $fields
	 * @return null
	 */
	function __construct( $options = array(), $fields = array() ) {

		// Set directory path
		$this->dir_path = str_replace( '\\', '/', dirname( __FILE__ ) );

		// Set directory uri
		$this->dir_uri  = trailingslashit( home_url() ) . str_replace( str_replace( '\\', '/', ABSPATH ), '', $this->dir_path );

		// Default page options
		$options_default = array(
			'page_title'  => '',
			'menu_title'  => '',
			'menu_slug'   => '',
			'parent_slug' => '',
			'capability'  => 'manage_options',
			'icon'        => 'dashicons-admin-generic',
			'position'    => null,
		);

		$this->options = wp_parse_args( $options, $options_default );

		extract( $this->options );

		// Titles and slugs should not be empty
		if ( empty( $page_title ) || empty( $menu_title ) || empty( $menu_slug ) )
			return false;

		$this->fields  = (array) $fields;

		$this->html_helper = class_exists( 'HD_HTML_Helper' ) ? new HD_HTML_Helper : false;

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_options' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );
		add_action( 'admin_notices', array( $this, 'show_notices' ) );

	}

	/**
	 * Register a New Menu Page
	 *
	 * @return null
	 */
	function register_menu() {

		// Collect all tabs
		foreach ( $this->fields as $field_setting => $field )
			if ( 'tab' == $field['type'] )
				$this->tabs[ sanitize_title( $field_setting ) ] = $field['title'];

		// Set active tab
		if ( ! empty( $this->tabs ) ) {
			if ( isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], (array) $this->tabs ) )
				$this->active_tab = $_GET['tab'];
			elseif ( isset( $_REQUEST[ $this->options['menu_slug'] . '_active_tab' ] ) && array_key_exists( $_REQUEST[ $this->options['menu_slug'] . '_active_tab' ], (array) $this->tabs ) )
				$this->active_tab = $_REQUEST[ $this->options['menu_slug'] . '_active_tab' ];
			else {
				$tab_keys = array_keys( (array) $this->tabs );
				$this->active_tab = reset( $tab_keys );
			}
		}

		extract( $this->options );

		if ( empty( $parent_slug ) )
			$this->hook_suffix = add_menu_page( $page_title, $menu_title, $capability, $menu_slug, array( $this, 'settings_page' ), $icon, $position );
		else
			$this->hook_suffix = add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, array( $this, 'settings_page' ) );

	}

	/**
	 * Enqueue Styles and Scripts
	 *
	 * @param  string $hook_suffix
	 * @return null
	 */
	function enqueue_styles_scripts( $hook_suffix ) {

		if ( $this->hook_suffix !== $hook_suffix )
			return;

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'hd-html-helper', $this->dir_uri . '/js/admin.js', array( 'jquery', 'wp-color-picker' ), null, true );

	}

	/**
	 * Register Sections, Fields and Settings
	 *
	 * @return mull
	 */
	function register_options() {

		foreach ( $this->fields as $field_setting => $field ) {

			$field['id'] = $field_setting;

			$this->current_field = $field;

			if ( 'tab' == $field['type'] ) {

				$this->current_tab = $field['id'];
				$this->current_section = 'default';

			} elseif ( 'section' == $field['type'] ) {

				$this->current_section = empty( $field['id'] ) ? 'default' : $field['id'] ;

				if ( empty( $this->current_tab ) )
					add_settings_section( $field['id'], $field['title'], array( $this, 'print_section' ), $this->options['menu_slug'] );
				else
					add_settings_section( $field['id'], $field['title'], array( $this, 'print_section' ), $this->options['menu_slug'] . '_' . $this->current_tab );

			} elseif ( in_array( $field['type'], array( 'text', 'textarea', 'select', 'checkbox', 'radio', 'multiselect', 'multicheck', 'upload', 'color', 'editor' ) ) ) {

				// Set Field Value
				$field['value'] = get_option( $field['id'] );

				if ( empty( $this->current_tab ) )
					add_settings_field( $field['id'], $field['title'], array( $this->html_helper, 'display_field' ), $this->options['menu_slug'], $this->current_section, $field );
				else
					add_settings_field( $field['id'], $field['title'], array( $this->html_helper, 'display_field' ), $this->options['menu_slug'] . '_' . $this->current_tab, $this->current_section, $field );

				if ( empty( $this->current_tab ) || $this->current_tab == $this->active_tab )
					register_setting( $this->options['menu_slug'], $field['id'], array( $this, 'sanitize_setting' ) );

				if ( ! empty( $field['default'] ) )
					add_option( $field['id'], $field['default'] );

			}

		}

	}

	/**
	 * Show Admin Notices
	 *
	 * @return null
	 */
	function show_notices() {

		global $parent_file;

		if ( 'options-general.php' == $parent_file )
			return;

		if ( isset( $_GET['page'] ) && $_GET['page'] == $this->options['menu_slug'] )
			settings_errors();

	}

	/**
	 * Print Settings Page
	 *
	 * @return null
	 */
	function settings_page() {

		?>
		<div class="wrap <?php echo sanitize_html_class( $this->options['menu_slug'] ); ?>">

			<h2><?php echo esc_html( $this->options['page_title'] ); ?></h2>

			<form action="<?php echo admin_url( 'options.php' ) ?>" method="post">

				<?php settings_fields( $this->options['menu_slug'] ); ?>

				<?php do_action( 'hd_settings_api_page_before', $this->hook_suffix, $this->options, $this->fields ); ?>

				<table class="form-table">
					<?php do_settings_fields( $this->options['menu_slug'], 'default' ); ?>
				</table>

				<?php do_settings_sections( $this->options['menu_slug'] ); ?>

				<?php if ( ! empty( $this->tabs ) ) { ?>
					<h2 class="nav-tab-wrapper">
						<?php
						foreach ( (array) $this->tabs as $tab_id => $tab_name )
							printf(
								'<a href="%s" class="nav-tab%s">%s</a>',
								add_query_arg( array( 'page' => $this->options['menu_slug'], 'tab' => $tab_id ) ),
								( $this->active_tab == $tab_id ) ? ' nav-tab-active' : '',
								esc_html( $tab_name )
							);
						?>
					</h2>

					<?php do_action( 'hd_settings_api_tab_before', $this->hook_suffix, $this->active_tab, $this->options, $this->fields ); ?>

					<table class="form-table">
						<?php do_settings_fields( $this->options['menu_slug'] . '_' . $this->active_tab, 'default' ); ?>
					</table>

					<?php do_settings_sections( $this->options['menu_slug'] . '_' . $this->active_tab ); ?>

					<?php do_action( 'hd_settings_api_tab_after', $this->hook_suffix, $this->active_tab, $this->options, $this->fields ); ?>

					<input type="hidden" name="<?php echo esc_attr( $this->options['menu_slug'] . '_active_tab' ); ?>" value="<?php echo esc_attr( $this->active_tab ); ?>"/>

				<?php } ?>

				<?php do_action( 'hd_settings_api_page_after', $this->hook_suffix, $this->options, $this->fields ); ?>

				<div class="clear"></div>

				<?php submit_button( apply_filters( 'hd_settings_api_save_button_text', __( 'Save Changes' ) ) ); ?>

			</form>

		</div>
		<?php

	}

	/**
	 * Print Settings Section
	 *
	 * @param  array $args Section Options
	 * @return null
	 */
	function print_section( $args ) {

		if ( isset( $this->fields[ $args['id'] ]['desc'] ) )
			echo $this->fields[ $args['id'] ]['desc'];

	}

	/**
	 * Sanitize settings
	 *
	 * @param  mixed $new_value Submitted new value
	 * @return mixed            Sanitized value
	 */
	function sanitize_setting( $new_value ) {

		$setting = str_replace( 'sanitize_option_', '', current_filter() );

		$field = $this->fields[ $setting ];

		if ( ! isset( $field['sanit'] ) )
			$field['sanit'] = '';

		switch ( $field['sanit'] ) {

			case 'int' :
				return is_array( $new_value ) ? array_map( 'intval', $new_value ) : intval( $new_value );
				break;

			case 'absint' :
				return is_array( $new_value ) ? array_map( 'absint', $new_value ) : absint( $new_value );
				break;

			case 'email' :
				return is_array( $new_value ) ? array_map( 'sanitize_email', $new_value ) : sanitize_email( $new_value );
				break;

			case 'url' :
				return is_array( $new_value ) ? array_map( 'esc_url_raw', $new_value ) : esc_url_raw( $new_value );
				break;

			case 'bool' :
				return (bool) $new_value;
				break;

			case 'color' :
				return $this->sanitize_hex_color( $new_value );
				break;

			case 'html' :
				if ( current_user_can( 'unfiltered_html' ) )
					return is_array( $new_value ) ? array_map( 'wp_kses_post', $new_value ) : wp_kses_post( $new_value );
				else
					return is_array( $new_value ) ? array_map( 'wp_strip_all_tags', $new_value ) : wp_strip_all_tags( $new_value );
				break;

			case 'nohtml' :
				return is_array( $new_value ) ? array_map( 'wp_strip_all_tags', $new_value ) : wp_strip_all_tags( $new_value );
				break;

			default :
				return apply_filters( 'hd_settings_api_sanitize_option', $new_value, $field, $setting );
				break;

		}

	}

	/**
	 * Sanitize Hex Color (taken from WP Core)
	 *
	 * @param  string $color Hex Color
	 * @return mixed         Sanitized Hex Color or null
	 */
	function sanitize_hex_color( $color ) {

		if ( '' === $color )
			return '';

		if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) )
			return $color;

		return null;

	}

} // HD_WP_Settings_API end

endif; // class_exists check