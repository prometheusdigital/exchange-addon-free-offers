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
	if ( empty( $product_id ) || ! $settings = it_exchange_get_product_feature( $product_id, 'free-offers' ) )
		continue;

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
	foreach( (array) it_exchange_get_cart_products() as $product => $details ) {
		if ( empty( $details['product_id'] ) || ! $settings = it_exchange_get_product_feature( $details['product_id'], 'free-offers' ) )
			continue;

		$incoming = empty( $settings['complete-purchase-label'] ) ? $incoming : $settings['complete-purchase-label'];
	}
	return $incoming;
}
add_filter( 'zero_sum_checkout_button_label', 'it_exchange_maybe_alter_zero_sum_checkout_button_label' );
