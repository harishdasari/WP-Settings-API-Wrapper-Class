<?php
/* A HTML Helper Class */

if ( ! class_exists( 'HD_HTML_Helper' ) ) :
/**
 * HTML Helper Class
 *
 * a Simple HTML Helper Class to generate form field.
 *
 * @version 1.0
 * @author  Harish Dasari
 * @link    http://github.com/harishdasari
 */
class HD_HTML_Helper {

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * Returns the Form Table html
	 *
	 * @param  array   $fields    Input fields options
	 * @param  boolean $show_help Show or hide help string
	 * @return string             HTML string
	 */
	public function get_form_table( $fields, $show_help = true ) {

		$form_table = '';

		$form_table .= '<table class="form-table">';

		foreach ( (array) $fields as $field )
			$form_table .= $this->get_table_row( $field, $show_help );

		$form_table .= '</table>';

		return apply_filters( 'hd_html_helper_form_table', $form_table, $fields, $show_help );

	}

	/**
	 * Echo/Display the HTML Form table
	 *
	 * @param  array   $fields    Input fields options
	 * @param  boolean $show_help Show or hide help string
	 * @return null
	 */
	public function display_form_table( $fields, $show_help = true ) {

		echo $this->get_form_table( $fields, $show_help );

	}

	/**
	 * Returns the table row html
	 *
	 * @param  array   $field
	 * @param  boolean $show_help
	 * @return string
	 */
	public function get_table_row( $field, $show_help ) {

		$table_row = '<tr valign="top">';
			$table_row .= sprintf( '<th><label for="%s">%s</label></th>', esc_attr( $field['id'] ), $field['title'] );
			$table_row .= sprintf( '<td>%s</td>', $this->get_field( $field, $show_help ) );
		$table_row .= '</tr>';

		return apply_filters( 'hd_html_helper_table_row', $table_row, $field, $show_help );

	}

	/**
	 * returns a input field based on field options
	 *
	 * @param  array   $field     Input field options
	 * @param  boolean $show_help Show or hide help string
	 * @return string             HTML string
	 */
	public function get_field( $field, $show_help = true ) {

		$field_default = array(
			'title'    => '',
			'id'       => '',
			'type'     => '',
			'default'  => '',
			'choices'  => array(),
			'value'    => '',
			'desc'     => '',
			'sanit'    => '',
			'multiple' => false, // for multiselect fiield
		);

		$field = wp_parse_args( $field, $field_default );

		$input_html = '';

		switch ( $field['type'] ) {
			case 'text'       : $input_html .= $this->text_input( $field ); break;
			case 'textarea'   : $input_html .= $this->textarea_input( $field ); break;
			case 'select'     : $input_html .= $this->select_input( $field ); break;
			case 'radio'      : $input_html .= $this->radio_input( $field ); break;
			case 'checkbox'   : $input_html .= $this->checkbox_input( $field ); break;
			case 'multicheck' : $input_html .= $this->multicheck_input( $field ); break;
			case 'upload'     : $input_html .= $this->upload_input( $field ); break;
			case 'color'      : $input_html .= $this->color_input( $field ); break;
			case 'editor'     : $input_html .= $this->editor_input( $field ); break;
		}

		if ( $show_help && 'checkbox' !== $field['type'] )
			$input_html .= $this->help_text( $field );

		return apply_filters( 'hd_html_helper_input_field', $input_html, $field, $show_help );

	}

	/**
	 * Displays a Input field based field options
	 *
	 * @param  array   $field     Input field options
	 * @param  boolean $show_help Show or hide help string
	 * @return null
	 */
	public function display_field( $field, $show_help = true ) {

		echo $this->get_field( $field, $show_help );

	}

