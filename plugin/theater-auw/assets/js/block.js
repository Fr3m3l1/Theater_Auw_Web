/**
 * Block «Theater Auw» — serverseitig gerendert, damit im Editor genau das
 * steht, was später live steht. Kein Build-Schritt: reines JS gegen wp.*.
 */
( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'theater-auw/seite', {
		edit: function ( props ) {
			var phases = [
				{ label: __( 'Automatisch (empfohlen)', 'theater-auw' ), value: '' },
				{ label: 'Vorbereitung', value: 'vorbereitung' },
				{ label: 'Vorverkauf', value: 'vorverkauf' },
				{ label: 'Spielzeit', value: 'spielzeit' },
				{ label: 'Danke', value: 'danke' }
			];

			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Phase', 'theater-auw' ) },
						el( components.SelectControl, {
							label: __( 'Angezeigte Phase', 'theater-auw' ),
							help: __( 'Automatisch folgt den Einstellungen unter «Theater Auw». Eine feste Phase hier überschreibt das nur für diesen Block.', 'theater-auw' ),
							value: props.attributes.phase,
							options: phases,
							onChange: function ( v ) {
								props.setAttributes( { phase: v } );
							}
						} )
					)
				),
				el( 'div', blockEditor.useBlockProps(),
					el( serverSideRender, {
						block: 'theater-auw/seite',
						attributes: props.attributes
					} )
				)
			);
		},
		save: function () {
			return null; // serverseitig gerendert
		}
	} );
}(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender,
	window.wp.i18n
) );
