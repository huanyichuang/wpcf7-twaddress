<?php
/**
 * Plugin Name:       Contact Form 7 Taiwan Address Selector Extension
 * Plugin URI:        https://huanyichuang.com/
 * Description:       To make a new tag for Contact Form 7 to add Taiwan address.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Huanyi Chuang
 * Author URI:        https://huanyichuang.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpcf7-twaddress
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Check if Contact Form 7 is installed.
if ( ! defined( 'WPCF7_VERSION' ) ) {
	return;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPCF7TWADDRESS_VERSION', '1.0.0' );
define( 'WPCF7TWADDRESS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Load plugin textdomain.
 */
add_action( 'plugins_loaded', 'wpcf7_twaddress_load_textdomain' );
function wpcf7_twaddress_load_textdomain() {
  load_plugin_textdomain( 'wpcf7-twaddress', false, basename( dirname( __FILE__ ) ) . '/languages/' ); 
}

add_action( 'wpcf7_init', 'wpcf7_add_address_tag' );
function wpcf7_add_address_tag() {
	wpcf7_add_form_tag( 
		array( 'address', 'address*' ),
		'wpcf7_add_address_tag_handler',
		array( 'name-attr' => true )
	);
}

function wpcf7_add_address_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
    }

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	$class .= ' wpcf7-validates-as-text';
	
	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
    }
	
	$atts = array();
	/* Use another arry to store ZIP tag's attributes. */
	$zip_atts = array();
	
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	if ( $tag->has_option( 'readonly' ) ) {
		$atts['readonly'] = 'readonly';
	}

	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
    }
	
	$value = (string) reset( $tag->values );
	
	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';
	
	if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	}

	if ( $tag->has_option( 'full-addr-hidden' ) ) {
		$atts['hidden'] = 'hidden';
	}

	if ( $tag->has_option( 'zip-hidden' ) ) {
		$zip_atts['data-hidden-zip-code'] = 'data-hidden-zip-code';
	}

	$value = $tag -> get_default_option( $value );
	
	$value = wpcf7_get_hangover( $tag->name, $value );
	
	$atts['value'] = $value;
    $atts['type']  = 'text';

	$atts['name'] = $tag->name;

	$atts = wpcf7_format_atts( $atts );
	$zip_atts = wpcf7_format_atts( $zip_atts );
	
	/* 開始拼裝 HTML 元素 */
	$html = '<div role="tw-city-selector" data-has-zipcode ' . $zip_atts . '></div>'; //基本的標籤
	$html .= '<span class="wpcf7-form-control-wrap %1$s"><input id="street-' . sanitize_html_class( $tag->name ) . '" type="text" class="street" placeholder="OO 路 O 段 O 號 O 樓" />'; //讓使用者填寫詳細地址
	$html .= sprintf( '<input %2$s />%3$s</span>',
					sanitize_html_class( $tag->name ),
					$atts,
					$validation_error
					);
	return $html;
	
}

add_action( 'wpcf7_enqueue_scripts', 'wpcf7_address_script' );
function wpcf7_address_script() {
	wp_enqueue_script( 'address-picker', plugin_dir_url( __FILE__ ) . 'assets/js/tw-city-selector.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'tw-selector', plugin_dir_url( __FILE__ ) . 'assets/js/tcs-init.js', array( 'address-picker' ), '', true );
}

add_filter( 'wpcf7_validate_address', 'wpcf7_address_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_address*', 'wpcf7_address_validation_filter', 10, 2 );

function wpcf7_address_validation_filter( $result, $tag ) {
	$name = $tag->name;

	$value = isset( $_POST[$name] )
		? trim( wp_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) )
		: '';
	
	if ( $tag->is_required() and '' === $value ) {
		$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
	}
	
	return $result;
}

/**
 * Add Tag generators
 */

add_action( 'wpcf7_admin_init', 'wpcf7_add_tag_generator_address', 25, 0 );

function wpcf7_add_tag_generator_address() {
	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator -> add( 'address', esc_html__( 'address', 'wpcf7-twaddress' ),
		'wpcf7_tag_generator_address' );
}

function wpcf7_tag_generator_address( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );

	$description = __( "Generate a form-tag for an address with Taiwan zipcode. For more details, see %s.", 'wpcf7-twaddress' );

	$desc_link = wpcf7_link( __( 'https://github.com/huanyichuang/wpcf7-twaddress', 'wpcf7-twaddress' ), __( 'my GitHub repository', 'wpcf7-twaddress' ) );
?>
<div class="control-box">
	<fieldset>
		<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

	<table class="form-table">
	<tbody>
		<tr>
		<th scope="row"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></th>
		<td>
			<fieldset>
			<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></legend>
			<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'contact-form-7' ) ); ?></label>
			</fieldset>
		</td>
		</tr>

		<tr>
		<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
		</tr>

		<tr>
			<th scope="row"><?php echo esc_html( __( 'Options', 'contact-form-7' ) ); ?></th>
			<td>
				<fieldset>
				<legend class="screen-reader-text"><?php echo esc_html( __( 'Options', 'contact-form-7' ) ); ?></legend>
				<label><input type="checkbox" name="zip-hidden" class="option" /> <?php echo esc_html( __( 'Hide zipcodes', 'wpcf7-twaddress' ) ); ?></label><br />
				<label><input type="checkbox" name="full-addr-hidden" class="option" checked /> <?php echo esc_html( __( 'Hide full address', 'wpcf7-twaddress' ) ); ?></label><br />
				</fieldset>
			</td>
			</tr>

		<tr>
		<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
		</tr>

		<tr>
		<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
		</tr>

	</tbody>
	</table>
	</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="address" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
	</div>

	<br class="clear" />

	<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
</div>
<?php
} 


