<?php
/*
WordPress Settings API Wrapper Class
URI: http://github.com/harishdasari
Author: Harish Dasari
Author URI: http://twitter.com/harishdasari
Version: 1.0
*/

/*=================================================================================
	WordPress Settings API Wrapper Class
 =================================================================================*/

/**
 * WordPress Settings API Wrapper Class
 *
 * @version 1.0
 * @author Harish Dasari
 */
class HD_WP_Settings_Wrapper {

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
		$this->dir_path = dirname( __FILE__ );

		// Set directory uri
		$this->dir_uri  = trailingslashit( home_url() ) . str_replace( ABSPATH, '', $this->dir_path );

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

		add_action( 'admin_menu', array( $this, 'hd_register_menu' ) );
		add_action( 'admin_init', array( $this, 'hd_register_sfs' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'hd_enqueue_styles_scripts' ) );

	}

	/**
	 * Register a New Menu Page
	 *
	 * @return null
	 */
	function hd_register_menu() {

		// Collect all tabs
		foreach ( $this->fields as $field_setting => $field )
			if ( 'tab' == $field['type'] )
				$this->tabs[ $field_setting ] = $field['title'];

		// Set active tab
		if ( ! empty( $this->tabs ) ) {
			if ( isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], (array) $this->tabs ) )
				$this->active_tab = $_GET['tab'];
			elseif ( isset( $_REQUEST[ $this->options['menu_slug'] . '_active_tab' ] ) && array_key_exists( $_REQUEST[ $this->options['menu_slug'] . '_active_tab' ], (array) $this->tabs ) )
				$this->active_tab = $_REQUEST[ $this->options['menu_slug'] . '_active_tab' ];
			else
				$this->active_tab = reset( array_keys( $this->tabs ) );
		}

		extract( $this->options );

		if ( empty( $parent_slug ) )
			$this->hook_suffix = add_menu_page( $page_title, $menu_title, $capability, $menu_slug, array( $this, 'hd_settings_page' ), $icon, $position );
		else
			$this->hook_suffix = add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, array( $this, 'hd_settings_page' ) );

	}

	/**
	 * Enqueue Styles and Scripts
	 *
	 * @param  string $hook_suffix
	 * @return null
	 */
	function hd_enqueue_styles_scripts( $hook_suffix ) {

		if ( $this->hook_suffix !== $hook_suffix )
			return;

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'hd-settings-wrapper', $this->dir_uri . '/js/admin.js', array( 'jquery', 'wp-color-picker' ), null, true );

	}

	/**
	 * Register Sections, Fields and Settings
	 *
	 * @return mull
	 */
	function hd_register_sfs() {

		foreach ( $this->fields as $field_setting => $field ) {

			$field['id'] = $field_setting;

			$this->current_field = $field;

			if ( 'tab' == $field['type'] ) {

				$this->current_tab = $field['id'];
				$this->current_section = 'default';

			} elseif ( 'section' == $field['type'] ) {

				$this->current_section = empty( $field['id'] ) ? 'default' : $field['id'] ;

				if ( empty( $this->current_tab ) )
					add_settings_section( $field['id'], $field['title'], array( $this, 'hd_print_section' ), $this->options['menu_slug'] );
				else
					add_settings_section( $field['id'], $field['title'], array( $this, 'hd_print_section' ), $this->options['menu_slug'] . '_' . $this->current_tab );

			} elseif ( in_array( $field['type'], array( 'text', 'textarea', 'select', 'checkbox', 'radio', 'multiselect', 'multicheck', 'upload', 'color', 'editor' ) ) ) {

				if ( empty( $this->current_tab ) )
					add_settings_field( $field['id'], $field['title'], array( $this, 'hd_print_' . $field['type'] . '_input' ), $this->options['menu_slug'], $this->current_section, $field );
				else
					add_settings_field( $field['id'], $field['title'], array( $this, 'hd_print_' . $field['type'] . '_input' ), $this->options['menu_slug'] . '_' . $this->current_tab, $this->current_section, $field );

				if ( empty( $this->current_tab ) || $this->current_tab == $this->active_tab )
					register_setting( $this->options['menu_slug'], $field['id'], array( $this, 'hd_sanitize_setting' ) );

				if ( ! empty( $field['default'] ) )
					add_option( $field['id'], $field['default'] );

			}

		}

	}

	/**
	 * Print Settings Page
	 *
	 * @return null
	 */
	function hd_settings_page() {

		?>
		<div class="wrap <?php echo sanitize_html_class( $this->options['menu_slug'] ); ?>">

			<h2><?php echo esc_html( $this->options['page_title'] ); ?></h2>
			<?php settings_errors(); ?>

			<form action="<?php echo admin_url( 'options.php' ) ?>" method="post">

				<?php settings_fields( $this->options['menu_slug'] ); ?>

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
								add_query_arg( array( 'page' => $this->options['menu_slug'], 'tab' => $tab_id ), admin_url( 'admin.php' ) ),
								( $this->active_tab == $tab_id ) ? ' nav-tab-active' : '',
								esc_html( $tab_name )
							);
						?>
					</h2>
					<table class="form-table">
						<?php do_settings_fields( $this->options['menu_slug'] . '_' . $this->active_tab, 'default' ); ?>
					</table>

					<?php do_settings_sections( $this->options['menu_slug'] . '_' . $this->active_tab ); ?>

					<input type="hidden" name="<?php echo esc_attr( $this->options['menu_slug'] . '_active_tab' ); ?>" value="<?php echo esc_attr( $this->active_tab ); ?>"/>

				<?php } ?>

				<?php submit_button(); ?>

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
	function hd_print_section( $args ) {

		echo $this->fields[ $args['id'] ]['desc'];

	}

	/*===[ Input Fields ]===*/

	/**
	 * Print Text Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	function hd_print_text_input( $field ) {

		printf(
			'<input type="text" name="%s" id="%s" value="%s" class="regular-text"/>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] ),
			esc_attr( get_option( $field['id'] ) )
		);

		$this->hd_print_help( $field );

	}

	/**
	 * Print Textarea Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	function hd_print_textarea_input( $field ) {

		printf(
			'<textarea name="%s" id="%s" rows="5" cols="40">%s</textarea>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] ),
			esc_textarea( get_option( $field['id'] ) )
		);

		$this->hd_print_help( $field );

	}

	/**
	 * Print Select Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	function hd_print_select_input( $field ) {

		$selected_value = get_option( $field['id'] );

		printf(
			'<select name="%s" id="%s">',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] )
		);

		if ( ! empty( $field['choices'] ) ) {
			foreach ( (array) $field['choices'] as $value => $label )
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $value ),
					selected( $selected_value, $value, false ),
					esc_html( $label )
				);
		}

		echo '</select>';

		$this->hd_print_help( $field );

	}

	/**
	 * Print Checkbox Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	function hd_print_checkbox_input( $field ) {

		printf(
			'<label><input type="checkbox" name="%s" id="%s"%s> %s</label>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] ),
			checked( get_option( $field['id'] ), 'on', false ),
			esc_html( $field['desc'] )
		);

	}

	/**
	 * Print Radio Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	function hd_print_radio_input( $field ) {

		$selected_value = get_option( $field['id'] );

		if ( ! empty( $field['choices'] ) ) {
			foreach ( (array) $field['choices'] as $value => $label )
				printf(
					'<label><input type="radio" name="%s" id="" value="%s"%s> %s</label><br/>',
					esc_attr( $field['id'] ),
					esc_attr( $value ),
					checked( $selected_value, $value, false ),
					esc_html( $label )
				);
		}

		$this->hd_print_help( $field );

	}

	/**
	 * Print Multi-Select Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	function hd_print_multiselect_input( $field ) {

		$selected_value = (array) get_option( $field['id'] );

		printf(
			'<select name="%s[]" id="%s" multiple>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] )
		);

		if ( ! empty( $field['choices'] ) ) {
			foreach ( (array) $field['choices'] as $value => $label )
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $value ),
					selected( in_array( $value, $selected_value ), true, false ),
					esc_html( $label )
				);
		}

		echo '</select>';

		$this->hd_print_help( $field );

	}

	/**
	 * Print Multi-Checkbox Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	function hd_print_multicheck_input( $field ) {

		$selected_value = (array) get_option( $field['id'] );

		if ( ! empty( $field['choices'] ) ) {
			foreach ( (array) $field['choices'] as $value => $label )
				printf(
					'<label><input type="checkbox" name="%s[]" id="" value="%s"%s> %s</label><br/>',
					esc_attr( $field['id'] ),
					esc_attr( $value ),
					checked( in_array( $value, $selected_value ), true, false ),
					esc_html( $label )
				);
		}

		$this->hd_print_help( $field );

	}

	/**
	 * Print Upload Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	function hd_print_upload_input( $field ) {

		// dang! dang!! dang!!!
		// We require to enqueue Media Uploader Scripts and Styles
		wp_enqueue_media();

		printf(
			'<input type="text" name="%s" id="%s" value="%s" class="regular-text hd-upload-input"/><input type="button" value="%s" class="hd-upload-button button button-secondary" id="hd_upload_%s"/>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] ),
			esc_attr( get_option( $field['id'] ) ),
			__( 'Upload' ),
			esc_attr( $field['id'] )
		);

		$this->hd_print_help( $field );

	}

	/**
	 * Print Color Picker Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	function hd_print_color_input( $field ) {

		$default_color = empty( $field['default'] ) ? '' : ' data-default-color="' . esc_attr( $field['default'] ) . '"';

		printf(
			'<input type="text" name="%s" id="%s" value="%s" class="hd-color-picker"%s/>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] ),
			esc_attr( get_option( $field['id'] ) ),
			$default_color
		);

		$this->hd_print_help( $field );

	}

	/**
	 * Print TinyMCE Editor Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	function hd_print_editor_input( $field ) {

		$settings = array(
			'media_buttons' => false,
			'textarea_rows' => 5,
			'textarea_cols' => 45,
		);

		$content = get_option( $field['id'] );
		$content = empty( $content ) ? '' : $content;

		wp_editor( $content, $field['id'], $settings );

		$this->hd_print_help( $field );

	}

	/**
	 * Print Help/Descripting for field
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	function hd_print_help( $field ) {

		if ( ! empty( $field['desc'] ) )
			echo '<p class="description">' . wp_kses_data( $field['desc'] ) . '</p>';

	}

	/**
	 * Sanitize settings
	 *
	 * @param  mixed $new_value Submitted new value
	 * @return mixed            Sanitized value
	 */
	function hd_sanitize_setting( $new_value ) {

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
				return $new_value;
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

} // HD_WP_Settings_Wrapper end