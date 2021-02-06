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
		const selectors = $( '[role="tw-city-selector"]' );
		selectors.each( function () {
			const that = $( this );
			that.children( '.county, .district' ).on( 'change', function () {
				const street =
						$( '.street' ).length !== 0 ? $( '.street' ).val() : '',
					district = that.attr( 'data-hidden-district' )
						? ''
						: $( '.district' ).val();
				that.parent()
					.children( '[class*=address]' )
					.children( '[name*=address]' )
					.val(
						$( '.zipcode' ).val() +
							$( '.county' ).val() +
							district +
							street
					);
			} );
			if ( $( this ).attr( 'data-hidden-district' ) ) {
				$( this ).children( '.district' ).attr( 'hidden', 'hidden' );
			}
		} );
	} );
	// eslint-disable-next-line no-undef
} )( jQuery );
