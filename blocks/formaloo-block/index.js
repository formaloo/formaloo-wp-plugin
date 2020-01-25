( function( wp ) {
	/**
	 * Registers a new block provided a unique name and an object defining its behavior.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/#registering-a-block
	 */
	var registerBlockType = wp.blocks.registerBlockType;
	/**
	 * Returns a new element of given type. Element is an abstraction layer atop React.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/packages/packages-element/
	 */

	/**
	 * Retrieves the translation of text.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/packages/packages-i18n/
	 */
	var __ = wp.i18n.__;

	var smileIcon = wp.element.createElement(
		"svg",
		{
			width: 20,
			height: 20
		},
		wp.element.createElement("path", {
			d:
				"M10 0.4c-5.302 0-9.6 4.298-9.6 9.6s4.298 9.6 9.6 9.6c5.301 0 9.6-4.298 9.6-9.601 0-5.301-4.299-9.599-9.6-9.599zM10 17.599c-4.197 0-7.6-3.402-7.6-7.6s3.402-7.599 7.6-7.599c4.197 0 7.601 3.402 7.601 7.6s-3.404 7.599-7.601 7.599zM7.501 9.75c0.828 0 1.499-0.783 1.499-1.75s-0.672-1.75-1.5-1.75-1.5 0.783-1.5 1.75 0.672 1.75 1.501 1.75zM12.5 9.75c0.829 0 1.5-0.783 1.5-1.75s-0.672-1.75-1.5-1.75-1.5 0.784-1.5 1.75 0.672 1.75 1.5 1.75zM14.341 11.336c-0.363-0.186-0.815-0.043-1.008 0.32-0.034 0.066-0.869 1.593-3.332 1.593-2.451 0-3.291-1.513-3.333-1.592-0.188-0.365-0.632-0.514-1.004-0.329-0.37 0.186-0.52 0.636-0.335 1.007 0.050 0.099 1.248 2.414 4.672 2.414 3.425 0 4.621-2.316 4.67-2.415 0.184-0.367 0.036-0.81-0.33-0.998z"
		})
	);

	/**
	 * Every block starts by registering a new block type definition.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/#registering-a-block
	 */
	registerBlockType( 'formaloo/formaloo-block', {
		/**
		 * This is the display title for your block, which can be translated with `i18n` functions.
		 * The block inserter will show this name.
		 */
		title: __( 'The Formaloo Block', 'formaloo' ),

		description: __('Use this block to show forms from Formaloo.'),

		/**
		 * An icon property should be specified to make it easier to identify a block.
		 * These can be any of WordPress’ Dashicons, or a custom svg element.
		 */
		icon: smileIcon,

		/**
		 * Blocks are grouped into categories to help users browse and discover them.
		 * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.
		 */
		category: 'embed',

		/**
		 * Optional block extended support features.
		 */
		supports: {
			// Removes support for an HTML mode.
			html: false,
		},

		attributes: {
			url: {
				type: 'string'
			},
			show_type: {
				type: 'string',
				default: "link"
			},
			link_title: {
				type: 'string',
				default: __("Show Form")
			},
			show_title: {
				type: 'boolean',
				default: true,
			},
			show_descr: {
				type: 'boolean',
				default: true,
			},
			show_logo: {
				type: 'boolean',
				default: true,
			},
		},

		/**
		 * The edit function describes the structure of your block in the context of the editor.
		 * This represents what the editor will render when the block is used.
		 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-edit-save/#edit
		 *
		 * @param {Object} [props] Properties passed from the editor.
		 * @return {Element}       Element to render.
		 */
		edit: (props) => {
			var onChangeURL = function onChangeURL(value) {
				props.setAttributes({
					url: value
				});
			};
	
			var onChangeShowType = function onChangeShowType(value) {
				props.setAttributes({
					show_type: value
				});
			};
	
			var onChangeLinkTitle = function onChangeLinkTitle(value) {
				props.setAttributes({
					link_title: value
				});
			};
	
			var onChangeShowLogo = function onChangeShowLogo(value) {
				props.setAttributes({
					show_logo: value
				});
			};
	
			var onChangeShowTitle = function onChangeShowTitle(value) {
				props.setAttributes({
					show_title: value
				});
			};
	
			var onChangeShowDescription = function onChangeShowDescription(value) {
				props.setAttributes({
					show_descr: value
				});
			};
			
			// https://regexr.com/3um70
			function is_url(str) {
				regexp = /^(https?|chrome):\/\/[^\s$.?#].[^\s]*$/g;
				if (regexp.test(str)) {
					return true;
				} else {
					return false;
				}
			}

			return (
				wp.element.createElement(
					wp.element.Fragment,
					null,
					wp.element.createElement(
						wp.editor.InspectorControls,
						null,
						wp.element.createElement( 'hr', {
              style: {marginTop:20}
            }),
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: __('Show Type'),
								value: props.attributes.show_type,
								options: [
									{
										value: null,
										label: __('Select a Show Type'),
										disabled: true
									},
									{
										value: 'link',
										label: __('Link')
									},
									{
										value: 'iframe',
										label: __('iFrame')
									},
									{
										value: 'script',
										label: __('Script')
									}
								],
								onChange: onChangeShowType
							}
						),
						props.attributes.show_type == 'link' && wp.element.createElement(
							wp.components.TextControl,
							{
								label: __('Link Title'),
								//help: 'If you choose ',
								value: props.attributes.link_title,
								onChange: onChangeLinkTitle
							}
						),
						props.attributes.show_type == 'script' &&
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: __('Show Logo'),
								checked: props.attributes.show_logo,
								onChange: onChangeShowLogo
							}
						),
						props.attributes.show_type == 'script' &&
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: __('Show Title'),
								checked: props.attributes.show_title,
								onChange: onChangeShowTitle
							}
						),
						props.attributes.show_type == 'script' &&
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: __('Show Description'),
								checked: props.attributes.show_descr,
								onChange: onChangeShowDescription
							}
						),
					),
					wp.element.createElement(
						'div',
						null,
						wp.element.createElement(
							'div',
							{
								className: 'formaloo-guten-div'
							},
							wp.element.createElement(wp.components.TextControl, {
								label: __('Form URL'),
								placeholder: __('Enter the Form URL'),
								onChange: onChangeURL,
								value: props.attributes.url || ''
							}),
							(!props.attributes.url) && wp.element.createElement(
								'p',
								{
									className: 'formaloo-back-info'
								},
								__('Please enter the form URL in the text field above')
							),
							is_url(props.attributes.url) && wp.element.createElement(
								'p',
								{
									className: 'formaloo-back-success'
								},
								__('* You can change form view options on the inspector control')
							),
							!is_url(props.attributes.url) && (props.attributes.url) && wp.element.createElement(
								'p',
								{
									className: 'formaloo-back-err'
								},
								__('Please enter a valid url')
							),
						),
					)
				)

			);
		},

		/**
		 * The save function defines the way in which the different attributes should be combined
		 * into the final markup, which is then serialized by Gutenberg into `post_content`.
		 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-edit-save/#save
		 *
		 * @return {Element}       Element to render.
		 */
		save: function() {
			return null;
		}
	} );
} )(
	window.wp
);
