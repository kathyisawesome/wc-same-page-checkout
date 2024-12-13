<?php
/*
 * Plugin Name: Same Page Checkout for WooCommerce
 * Plugin URI: https://github.com/kathyisawesome/wc-same-page-checkout
 * Description: A block pattern for creating one page checkouts in WooCommerce
 * Version: 1.0.0-alpha.1
 * Author: Backcourt Development
 * Author URI: http://kathyisawesome.com
 * Requires at least: 6.5.0
 * Tested up to: 6.6.0
 * WC requires at least: 9.0.0    
 * WC tested up to: 9.5.0   
 * 
 * GitHub Plugin URI: https://github.com/kathyisawesome/wc-same-page-checkout
 * Primary Branch: trunk
 * Release Asset: true
 *
 * Text Domain: wc-same-page-checkout
 * Domain Path: /languages/
 *
 * Copyright: © 2024 Backcourt Development.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Backcourt\SamePageCheckout;

defined( 'ABSPATH' ) || exit;

// Store the main plugin file.
const PLUGINFILE = __FILE__;

// Store the plugin version.
const VERSION = '1.0.0-alpha.1';

/**
 * Includes.
 */
require plugin_dir_path( __FILE__ ) . '/includes/Blocks/AddToCartForm.php';
require plugin_dir_path( __FILE__ ) . '/includes/Blocks/Checkout.php';
require plugin_dir_path( __FILE__ ) . '/includes/Blocks/Group.php';

/**
 * Declare WooCommerce Features compatibility.
 */
add_action( 'before_woocommerce_init', function() {
	if ( ! class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		return;
	}

	// HPOS (Custom Order tables) compatibility.
	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( __FILE__ ), true );

	// Cart and Checkout Blocks.
	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', plugin_basename( __FILE__ ), true );

} );

/**
 * Register the block pattern.
 */
add_action( 'init', function() {

	ob_start();
	include __DIR__ . "/patterns/same-page-checkout.html";
	$pattern = ob_get_clean();

	register_block_pattern(
		'backcourt/same-page-checkout',
		array(
			'title'    => esc_html__( 'Same Page Checkout', 'wc-same-page-checkout' ),
			'inserter' => true,
			'content'  => $pattern,
			'category' => 'woocommerce',
		)
	);

} );

/**
 * Universally set my state - needs to happen before any block is parsed.
 */
add_action( 'wp', function() {

	$initial_cart_count = \wc()->cart ? \wc()->cart->get_cart_contents_count() : 0;

	wp_interactivity_state(
		'backcourt/same-page-checkout',
		array(
			'cartCount' => $initial_cart_count,
			'isCartEmpty' => 0 === $initial_cart_count,
		)
	);

});
