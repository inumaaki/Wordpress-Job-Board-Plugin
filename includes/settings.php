<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// function bjb_add_settings_page() -> Moved to admin-menu.php to ensure order
// add_action( 'admin_menu', 'bjb_add_settings_page' );

/**
 * Register Settings
 */
function bjb_register_settings() {
	// General Settings
	register_setting( 'bjb_general_options', 'bjb_general_currency_symbol' );
    register_setting( 'bjb_general_options', 'bjb_google_maps_api_key' );
    
    // Email Settings
    register_setting( 'bjb_email_options', 'bjb_email_from_name' );
    register_setting( 'bjb_email_options', 'bjb_email_from_address' );
}
add_action( 'admin_init', 'bjb_register_settings' );

/**
 * Render Settings Page
 */
function bjb_render_settings_page() {
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
	?>
	<div class="wrap bjb-settings-wrap">
		<h1><?php _e( 'Job Board Settings', 'wp-job-board' ); ?></h1>
		
		<h2 class="nav-tab-wrapper">
			<a href="?page=bjb-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e( 'General', 'wp-job-board' ); ?></a>
			<a href="?page=bjb-settings&tab=currencies" class="nav-tab <?php echo $active_tab == 'currencies' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Currencies', 'wp-job-board' ); ?></a>
			<a href="?page=bjb-settings&tab=emails" class="nav-tab <?php echo $active_tab == 'emails' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Emails', 'wp-job-board' ); ?></a>
		</h2>

		<form method="post" action="options.php">
			<?php
			if ( $active_tab == 'general' ) {
				settings_fields( 'bjb_general_options' );
				do_settings_sections( 'bjb_general_options' );
				?>
				<table class="form-table">
					<tr valign="top">
					    <th scope="row"><?php _e( 'Currency Symbol', 'wp-job-board' ); ?></th>
					    <td>
                            <input type="text" name="bjb_general_currency_symbol" value="<?php echo esc_attr( get_option( 'bjb_general_currency_symbol', '$' ) ); ?>" class="regular-text" />
                            <p class="description"><?php _e('E.g. $, €, £', 'wp-job-board'); ?></p>
                        </td>
					</tr>
                    <tr valign="top">
					    <th scope="row"><?php _e( 'Google Maps API Key', 'wp-job-board' ); ?></th>
					    <td>
                            <input type="text" name="bjb_google_maps_api_key" value="<?php echo esc_attr( get_option( 'bjb_google_maps_api_key' ) ); ?>" class="regular-text" />
                            <p class="description"><?php _e('Required for location services.', 'wp-job-board'); ?></p>
                        </td>
					</tr>
				</table>
				<?php
				submit_button();
			} elseif ( $active_tab == 'currencies' ) {
				echo '<div class="bjb-tab-content"><h3>' . __( 'Currency Settings', 'wp-job-board' ) . '</h3><p>' . __( 'Multi-currency support options will appear here.', 'wp-job-board' ) . '</p></div>';
			} elseif ( $active_tab == 'emails' ) {
                settings_fields( 'bjb_email_options' );
				do_settings_sections( 'bjb_email_options' );
                ?>
                <table class="form-table">
					<tr valign="top">
					    <th scope="row"><?php _e( 'Sender Name', 'wp-job-board' ); ?></th>
					    <td>
                            <input type="text" name="bjb_email_from_name" value="<?php echo esc_attr( get_option( 'bjb_email_from_name', get_bloginfo('name') ) ); ?>" class="regular-text" />
                        </td>
					</tr>
                    <tr valign="top">
					    <th scope="row"><?php _e( 'Sender Email', 'wp-job-board' ); ?></th>
					    <td>
                            <input type="email" name="bjb_email_from_address" value="<?php echo esc_attr( get_option( 'bjb_email_from_address', get_bloginfo('admin_email') ) ); ?>" class="regular-text" />
                        </td>
					</tr>
				</table>
                <?php
                submit_button();
			}
			?>
		</form>
	</div>
	<?php
}
