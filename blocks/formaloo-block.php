<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package formaloo
 */

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function formaloo_block_block_init() {
	// Skip block registration if Gutenberg is not enabled/merged.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$dir = dirname( __FILE__ );
	global $formalooClss;

	$index_js = 'formaloo-block/index.js';
	wp_register_script(
		'formaloo-block-block-editor',
		plugins_url( $index_js, __FILE__ ),
		array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
			'wp-components',
			'wp-editor'
		),
		filemtime( "$dir/$index_js" )
	);

	$editor_css = 'formaloo-block/editor.css';
	wp_register_style(
		'formaloo-block-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'formaloo-block/style.css';
	wp_register_style(
		'formaloo-block-block',
		plugins_url( $style_css, __FILE__ ),
		array(),
		filemtime( "$dir/$style_css" )
	);

	register_block_type( 'formaloo/formaloo-block', array(
		'editor_script' => 'formaloo-block-block-editor',
		'editor_style'  => 'formaloo-block-block-editor',
		'style'         => 'formaloo-block-block',
		'render_callback' => [$formalooClss, 'formaloo_gutenberg_block_callback'],
		'attributes'	  => array(
				'url' => array(
						'type' => 'string',
				),
				'show_type' => array (
						'type' => 'string',
						'default' => 'link',
				),
				'link_title' => array( 
						'type' => 'string',
						'default' => 'Show Form',
				),
				'show_title' => array (
						'type' => 'boolean',
						'default' => true,
				),
				'show_descr' => array(
						'type' => 'boolean',
						'default' => true,
				),
				'show_logo' => array(
						'type' => 'boolean',
						'default' => true,
				),
		),
	) );
}
add_action( 'init', 'formaloo_block_block_init' );
