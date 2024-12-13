import { registerBlockStyle } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor. All other files
 * get applied to the editor only.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './edit.scss';

// Register the no-quantity input block style
registerBlockStyle('woocommerce/add-to-cart-form', {
    name: 'hide-quantity-input',
    label: __( 'Hide Quantity Selector', 'wc-same-page-checkout' )
});
