<?php
/**
 * Modify the Checkout Block.
 *
 * @package WC_Same_Page_Checkout\Blocks
 */
namespace Backcourt\SamePageCheckout\Blocks\Checkout;

defined( 'ABSPATH' ) || exit;

/**
 * Hooks
 */
add_filter( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_block_assets' );
add_filter( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
add_filter( 'block_type_metadata', __NAMESPACE__ . '\add_custom_metadata' );
add_filter( 'render_block_woocommerce/checkout', __NAMESPACE__ . '\modify_output', 10, 3 );

/**
 * Register the block assets.
 */
function enqueue_block_assets() {

	if ( has_block( 'woocommerce/single-product' ) && has_block( 'woocommerce/checkout' ) ) {

		// Enqueue the custom stylesheet for the pattern
		wp_enqueue_style( 'backcourt-same-page-checkout', plugin_dir_url( \Backcourt\SamePageCheckout\PLUGINFILE ) . 'dist/checkout/view.css' );
	}

}

/**
 * Register the block editor assets.
 */
function enqueue_block_editor_assets() {
	$asset_path = plugin_dir_path( \Backcourt\SamePageCheckout\PLUGINFILE ) . '/dist/checkout/index.asset.php';

	if ( file_exists( $asset_path ) ) {
		$asset_file = include $asset_path;
		$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? $asset_file['version'] : \Backcourt\SamePageCheckout\VERSION;
		wp_enqueue_script( 'backcourt-same-page-checkout--checkout', plugin_dir_url( \Backcourt\SamePageCheckout\PLUGINFILE ) . 'dist/checkout/index.js', $asset_file['dependencies'], $version, true );
	}

}

/**
 * Provide 'isDescendentOfSamePageCheckout' context from the add to cart and checkout blocks.
 *
 * @param array $metadata Metadata for registering a block type.
 * @return array
 */
function add_custom_metadata( $metadata ) {
	if ( 'woocommerce/checkout' === $metadata['name'] ) {
		$metadata['attributes']['isDescendentOfSamePageCheckout'] = [
			'type'    => 'boolean',
			'default' => false,
		];
		$metadata['supports']['interactivity'] = true;
	}
	return $metadata;
}

/**
 * Handle the rendering of the block.
 *
 * @param string $block_content The block content about to be rendered.
 * @param array  $block The block being rendered.
 * @param \WP_Block $instance      The block instance.
 *
 * @return string
 */
function modify_output( $block_content, $block, $instance ) {

	$isDescendentOfSamePageCheckout = $block['attrs']['isDescendentOfSamePageCheckout'] ?? false;

	if ( is_admin() || ! $isDescendentOfSamePageCheckout ) {
		return $block_content;
	}

	$p = new \WP_HTML_Tag_Processor( $block_content );

	if ( $p->next_tag( array( 'class_name' => 'wp-block-woocommerce-checkout' ) ) ) {
		$p->set_attribute( 'data-wp-interactive', 'backcourt/same-page-checkout' );
		$p->set_attribute( 'data-wp-class--has-empty-cart', 'backcourt/same-page-checkout::state.isCartEmpty' );
	}

	return $p->get_updated_html();

}
