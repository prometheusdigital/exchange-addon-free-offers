<?php
/**
 * This controls the button labels and the price output for free products
 * @since 1.0.0
 * @package IT_Exchange_Addon_Free_Offers
*/


class IT_Exchange_Addon_Free_Offers_Product_Feature {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function IT_Exchange_Addon_Free_Offers_Product_Feature() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_free-offers', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_free-offers', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_free-offers', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_free-offers', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.0.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'free-offers';
		$description = __( "Allows you to turn any product into a free offer.", 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) { 
			it_exchange_add_feature_support_to_product_type( 'free-offers', $params['slug'] );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function init_feature_metaboxes() {
		
		global $post;
		
		if ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];
		} else {
			if ( isset( $_REQUEST['post'] ) )
				$post_id = (int) $_REQUEST['post'];
			elseif ( isset( $_REQUEST['post_ID'] ) )
				$post_id = (int) $_REQUEST['post_ID'];
			else
				$post_id = 0;

			if ( $post_id )
				$post = get_post( $post_id );

			if ( isset( $post ) && ! empty( $post ) )
				$post_type = $post->post_type;
		}
			
		if ( ! empty( $_REQUEST['it-exchange-product-type'] ) )
			$product_type = $_REQUEST['it-exchange-product-type'];
		else
			$product_type = it_exchange_get_product_type( $post );
		
		if ( ! empty( $post_type ) && 'it_exchange_prod' === $post_type ) {
			if ( ! empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'free-offers' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
		}
		
	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature 
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-feature-free-offers', __( 'Free Offers', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_advanced' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the feature for this product
		$values = it_exchange_get_product_feature( $product->ID, 'free-offers' );

		$values['hide-price-in-store'] = ! empty( $values['hide-price-in-store'] );
		$values['hide-price-on-product-page'] = ! empty( $values['hide-price-on-product-page'] );
		$values['buy-now-label'] = empty( $values['buy-now-label'] ) ? __( 'Buy Now', 'LION' ) : $values['buy-now-label'];
		$values['complete-purchase-label'] = empty( $values['complete-purchase-label'] ) ? __( 'Complete Purchase', 'LION' ) : $values['complete-purchase-label'];
		
		$description = sprintf( __( "These settings will be applied if the product's base price is %s prior to discounts and taxes.", 'LION' ), it_exchange_format_price( '0' ) );
		$description = apply_filters( 'it_exchange_membership_addon_product_welcome-message_metabox_description', $description );

		if ( $description ) {
			echo '<p class="intro-description">' . $description . '</p>';
		}
	
		?>
		<div class="hide-price-label-settings">
			<label><input type="checkbox" name="it-exchange-product-feature-free-offers[hide-price-in-store]" value="true" <?php checked( true, $values['hide-price-in-store'] ); ?>/>&nbsp;
			<?php _e( 'Hide product price in Exchange store?', 'LION' ); ?></label>

			<label><input type="checkbox" name="it-exchange-product-feature-free-offers[hide-price-on-product-page]" value="true" <?php checked( true, $values['hide-price-on-product-page'] ); ?>/>&nbsp;
			<?php _e( 'Hide product price on Exchange product page?', 'LION' ); ?></label>
		</div>

		<div class="button-labels">
			<div class="buy-now-label">
				<label><?php _e( 'Buy Now Button Text', 'LION' ); ?></label><input type="text" value="<?php esc_attr_e( $values['buy-now-label'] ); ?>" name="it-exchange-product-feature-free-offers[buy-now-label]" />
			</div>
			<div class="complete-purchase-label">
				<label><?php _e( 'Purchase Button Text', 'LION' ); ?></label><input type="text" value="<?php esc_attr_e( $values['complete-purchase-label'] ); ?>" name="it-exchange-product-feature-free-offers[complete-purchase-label]" />
			</div>
		</div>
		<?php
	}

	/**
	 * This saves the value
	 *
	 * @since 1.0.0
	 *
	 * @param object $post wp post object
	 * @return void
	*/
	function save_feature_on_product_save() {
		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() )
			return;

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id )
			return;

		// Abort if this product type doesn't support this feature 
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'free-offers' ) || empty( $_POST['it-exchange-product-feature-free-offers']  ))
			return;

		// If the value is empty (0), delete the key, otherwise save
		if ( empty( $_POST['it-exchange-product-feature-free-offers'] ) )
			delete_post_meta( $product_id, '_it-exchange-product-feature-free-offers' );
		else
			it_exchange_update_product_feature( $product_id, 'free-offers', $_POST['it-exchange-product-feature-free-offers'] );
	}

	/**
	 * This updates the feature for a product
	 *
	 * @since 1.0.0
	 *
	 * @param integer $product_id the product id
	 * @param mixed $new_value the new value 
	 * @return bolean
	*/
	function save_feature( $product_id, $new_value ) {
		update_post_meta( $product_id, '_it-exchange-product-feature-free-offers', $new_value );
		return true;
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.0.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return array product feature
	*/
	function get_feature( $existing, $product_id ) {
		// Is the the add / edit product page?
		$current_screen = is_admin() ? get_current_screen(): false;
		$editing_product = ( ! empty( $current_screen->id ) && 'it_exchange_prod' == $current_screen->id );

		// Return the value if supported or on add/edit screen
		if ( it_exchange_product_supports_feature( $product_id, 'free-offers' ) || $editing_product )
			return get_post_meta( $product_id, '_it-exchange-product-feature-free-offers', true );

		return false;
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.0.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id ) )
			return false;
		return (boolean) $this->get_feature( false, $product_id );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can 
	 * support a feature but might not have the feature set.
	 *
	 * @since 1.0.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'free-offers' ) )
			return false;

		return true;
	}
}
$IT_Exchange_Addon_Free_Offers_Product_Feature = new IT_Exchange_Addon_Free_Offers_Product_Feature();
