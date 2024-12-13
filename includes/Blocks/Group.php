<?php
/**
 * Modify the Checkout Block.
 *
 * @package WC_Same_Page_Checkout\Blocks
 */
namespace Backcourt\SamePageCheckout\Blocks\Group;

defined( 'ABSPATH' ) || exit;

/**
 * Hooks
 */
add_filter( 'render_block_core/group', __NAMESPACE__ . '\modify_output', 10, 3 );

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

	$pattern = $block['attrs']['metadata']['patternName'] ?? '';

	if ( is_admin() || $pattern !== 'backcourt/same-page-checkout' ) {
		return $block_content;
	}

	$p = new \WP_HTML_Tag_Processor( $block_content );

	if ( $p->next_tag( array( 'class_name' => 'wp-block-group' ) ) ) {
	    $p->add_class( 'backcourt-same-page-checkout' );
	}

	$block_content = $p->get_updated_html();

	return $block_content;

}