	/**
	 * Print Text Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	private function text_input( $field ) {

		return sprintf(
			'<input type="text" name="%s" id="%s" value="%s" class="regular-text"/>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] ),
			esc_attr( $field['value'] )
		);

	}

	/**
	 * Print Textarea Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	private function textarea_input( $field ) {

		return sprintf(
			'<textarea name="%s" id="%s" rows="5" cols="40">%s</textarea>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] ),
			esc_textarea( $field['value'] )
		);

	}

	/**
	 * Print Select Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	private function select_input( $field ) {

		$selected_value = $field['value'];

		$multiple = ( true == $field['multiple'] || 'true' == $field['multiple'] ) ? true : false ;

		if ( $multiple )
			$field['id'] = $field['id'] . '[]';

		$select_field = sprintf(
			'<select name="%s" id="%s"%s>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] ),
			( $multiple ? ' multiple' : '' )
		);

		if ( ! empty( $field['choices'] ) ) {
			foreach ( (array) $field['choices'] as $value => $label ) {
				$selected = $multiple ? selected( in_array( $value, (array) $selected_value ), true, false ) : selected( $selected_value, $value, false );
				$select_field .= sprintf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $value ),
					$selected,
					esc_html( $label )
				);
			}
		}

		$select_field .= '</select>';

		return $select_field;

	}

	/**
	 * Print Checkbox Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	private function checkbox_input( $field ) {

		return sprintf(
			'<label><input type="checkbox" name="%s" id="%s"%s> %s</label>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] ),
			checked( $field['value'], 'on', false ),
			esc_html( $field['desc'] )
		);

	}

	/**
	 * Print Radio Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	private function radio_input( $field ) {

		$selected_value = $field['value'];

		$radio_field = '';

		if ( ! empty( $field['choices'] ) ) {
			foreach ( (array) $field['choices'] as $value => $label )
				$radio_field .= sprintf(
					'<label><input type="radio" name="%s" id="" value="%s"%s> %s</label><br/>',
					esc_attr( $field['id'] ),
					esc_attr( $value ),
					checked( $selected_value, $value, false ),
					esc_html( $label )
				);
		}

		return $radio_field;

	}

	/**
	 * Print Multi-Checkbox Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	private function multicheck_input( $field ) {

		$selected_value = (array) $field['value'];

		$multicheck_field = '';

		if ( ! empty( $field['choices'] ) ) {
			foreach ( (array) $field['choices'] as $value => $label )
				$multicheck_field .= sprintf(
					'<label><input type="checkbox" name="%s[]" id="" value="%s"%s> %s</label><br/>',
					esc_attr( $field['id'] ),
					esc_attr( $value ),
					checked( in_array( $value, $selected_value ), true, false ),
					esc_html( $label )
				);
		}

		return $multicheck_field;

	}

	/**
	 * Print Upload Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	private function upload_input( $field ) {

		// dang! dang!! dang!!!
		// We require to enqueue Media Uploader Scripts and Styles
		wp_enqueue_media();

		return sprintf(
			'<input type="text" name="%s" id="%s" value="%s" class="regular-text hd-upload-input"/>' .
			'<input type="button" value="%s" class="hd-upload-button button button-secondary" id="hd_upload_%s"/>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] ),
			esc_attr( $field['value'] ),
			__( 'Upload' ),
			esc_attr( $field['id'] )
		);

	}

	/**
	 * Print Color Picker Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	private function color_input( $field ) {

		$default_color = empty( $field['default'] ) ? '' : ' data-default-color="' . esc_attr( $field['default'] ) . '"';

		return sprintf(
			'<input type="text" name="%s" id="%s" value="%s" class="hd-color-picker"%s/>',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] ),
			esc_attr( $field['value'] ),
			$default_color
		);

	}

	/**
	 * Print TinyMCE Editor Input
	 *
	 * @param  array $field Input Options
	 * @return null
	 */
	private function editor_input( $field ) {

		$settings = array(
			'media_buttons' => false,
			'textarea_rows' => 5,
			'textarea_cols' => 45,
		);

		$content = $field['value'];
		$content = empty( $content ) ? '' : $content;

		ob_start();
		wp_editor( $content, $field['id'], $settings );
		return ob_get_clean();

	}

	/**
	 * Print Help/Descripting for field
	 *
	 * @param  array $field Input Options
	 * @return (string|null)
	 */
	private function help_text( $field ) {

		if ( empty( $field['desc'] ) )
			return '';

		return '<p class="description">' . wp_kses_data( $field['desc'] ) . '</p>';

	}

} // End HD_HTML_Helper

endif; // end class_exists check