/**
 * FANUC Simulator — script admin.
 * Nessuna dipendenza: copia dello shortcode negli appunti.
 */
( function () {
	'use strict';

	var L10n = window.fanucSimL10n || {};

	function message( text, isError ) {
		var box = document.getElementById( 'fanuc-sim-copy-msg' );
		if ( ! box ) {
			return;
		}
		box.textContent = text;
		box.classList.toggle( 'is-error', !! isError );
		window.clearTimeout( box._timer );
		box._timer = window.setTimeout( function () {
			box.textContent = '';
			box.classList.remove( 'is-error' );
		}, 3000 );
	}

	/**
	 * Copia negli appunti. L'API asincrona non e' disponibile in contesti non
	 * sicuri (http://), quindi si ricade su execCommand con la selezione del
	 * campo, che funziona ovunque ed e' comunque un gesto dell'utente.
	 */
	function copy( input ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( input.value ).then(
				function () {
					message( L10n.copied || 'Copiato.' );
				},
				function () {
					legacyCopy( input );
				}
			);
			return;
		}
		legacyCopy( input );
	}

	function legacyCopy( input ) {
		input.focus();
		input.select();
		input.setSelectionRange( 0, input.value.length );
		var ok = false;
		try {
			ok = document.execCommand( 'copy' );
		} catch ( e ) {
			ok = false;
		}
		message(
			ok ? ( L10n.copied || 'Copiato.' ) : ( L10n.copyError || 'Copia non riuscita.' ),
			! ok
		);
	}

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest ? e.target.closest( '[data-fanuc-copy]' ) : null;
		if ( ! btn ) {
			return;
		}
		e.preventDefault();
		var input = document.getElementById( btn.getAttribute( 'data-fanuc-copy' ) );
		if ( input ) {
			copy( input );
		}
	} );
}() );
