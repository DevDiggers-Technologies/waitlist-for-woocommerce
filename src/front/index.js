/* global ddwcwlFrontObj, jQuery */
'use strict';

import './front.less';

const { ajaxUrl, nonce, i18n } = window.ddwcwlFrontObj || {};

/**
 * Show an inline message inside a waiting list box.
 *
 * @param {HTMLElement} box     Waiting list wrapper.
 * @param {string}      message Message, empty to clear.
 * @param {boolean}     success Success state.
 */
const showNotice = ( box, message, success ) => {
	const area = box.querySelector( '.ddwcwl-notice-area' );

	area.hidden = ! message;

	if ( ! message ) {
		area.replaceChildren();
		return;
	}

	const notice = document.createElement( 'div' );
	const custom = box.dataset.customNotice;

	notice.setAttribute( 'role', success ? 'status' : 'alert' );
	notice.textContent = message;
	notice.className = 'ddwcwl-message ' + ( success ? 'ddwcwl-message-success' : 'ddwcwl-message-error' );

	if ( custom ) {
		notice.className = 'ddwcwl-custom-notice';
		notice.setAttribute( 'style', custom );
	}

	area.replaceChildren( notice );
};

/**
 * Toggle a fact line and fill its value.
 *
 * @param {HTMLElement} box      Wrapper.
 * @param {string}      selector Element selector.
 * @param {boolean}     visible  Visible.
 */
const toggle = ( box, selector, visible ) => {
	const el = box.querySelector( selector );

	if ( el ) {
		el.hidden = ! visible;
	}
};

/**
 * Paint a product/variation state onto the box: count, joined, position.
 *
 * @param {HTMLElement} box   Wrapper.
 * @param {Object}      state State from PHP or a join response.
 */
const applyState = ( box, state ) => {
	const count = Number( state.count ) || 0;
	const countLine = box.querySelector( '.ddwcwl-count' );

	if ( countLine ) {
		countLine.textContent = ( 1 === count ? i18n.countSingular : i18n.countPlural ).replace( '%s', count.toLocaleString() );
	}

	toggle( box, '.ddwcwl-fact-count', !! box.dataset.showCount && count > 0 );

	const position = box.querySelector( '.ddwcwl-position-value' );

	if ( position ) {
		position.textContent = ( Number( state.position ) || 0 ).toLocaleString();
	}

	toggle( box, '.ddwcwl-position', !! box.dataset.showPosition && Number( state.position ) > 0 );
	box.classList.toggle( 'ddwcwl-joined', !! state.joined );

	const facts = box.querySelector( '.ddwcwl-facts' );

	if ( facts ) {
		facts.hidden = ! [ ...facts.querySelectorAll( '.ddwcwl-fact' ) ].some( ( fact ) => ! fact.hidden && 'none' !== window.getComputedStyle( fact ).display );
	}
};

/**
 * Point every product id input of a box at a product or variation.
 *
 * @param {HTMLElement} box Wrapper.
 * @param {number}      id  Product ID.
 */
const setProduct = ( box, id ) => {
	box.querySelectorAll( '.ddwcwl-product-id' ).forEach( ( input ) => ( input.value = id ) );

	const variations = box.dataset.variations ? JSON.parse( box.dataset.variations ) : null;

	if ( variations && variations[ id ] ) {
		showNotice( box, '' );
		applyState( box, variations[ id ] );
	}
};

/**
 * Submit a join/leave form over AJAX, falling back to a normal post on network failure.
 *
 * @param {HTMLFormElement} form Form.
 */
