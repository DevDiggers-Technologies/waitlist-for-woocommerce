/* global ddwcwlAdminObj, tinymce */
'use strict';

import './admin.less';

const { confirmDelete, confirmNotify, ajaxUrl, previewNonce, previewTitle, close, sending, error } = window.ddwcwlAdminObj || {};

/**
 * Toggle an inline admin form (add subscriber).
 *
 * @param {string} id Form ID.
 */
const toggleForm = ( id ) => {
	const form = document.getElementById( id );

	if ( ! form ) {
		return;
	}

	form.hidden = ! form.hidden;

	if ( ! form.hidden ) {
		const input = form.querySelector( 'input:not([type="hidden"])' );

		if ( input ) {
			input.focus();
		}
	}
};

/**
 * Insert a placeholder into the last focused subject, heading or content field.
 */
let lastField = null;

document.addEventListener( 'focusin', ( event ) => {
	if ( event.target.matches( '.ddwcwl-placeholder-target, .wp-editor-area' ) ) {
		lastField = event.target;
	}
} );

const insertPlaceholder = ( placeholder ) => {
	const editorId = document.querySelector( '.wp-editor-area' ) ? document.querySelector( '.wp-editor-area' ).id : '';
	const editor = window.tinymce && editorId ? tinymce.get( editorId ) : null;

	// Visual editor active and no text field picked: insert into the content.
	if ( ( ! lastField || lastField.classList.contains( 'wp-editor-area' ) ) && editor && ! editor.isHidden() ) {
		editor.execCommand( 'mceInsertContent', false, placeholder );
		return;
	}

	const field = lastField || document.querySelector( '.wp-editor-area' );

	if ( ! field ) {
		return;
	}

	const start = field.selectionStart ?? field.value.length;
	const end = field.selectionEnd ?? field.value.length;

	field.value = field.value.slice( 0, start ) + placeholder + field.value.slice( end );
	field.focus();
	field.setSelectionRange( start + placeholder.length, start + placeholder.length );
};

/**
 * Open the email preview in a dialog.
 *
 * @param {string} url Preview URL.
 */
const openPreview = ( url ) => {
	let dialog = document.querySelector( '.ddwcwl-preview-dialog' );

	if ( ! dialog ) {
		dialog = document.createElement( 'dialog' );
		dialog.className = 'ddwcwl-preview-dialog';
		dialog.innerHTML = '<div class="ddwcwl-preview-head"><strong></strong><button type="button" class="ddwcwl-preview-close"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button></div><iframe title=""></iframe>';
		dialog.querySelector( 'strong' ).textContent = previewTitle;
		dialog.querySelector( '.ddwcwl-preview-close' ).setAttribute( 'aria-label', close );
		dialog.querySelector( 'iframe' ).title = previewTitle;
		dialog.querySelector( '.ddwcwl-preview-close' ).addEventListener( 'click', () => dialog.close() );
		dialog.addEventListener( 'click', ( event ) => event.target === dialog && dialog.close() );
		document.body.appendChild( dialog );
	}

	dialog.querySelector( 'iframe' ).src = url;
	dialog.showModal();
};

/**
 * Send a test email.
 *
 * @param {HTMLElement} toolbar Toolbar element.
 */
const sendTest = ( toolbar ) => {
	const button = toolbar.querySelector( '.ddwcwl-send-test' );
	const result = toolbar.querySelector( '.ddwcwl-test-result' );
	const input = toolbar.querySelector( 'input[type="email"]' );

	if ( ! input.checkValidity() || ! input.value ) {
		input.reportValidity();
		return;
	}

	const data = new FormData();

	data.append( 'action', 'ddwcwl_send_test_email' );
	data.append( 'nonce', previewNonce );
	data.append( 'type', toolbar.dataset.type );
	data.append( 'recipient', input.value );

	button.disabled = true;
	result.className = 'ddwcwl-test-result';
	result.textContent = sending;

	fetch( ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
		.then( ( response ) => response.json() )
		.then( ( { success, data: response } ) => {
			result.classList.add( success ? 'ddwcwl-success' : 'ddwcwl-error' );
			result.textContent = response.message || error;
		} )
		.catch( () => {
			result.classList.add( 'ddwcwl-error' );
			result.textContent = error;
		} )
		.finally( () => ( button.disabled = false ) );
};

document.addEventListener( 'click', ( event ) => {
	const target = event.target;

	if ( target.closest( '.ddwcwl-toggle-add' ) ) {
		event.preventDefault();
		toggleForm( 'ddwcwl-add-subscriber' );
		return;
	}

	if ( target.closest( '.ddwcwl-placeholder' ) ) {
		event.preventDefault();
		insertPlaceholder( target.closest( '.ddwcwl-placeholder' ).dataset.placeholder );
		return;
	}

	if ( target.closest( '.ddwcwl-preview-email' ) ) {
		event.preventDefault();
		openPreview( target.closest( '.ddwcwl-preview-email' ).dataset.url );
		return;
	}

	if ( target.closest( '.ddwcwl-send-test' ) ) {
		event.preventDefault();
		sendTest( target.closest( '.ddwcwl-email-toolbar' ) );
		return;
	}

	const link = target.closest( '.ddwcwl-confirm-delete, .ddwcwl-confirm-notify' );

	if ( link && ! window.confirm( link.classList.contains( 'ddwcwl-confirm-delete' ) ? confirmDelete : confirmNotify ) ) {
		event.preventDefault();
	}
} );

// Enter in the test email field sends the test instead of saving the settings form.
document.addEventListener( 'keydown', ( event ) => {
	if ( 'Enter' === event.key && event.target.closest( '.ddwcwl-test-email' ) ) {
		event.preventDefault();
		sendTest( event.target.closest( '.ddwcwl-email-toolbar' ) );
	}
} );

// Confirm destructive bulk actions.
document.addEventListener( 'submit', ( event ) => {
	const form = event.target;

	if ( ! form.querySelector( '.wp-list-table' ) || ! event.submitter || ! /^doaction2?$/.test( event.submitter.id ) ) {
		return;
	}

	const select = form.querySelector( 'doaction2' === event.submitter.id ? '#bulk-action-selector-bottom' : '#bulk-action-selector-top' );
	const action = select ? select.value : '';

	if ( ( 'delete' === action || 'clear' === action ) && ! window.confirm( confirmDelete ) ) {
		event.preventDefault();
	} else if ( ( 'notify' === action || 'notify_all' === action ) && ! window.confirm( confirmNotify ) ) {
		event.preventDefault();
	}
} );
