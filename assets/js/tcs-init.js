// eslint-disable-next-line no-undef
new TwCitySelector();
( function ( $ ) {
	$( 'document' ).ready( function () {
		$( '.street' ).change( function () {
			$( this )
				.parent()
				.children( '[name*=address]' )
				.val(
					$( '.zipcode' ).val() +
						$( '.county' ).val() +
						$( '.district' ).val() +
						$( '.street' ).val()
				);
		} );
	} );
	// eslint-disable-next-line no-undef
} )( jQuery );
