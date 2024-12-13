/**
 * WordPress dependencies
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor. All other files
 * get applied to the editor only.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './view.scss';

import { serializeForm } from './serializeForm';

const cartStoreKey = 'wc/store/cart';

const { state } = store( 'backcourt/same-page-checkout', {

	state: {
		get isCartEmpty() {
            return state.cartCount === 0;
        },
	},
	actions: {
		*addToCart(event) {
			event.preventDefault();

			const context = getContext();
			const { productId } = context;

			try {

				const form = event.target;

				if (!(form instanceof HTMLFormElement)) {
					throw new Error("The provided element is not a valid HTMLFormElement.");
				}

				if (!(productId)) {
					throw new Error("No product ID defined.");
				}

				let additionalData = serializeForm(form);
				let variation = [];

				// Get quantity from the form.
				const quantityToAdd = parseInt(additionalData?.quantity ?? 1);
				delete additionalData.quantity;

				// Get any variable attributes from the form.
				for (const key in additionalData) {
					if (key.startsWith("attribute_")) {
						// Extract the attribute key and value
						const attribute = key.replace("attribute_", "");
	
						variation.push({
							attribute: attribute,
							value: additionalData[key]
						});
						// Delete the key from the original object
						delete additionalData[key];
					}
				}

				yield wp.data.dispatch( cartStoreKey ).addItemToCart(
					productId,
					quantityToAdd,
					variation,
					additionalData		
				);			

			} catch( error ) {
				console.error( error );
			}

		},
		updateQuantity(event) {
			const context = getContext();
			context.quantityToAdd = parseInt(event.target.value);
		},
	},
	callbacks: {
		setQuantity: () => {
            const context = getContext();
			const { ref } = getElement();
			context.quantityToAdd = parseInt(ref.value);
        },
    },
} );

// @todo Test wp.data is defined and working

// Subscribe to changes in Cart data.i'm inclined to 
wp.data.subscribe( () => {
	const cartData = wp.data.select( cartStoreKey ).getCartData();
	const isResolutionFinished =
		wp.data.select( cartStoreKey ).hasFinishedResolution( 'getCartData' );
	if ( isResolutionFinished && cartData.itemsCount !== state.cartCount ) {
		state.cartCount = cartData.itemsCount;
	}
}, cartStoreKey );
