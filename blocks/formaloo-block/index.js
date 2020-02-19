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
				"M14.8,3L6.7,3c-1.6,0-3,1.3-3,2.9l0,8.1c0,1.6,1.3,3,2.9,3l8.1,0c1.6,0,3-1.3,3-2.9l0-8.1C17.7,4.4,16.4,3,14.8,3z M8.7,13.2c0,0.4-0.3,0.6-0.6,0.6l-0.6,0c-0.4,0-0.6-0.3-0.6-0.6l0-0.6c0-0.4,0.3-0.6,0.6-0.6l0.6,0c0.4,0,0.6,0.3,0.6,0.6L8.7,13.2 z M11.7,10.4c0,0.3-0.3,0.6-0.6,0.6l-3.7,0c-0.3,0-0.6-0.3-0.6-0.6l0-0.8C6.9,9.3,7.1,9,7.4,9l3.7,0c0.3,0,0.6,0.3,0.6,0.6 L11.7,10.4z M14.6,7.5c0,0.3-0.3,0.6-0.6,0.6L7.5,8C7.1,8,6.9,7.8,6.9,7.4l0-0.7c0-0.3,0.3-0.6,0.6-0.6l6.5,0c0.3,0,0.6,0.3,0.6,0.6 L14.6,7.5z"
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
				default: "script"
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
								__('Please enter the form URL in the text field above e.g. https://formaloo.net/feedback')
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
