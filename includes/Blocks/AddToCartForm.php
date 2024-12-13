<?php
/**
 * Modify the AddToCartForm Block.
 *
 * @package WC_Same_Page_Checkout\Blocks
 */
namespace Backcourt\SamePageCheckout\Blocks\AddToCartForm;

defined( 'ABSPATH' ) || exit;

/**
 * Hooks
 */
add_filter( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_block_assets' );
add_filter( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
add_filter( 'block_type_metadata', __NAMESPACE__ . '\add_custom_metadata' );
add_filter( 'render_block_woocommerce/add-to-cart-form', __NAMESPACE__ . '\add_interactivity', 10, 3 );

/**
 * Register the block assets.
 */
function enqueue_block_assets() {

	$script_asset_path = dirname( \Backcourt\SamePageCheckout\PLUGINFILE ) . '/dist/add-to-cart-form/view.asset.php';
	$script_asset      = require $script_asset_path;

	$asset_path = plugin_dir_path( \Backcourt\SamePageCheckout\PLUGINFILE ) . '/dist/add-to-cart-form/index.asset.php';

    if ( file_exists( $asset_path ) ) {
		$asset_file = include $asset_path;
		$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? $asset_file['version'] : \Backcourt\SamePageCheckout\VERSION;

		wp_register_script_module(
			'wc-same-page-checkout',
			plugin_dir_url( \Backcourt\SamePageCheckout\PLUGINFILE ) . 'dist/add-to-cart-form/view.js',
			$script_asset['dependencies'],
			$script_asset['version']
		);

		// Load JS translations.
		wp_set_script_translations(
			'wc-same-page-checkout',
			'wc-same-page-checkout',
			__DIR__ . '/languages'
		);

		if ( has_block( 'woocommerce/single-product' ) && has_block( 'woocommerce/checkout' ) ) {
	
			// Enqueue the custom stylesheet for the pattern
			wp_enqueue_style(
				'backcourt-same-page-checkout--add-to-cart-form',
				plugin_dir_url( \Backcourt\SamePageCheckout\PLUGINFILE ) . 'dist/add-to-cart-form/view.css',
				[],
				$version
			);
		}

	}

}


/**
 * Register the block editor assets.
 */
function enqueue_block_editor_assets() {
	$asset_path = plugin_dir_path( \Backcourt\SamePageCheckout\PLUGINFILE ) . '/dist/add-to-cart-form/index.asset.php';

    if ( file_exists( $asset_path ) ) {
		$asset_file = include $asset_path;
		$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? $asset_file['version'] : \Backcourt\SamePageCheckout\VERSION;
		wp_enqueue_script( 'backcourt-same-page-checkout--add-to-cart-form-edit', plugin_dir_url( \Backcourt\SamePageCheckout\PLUGINFILE ) . 'dist/add-to-cart-form/index.js', $asset_file['dependencies'], $version, true );

		wp_enqueue_style(
			'backcourt-same-page-checkout--add-to-cart-form-edit',
			plugin_dir_url( \Backcourt\SamePageCheckout\PLUGINFILE ) . 'dist/add-to-cart-form/index.css',
			[],
			$version
		);
	}

	// Enqueue editor styles for the custom toggle class.
    

}

/**
 * Provide 'isDescendentOfSamePageCheckout' context from the add to cart and checkout blocks.
 *
 * @param array $metadata Metadata for registering a block type.
 * @return array
 */
function add_custom_metadata( $metadata ) {
	if ( 'woocommerce/add-to-cart-form' === $metadata['name'] ) {
		$metadata['attributes']['isDescendentOfSamePageCheckout'] = [
			'type'    => 'boolean',
			'default' => false,
		];
		$metadata['supports']['interactivity'] = true;
	}
	return $metadata;
}

/**
 * Add Interactivity to the AddToCartForm Block.
 *
 * @param string $block_content The block content about to be rendered.
 * @param array  $block The block being rendered.
 * @param \WP_Block $instance      The block instance.
 *
 * @return string
 */
function add_interactivity( $block_content, $block, $instance ) {

	$isDescendentOfSamePageCheckout = $block['attrs']['isDescendentOfSamePageCheckout'] ?? false;

	if ( is_admin() || ! $isDescendentOfSamePageCheckout ) {
		return $block_content;
	}

	wp_enqueue_script( 'wp-data' );
	wp_enqueue_script_module( 'wc-same-page-checkout' );

	add_filter( 'woocommerce_product_single_add_to_cart_text', __NAMESPACE__ . '\add_to_cart_text', 10, 2 );

	$productId = $instance->context['postId'] ?? 0;

	$context = array(
		'productId' => $productId,
		'quantityToAdd' => 1,
	);

	$p = new \WP_HTML_Tag_Processor( $block_content );

	if ( $p->next_tag( array( 'class_name' => 'cart' ) ) ) {
		$p->set_attribute( 'data-wp-interactive', 'backcourt/same-page-checkout' );
		$p->set_attribute( 'data-wp-on--submit', 'backcourt/same-page-checkout::actions.addToCart' );
		$p->set_attribute( 'data-wp-context', wp_json_encode( $context ) );
	}

	if ( $p->next_tag( array( 'class_name' => 'qty' ) ) ) { // How do we limit this to only the main input?
		$p->set_attribute( 'data-wp-interactive', 'backcourt/same-page-checkout' );
		$p->set_attribute( 'data-wp-on--input', 'backcourt/same-page-checkout::actions.updateQuantity' );
		$p->set_attribute( 'data-wp-init', 'backcourt/same-page-checkout::callbacks.setQuantity' );
		$p->set_attribute( 'wp-bind--value', 'backcourt/same-page-checkout::context.quantityToAdd' );
	}

	//if ( $p->next_tag( array( 'class_name' => 'single_add_to_cart_button' ) ) ) {
	//	$p->set_attribute( 'data-wp-interactive', 'backcourt/same-page-checkout' );
	//	$p->set_attribute( 'data-wp-on--click', 'backcourt/same-page-checkout::actions.addToCart' );
	//	$p->set_attribute( 'data-wp-context', wp_json_encode( $context ) );
	//}

	return $p->get_updated_html();

}

/**
 * Change Add to Cart button text
 *
 * @param string $block_content The block content about to be rendered.
 * @param array  $block The block being rendered.
 * @param \WP_Block $instance      The block instance.
 *
 * @return string
 */
function add_to_cart_text( $text, $product ) {
	wp_die('here');
	return __( 'Buy now', 'backcourt-same-page-checkout' );
}