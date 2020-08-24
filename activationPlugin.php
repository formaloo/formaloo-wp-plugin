<?php

/**
 * Get the current time and set it as an option when the plugin is activated.
 *
 * @return null
 */
function formaloo_set_activation() {

	/* Create transient data */
	// set_transient( 'formaloo_admin_notice_activation', true, 5 );

	$now = strtotime( "now" );
	add_option( 'formaloo_activation_date', $now );

}
register_activation_hook( __FILE__, 'formaloo_set_activation' );

/* Add admin notice */
// add_action( 'admin_notices', 'formaloo_admin_notice_activation_notice' );

/**
 * Check date on admin initiation and add to admin notice if it was over 10 days ago.
 *
 * @return null
 */
function formaloo_check_installation_date() {

	// Added Lines Start
	$nobug = "";
	$nobug = get_option('formaloo_no_bug');

	if (!$nobug) {

		$install_date = get_option( 'formaloo_activation_date' );
		$past_date    = strtotime( '+7 days' );

		if ( $past_date >= $install_date ) {

			add_action( 'admin_notices', 'formaloo_display_admin_notice' );

		}

	}
}
add_action( 'admin_init', 'formaloo_check_installation_date' );


/**
 * Display Admin Notice, asking for a review
 *
 * @return null
 */
function formaloo_display_admin_notice() {

	$reviewurl = 'https://wordpress.org/plugins/formaloo-form-builder/#reviews';

	$nobugurl = get_admin_url() . '?formaloonobug=1';

	echo '<div class="updated"><p>';
	printf( __( "You have been using our plugin for a week now, do you like it? If so, please leave us a review with your feedback! <br /><br /> <a href='%s' target='_blank'>Leave A Review</a>/<a href='%s'>Leave Me Alone</a>" ), $reviewurl, $nobugurl );
	echo "</p></div>";
}


/**
 * Set the plugin to no longer bug users if user asks not to be.
 *
 * @return null
 */
function formaloo_set_no_bug() {

	$nobug = "";

	if ( isset( $_GET['formaloonobug'] ) ) {
		$nobug = esc_attr( $_GET['formaloonobug'] );
	}

	if ( 1 == $nobug ) {

		add_option( 'formaloo_no_bug', TRUE );

	}

} add_action( 'admin_init', 'formaloo_set_no_bug', 5 );


/**
 * Admin Notice on Activation.
 * @since 0.1.0
 */
function formaloo_admin_notice_activation_notice(){

	/* Check transient, if available display notice */
	if( get_transient( 'formaloo_admin_notice_activation' ) ){
			?>
			<div class="updated notice is-dismissible">
			<p><?php echo __('Thank you for using the Formaloo plugin!', 'formaloo-form-builder') ?> <a href="<?php echo admin_url( "admin.php?page=formaloo-settings-page" ) ?>"><strong><?php echo __('Get Started by visiting the Settings Page', 'formaloo-form-builder') ?></strong></a>.</p>
			</div>
			<?php
			/* Delete transient, only display this notice once. */
			delete_transient( 'formaloo_admin_notice_activation' );
	}
}