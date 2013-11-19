<?php
/**
 * iThemes Exchange Free Offers Add-on
 * @package IT_Exchange_Addon_Free_Offers
 * @since 1.0.0
*/

/**
 * New Product Features added by the Exchange Membership Add-on.
*/
require( 'lib/product-features/load.php' );


/**
 * Remove proudct base price from templates depending on product settings
 *
 * @since 1.0.0
 *
 * @param  array $incoming the default tempalte parts
 * @return array modified tempatle part elements
*/
function it_exchange_free_offers_maybe_remove_base_price( $incoming ) {
	$product_id = empty( $GLOBALS['it_exchange']['product']->ID ) ? false : $GLOBALS['it_exchange']['product']->ID;

	if ( it_exchange_product_supports_feature( $product_id, 'base-price' ) && ( it_exchange_get_product_feature( $product_id, 'base-price' ) > 0 ) )
		return $incoming;

	// Return if we can't find a product ID
	if ( empty( $product_id ) )
		return $incoming;

	if ( $settings = it_exchange_get_product_feature( $product_id, 'free-offers' ) ) {
		if ( it_exchange_is_page( 'store' ) && ! empty( $settings['hide-price-in-store'] ) && ( FALSE !== ( $index = array_search( 'base-price', $incoming ) ) ) )
			unset( $incoming[$index] );

		if ( it_exchange_is_page( 'product' ) && ! empty( $settings['hide-price-on-product-page'] ) && ( FALSE !== ( $index = array_search( 'base-price', $incoming ) ) ) )
			unset( $incoming[$index] );
	}

	return $incoming;
}
add_filter( 'it_exchange_get_store_product_product_info_loop_elements', 'it_exchange_free_offers_maybe_remove_base_price', 10 );
add_filter( 'it_exchange_get_store_product_product_features_elements', 'it_exchange_free_offers_maybe_remove_base_price', 10 );
add_filter( 'it_exchange_get_content_product_product_info_loop_elements', 'it_exchange_free_offers_maybe_remove_base_price', 10 );

/**
 * Maybe change the Buy Now button label based on product-feature settings.
 *
 * @since 1.0.0
 *
 * @param  array $incoming_options the options passed and parsed via the theme API
 * @param  int   $product_id       the product id
 * @return array
*/
function it_exchange_maybe_alter_buy_now_button_label( $incoming_options, $product_id ) {

	if ( it_exchange_product_supports_feature( $product_id, 'base-price' ) && ( it_exchange_get_product_feature( $product_id, 'base-price' ) > 0 ) )
		return $incoming_options;

	if ( empty( $product_id ) || ! $settings = it_exchange_get_product_feature( $product_id, 'free-offers' ) )
		return $incoming_options;

	$incoming_options['label'] = empty( $settings['buy-now-label'] ) ? $incoming : $settings['buy-now-label'];
	return $incoming_options;
}
add_filter( 'it_exchange_product_theme_api_buy_now_options', 'it_exchange_maybe_alter_buy_now_button_label', 10, 2 );

/**
 * Maybe change the Complete Checkout button label based on product-feature settings.
 *
 * @since 1.0.0
 *
 * @param  string $incoming the incoming value from the WP filter
 * @return string
*/
function it_exchange_maybe_alter_zero_sum_checkout_button_label( $incoming ) {
	$cart_products = (array) it_exchange_get_cart_products();
	if ( count( $cart_products ) > 1 )
		return $incoming;

	foreach( $cart_products as $product => $details ) {
		if ( empty( $details['product_id'] ) || ! $settings = it_exchange_get_product_feature( $details['product_id'], 'free-offers' ) )
			continue;

		$incoming = empty( $settings['complete-purchase-label'] ) ? $incoming : $settings['complete-purchase-label'];
	}
	return $incoming;
}
add_filter( 'zero_sum_checkout_button_label', 'it_exchange_maybe_alter_zero_sum_checkout_button_label' );

/**
 * Enqueues styles for Free Offers pages
 *
 * @since 1.0.0
 * @param string $hook_suffix WordPress Hook Suffix
 * @param string $post_type WordPress Post Type
*/
function it_exchange_free_offers_addon_admin_wp_enqueue_styles( $hook_suffix, $post_type ) {
	if ( isset( $post_type ) && 'it_exchange_prod' === $post_type ) {
		wp_enqueue_style( 'it-exchange-free-offers-addon-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/lib/styles/add-edit-product.css' );
	}
}
add_action( 'it_exchange_admin_wp_enqueue_styles', 'it_exchange_free_offers_addon_admin_wp_enqueue_styles', 10, 2 );

/**
 * Shows the nag when needed.
 *
 * @since 1.0.2
 *
 * @return void
*/
function it_exchange_addon_free_offers_show_version_nag() {
    if ( $GLOBALS['it_exchange']['version'] < '1.6.2' ) { 
        ?>  
        <div id="it-exchange-add-on-min-version-nag" class="it-exchange-nag">
            <?php printf( __( 'The Free Offers add-on requires iThemes Exchange version 1.6.2 or greater. %sPlease upgrade Exchange%s.', 'LION' ), '<a href="' . admin_url( 'update-core.php' ) . '">', '</a>' ); ?>
        </div>
        <script type="text/javascript">
            jQuery( document ).ready( function() {
                if ( jQuery( '.wrap > h2' ).length == '1' ) {
                    jQuery("#it-exchange-add-on-min-version-nag").insertAfter('.wrap > h2').addClass( 'after-h2' );
                }
            });
        </script>
        <?php
    }   
}
add_action( 'admin_notices', 'it_exchange_addon_free_offers_show_version_nag' );
