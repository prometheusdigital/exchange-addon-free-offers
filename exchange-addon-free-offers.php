<?php
/*
 * Plugin Name: ExchangeWP - Free Offers
 * Version: 1.2.2
 * Description: Allows you to turn any product into a free offer
 * Plugin URI: http://ithemes.com/purchase/free-offers-add-on/
 * Author: ExchangeWP
 * Author URI: http://ithemes.com
 * ExchangeWP Package: exchange-addon-free-offers

 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 * 5. Add license key to settings page.
 *
*/

/**
 * This registers our plugin as a membership addon
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_register_free_offers_addon() {
	$options = array(
		'name'              => __( 'Free Offers', 'LION' ),
		'description'       => __( 'Allows you to turn any product into a free offer.', 'LION' ),
		'author'            => 'ExchangeWP',
		'author_url'        => 'http://ithemes.com/purchase/free-offers-add-on/',
		'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/lib/images/free-offers-50px.png' ),
		'file'              => dirname( __FILE__ ) . '/init.php',
		'category'          => 'product-feature',
		'basename'          => plugin_basename( __FILE__ ),
		'settings-callback' => 'it_exchange_free_offers_addon_settings_callback',
		'labels'      => array(
			'singular_name' => __( 'Free Offer', 'LION' ),
		),
	);
	it_exchange_register_addon( 'free-offers', $options );
}
add_action( 'it_exchange_register_addons', 'it_exchange_register_free_offers_addon' );

/**
 * Loads the translation data for WordPress
 *
 * @uses load_plugin_textdomain()
 * @since 1.0.0
 * @return void
*/
function it_exchange_free_offers_set_textdomain() {
	load_plugin_textdomain( 'LION', false, dirname( plugin_basename( __FILE__  ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'it_exchange_free_offers_set_textdomain' );

/**
 * Registers Plugin with ExchangeWP updater class
 *
 * @since 1.0.0
 *
 * @param object $updater ithemes updater object
 * @return void
*/
function ithemes_exchange_addon_free_offers_updater_register( $updater ) {
	    $updater->register( 'exchange-addon-free-offers', __FILE__ );
}
add_action( 'ithemes_updater_register', 'ithemes_exchange_addon_free_offers_updater_register' );
// require( dirname( __FILE__ ) . '/lib/updater/load.php' );

function exchangewp_freeoffers_plugin_updater() {

	$license_check = get_transient( 'exchangewp_license_check' );

	if ($license_check->license == 'valid' ) {
		$license_key = it_exchange_get_option( 'exchangewp_licenses' );
		$license = $license_key['exchange_license'];

		$edd_updater = new EDD_SL_Plugin_Updater( 'https://exchangewp.com', __FILE__, array(
				'version' 		=> '1.2.2', 				// current version number
				'license' 		=> $license, 				// license key (used get_option above to retrieve from DB)
				'item_id' 		=> 364,				 	  	// name of this plugin
				'author' 	  	=> 'ExchangeWP',    // author of this plugin
				'url'       	=> home_url(),
				'wp_override' => true,
				'beta'		  	=> false
			)
		);
	}

}

add_action( 'admin_init', 'exchangewp_freeoffers_plugin_updater', 0 );