const submitForm = ( form ) => {
	const box = form.closest( '.ddwcwl-waiting-list' );
	const button = form.querySelector( 'button[type="submit"]' );
	const data = new FormData( form );
	const leave = 'leave' === data.get( 'ddwcwl_form_action' );
	const email = form.querySelector( '.ddwcwl-email' );

	if ( email && ! email.checkValidity() ) {
		email.reportValidity();
		return;
	}

	if ( ! form.checkValidity() ) {
		form.reportValidity();
		return;
	}

	data.append( 'action', leave ? 'ddwcwl_leave_waiting_list' : 'ddwcwl_join_waiting_list' );
	data.append( 'nonce', nonce );

	button.disabled = true;
	box.classList.add( 'ddwcwl-loading' );

	fetch( ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
		.then( ( response ) => response.json() )
		.then( ( { success, data: result } ) => {
			if ( ! success ) {
				showNotice( box, result.message || i18n.error, false );
				return;
			}

			const state = {
				count: result.count,
				joined: ! leave,
				position: result.position || 0,
			};

			// Keep the stored state of the current product in sync.
			const id = data.get( 'ddwcwl_product_id' );
			const variations = box.dataset.variations ? JSON.parse( box.dataset.variations ) : null;
			const base = variations ? variations[ id ] : JSON.parse( box.dataset.state || '{}' );

			Object.assign( base || {}, state );

			if ( variations && variations[ id ] ) {
				box.dataset.variations = JSON.stringify( variations );
			} else if ( ! variations ) {
				box.dataset.state = JSON.stringify( base );
			}

			applyState( box, base || state );

			if ( leave ) {
				showNotice( box, result.message, true );
				return;
			}

			showNotice( box, '' );
			box.querySelector( '.ddwcwl-done-title' ).textContent = result.title || i18n.joined;
			box.querySelector( '.ddwcwl-done-message' ).textContent = ! result.position || ! box.dataset.showPosition ? result.message : '';

			if ( email ) {
				email.value = '';
			}

			box.querySelector( '.ddwcwl-done' ).focus( { preventScroll: true } );
		} )
		.catch( () => form.submit() )
		.finally( () => {
			button.disabled = false;
			box.classList.remove( 'ddwcwl-loading' );
		} );
};

document.addEventListener( 'submit', ( event ) => {
	const form = event.target.closest( '.ddwcwl-form' );

	if ( form && ajaxUrl ) {
		event.preventDefault();
		submitForm( form );
	}
} );

// Paint the initial state of simple products (hides empty fact lists).
document.querySelectorAll( '.ddwcwl-waiting-list[data-state]' ).forEach( ( box ) => {
	if ( box.dataset.state ) {
		applyState( box, JSON.parse( box.dataset.state ) );
	}
} );

document.addEventListener( 'change', ( event ) => {
	if ( event.target.matches( '.ddwcwl-variation-select' ) ) {
		setProduct( event.target.closest( '.ddwcwl-waiting-list' ), event.target.value );
	}
} );

// A shortcode away from the product page carries its own variation picker: show the first option's state.
document.querySelectorAll( '.ddwcwl-context-shortcode .ddwcwl-variation-select' ).forEach( ( select ) => {
	setProduct( select.closest( '.ddwcwl-waiting-list' ), select.value );
} );

// Variable product pages: show the form only for eligible (out of stock) variations.
if ( window.jQuery ) {
	/**
	 * Waiting list box belonging to a variations form.
	 *
	 * @param {HTMLElement} form Variations form.
	 * @return {HTMLElement|null} Box.
	 */
	const findBox = ( form ) => {
		const scope = form.closest( '.product' ) || document;

		return scope.querySelector( '.ddwcwl-context-product[data-variations]:not([data-variations=""])' );
	};

	jQuery( document.body ).on( 'found_variation', '.variations_form', function ( event, variation ) {
		const box = findBox( this );

		if ( ! box ) {
			return;
		}

		const state = JSON.parse( box.dataset.variations )[ variation.variation_id ];

		box.hidden = ! state;

		if ( state ) {
			setProduct( box, variation.variation_id );
		}
	} );

	jQuery( document.body ).on( 'reset_data hide_variation', '.variations_form', function () {
		const box = findBox( this );

		if ( box ) {
			box.hidden = true;
		}
	} );
}
