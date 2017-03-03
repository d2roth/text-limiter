<?php
/**
 * Plugin Name: Meta Box Text Limiter
 * Plugin URI: https://metabox.io
 * Description: Limit number of characters or words entered for text and textarea fields
 * Author: ThaoHa, Anh Tran
 * Version: 1.0.2
 * Author URI: https://metabox.io
 *
 * @package Meta Box
 * @subpackage Meta Box Text Limiter
 */

/**
 * Text limiter class.
 */
class Text_Limiter {
	/**
	 * List of supported fields.
	 *
	 * @var array
	 */
	protected $types = array( 'text', 'textarea' );

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rwmb_before', array( $this, 'register' ) );

		// Change the output of fields with limit.
		// Pre Meta Box 4.8.2.
		add_filter( 'rwmb_get_field', array( $this, 'get_value' ), 10, 2 );
		add_filter( 'rwmb_the_field', array( $this, 'get_value' ), 10, 2 );
		// Meta Box 4.8.2+.
		add_filter( 'rwmb_get_value', array( $this, 'get_value' ), 10, 2 );
		add_filter( 'rwmb_the_value', array( $this, 'get_value' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Register hook to change the output of text/textarea fields.
	 */
	public function register() {
		foreach ( $this->types as $type ) {
			add_filter( "rwmb_{$type}_html", array( $this, 'show' ), 10, 2 );
		}
	}

	/**
	 * Change the output of text/textarea fields.
	 *
	 * @param string $output HTML output of the field.
	 * @param array  $field  Field parameter.
	 *
	 * @return string
	 */
	public function show( $output, $field ) {
		if ( ! isset( $field['limit'] ) || ! is_numeric( $field['limit'] ) || ! $field['limit'] > 0 ) {
			return $output;
		}

		$type = isset( $field['limit_type'] ) ? $field['limit_type'] : 'character';
		$text = 'word' === $type ? __( 'Word Count', 'text-limiter' ) : __( 'Character Count', 'text-limiter' );

		return $output . '
			<div class="text-limiter" data-limit-type="' . esc_attr( $type ) . '">
				<span>' . esc_html( $text ) . ':
					<span class="counter">0</span>/<span class="maximum">' . esc_html( $field['limit'] ) . '</span>
				</span>
			</div>';
	}

	/**
	 * Filters the value of a field
	 *
	 * @see rwmb_get_field() in meta-box/inc/functions.php for explenation
	 *
	 * @param string $value   Field value.
	 * @param array  $field   Field parameters.
	 *
	 * @return string
	 */
	public function get_value( $value, $field ) {
		if ( ! in_array( $field['type'], $this->types, true ) || empty( $field['limit'] ) || ! is_numeric( $field['limit'] ) ) {
			return $value;
		}

		$type = isset( $field['limit_type'] ) ? $field['limit_type'] : 'character';
		if ( 'word' === $type ) {
			$value_array = preg_split( '/\s+/', $value, - 1, PREG_SPLIT_NO_EMPTY );
			$delimiter   = ' ';
		} else {
			$value_array = str_split( $value );
			$delimiter   = '';
		}

		$value = implode( $delimiter, array_slice( $value_array, 0, $field['limit'] ) );

		return $value;
	}

	/**
	 * Enqueue assets.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'text-limiter', plugin_dir_url( __FILE__ ) . 'css/text-limiter.css' );
		wp_enqueue_script( 'text-limiter', plugin_dir_url( __FILE__ ) . 'js/text-limiter.js', array( 'jquery' ), '', true );
	}
}

new Text_Limiter;
