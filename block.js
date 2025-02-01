( function( blocks, i18n, element ) {
	var el = element.createElement;

	blocks.registerBlockType( 'otd/on-this-day', {
		title: i18n.__( 'On This Day', 'otd' ),
		icon: 'calendar-alt',
		category: 'widgets',
		description: i18n.__( 'Displays posts published on this day in previous years.', 'otd' ),
		edit: function() {
			return el(
				'div',
				{ className: 'otd-block-editor' },
				el( 'p', {}, i18n.__( 'This block will display posts from previous years that were published on today’s date.', 'otd' ) )
			);
		},
		// The save function is null because the content is rendered dynamically in PHP.
		save: function() {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.i18n, window.wp.element );
