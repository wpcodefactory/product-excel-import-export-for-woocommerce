<?php

namespace codecabin;

defined( 'ABSPATH' ) || exit;

if(!is_admin())
	return;

global $pagenow;

if($pagenow != "plugins.php")
	return;

if(defined('CODECABIN_DEACTIVATE_FEEDBACK_FORM_INCLUDED'))
	return;
define('CODECABIN_DEACTIVATE_FEEDBACK_FORM_INCLUDED', true);

add_action('admin_enqueue_scripts', function() {

	/**
	 * Enqueue scripts and styles.
	 *
	 * @version 7.0.6
	 */
	$version = defined( 'WPFACTORY_WC_PEIE_VERSION' ) ? WPFACTORY_WC_PEIE_VERSION : '7.0.6';

	// Enqueue scripts
	wp_enqueue_script('remodal', plugin_dir_url(__FILE__) . 'remodal.min.js', array(), $version, true);
	wp_enqueue_style('remodal', plugin_dir_url(__FILE__) . 'remodal.css', array(), $version);
	wp_enqueue_style('remodal-default-theme', plugin_dir_url(__FILE__) . 'remodal-default-theme.css', array(), $version);

	wp_enqueue_script('codecabin-deactivate-feedback-form', plugin_dir_url(__FILE__) . 'deactivate-feedback-form.js', array('jquery'), $version, true);
	wp_enqueue_style('codecabin-deactivate-feedback-form', plugin_dir_url(__FILE__) . 'deactivate-feedback-form.css', array(), $version);
	
	// Localized strings
	wp_localize_script('codecabin-deactivate-feedback-form', 'codecabin_deactivate_feedback_form_strings', array(
		'ajax_url'					=> admin_url( 'admin-ajax.php' ),
		'nonce'						=> wp_create_nonce( 'woopei_deactivate_feedback' ),
		'quick_feedback'			=> __('We are sorry to see you go...', 'woo-product-excel-importer'),
		'foreword'					=> __('If you would be kind enough, please tell us why you\'re deactivating?', 'woo-product-excel-importer'),
		'better_plugins_name'		=> __('Please tell us which plugin?', 'woo-product-excel-importer'),
		'please_tell_us'			=> __('Please tell us the reason so we can improve the plugin', 'woo-product-excel-importer'),
		'do_not_attach_email'		=> __('Do not send my e-mail address with this feedback', 'woo-product-excel-importer'),
		
		'brief_description'			=> __('Please give us any feedback that could help us improve', 'woo-product-excel-importer'),
		
		'cancel'					=> __('Cancel', 'woo-product-excel-importer'),
		'skip_and_deactivate'		=> __('Skip &amp; Deactivate', 'woo-product-excel-importer'),
		'submit_and_deactivate'		=> __('Submit &amp; Deactivate', 'woo-product-excel-importer'),
		'please_wait'				=> __('Please wait', 'woo-product-excel-importer'),
		'thank_you'					=> __('Thank you!', 'woo-product-excel-importer')
	));
	
	// Plugins
	$plugins = apply_filters('codecabin_deactivate_feedback_form_plugins', array());
	
	// Reasons
	$defaultReasons = array(
		'suddenly-stopped-working'	=> __('The plugin suddenly stopped working', 'woo-product-excel-importer'),
		'plugin-broke-site'			=> __('The plugin broke my site', 'woo-product-excel-importer'),
		'no-longer-needed'			=> __('I don\'t need this plugin any more', 'woo-product-excel-importer'),
		'found-better-plugin'		=> __('I found a better plugin', 'woo-product-excel-importer'),
		'temporary-deactivation'	=> __('It\'s a temporary deactivation, I\'m troubleshooting', 'woo-product-excel-importer'),
		'other'						=> __('Other', 'woo-product-excel-importer')
	);
	
	foreach($plugins as $plugin)
	{
		$plugin->reasons = apply_filters('codecabin_deactivate_feedback_form_reasons', $defaultReasons, $plugin);
	}
	
	// Send plugin data
	wp_localize_script('codecabin-deactivate-feedback-form', 'codecabin_deactivate_feedback_form_plugins', $plugins);
});

/**
 * Hook for adding plugins, pass an array of objects in the following format:
 *  'slug'		=> 'plugin-slug'
 *  'version'	=> 'plugin-version'
 * @return array The plugins in the format described above
 */
add_filter('codecabin_deactivate_feedback_form_plugins', function($plugins) {
	return $plugins;
});

/**
 * Forwards the deactivation feedback form to extend-wp.com.
 *
 * @since  7.0.6
 * @version 7.0.6
 */
add_action( 'wp_ajax_woopei_deactivate_feedback', function () {

	check_ajax_referer( 'woopei_deactivate_feedback', 'nonce' );

	if ( ! current_user_can( 'deactivate_plugins' ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied.' ), 403 );
	}

	$body = array();

	foreach ( array( 'plugin', 'slug', 'version', 'reason', 'comments' ) as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$body[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
		}
	}

	$response = wp_remote_post(
		'https://extend-wp.com/wp-json/feedback/v2/post',
		array(
			'timeout' => 10,
			'body'    => $body,
		)
	);

	wp_send_json( array( 'ok' => ! is_wp_error( $response ) ) );
} );

