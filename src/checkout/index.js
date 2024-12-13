/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';

function addCheckoutAttributes( settings, name ) {
    if ( name !== 'woocommerce/checkout' ) {
        return settings;
    }
    return {
        ...settings,
        attributes: {
            ...settings.attributes,
            isDescendentOfSamePageCheckout: {
                "type": "boolean",
                "default": false
            },
        },
    };
}

addFilter(
    'blocks.registerBlockType',
    'backcourt-same-page-checkout/checkout-attributes',
    addCheckoutAttributes
);
