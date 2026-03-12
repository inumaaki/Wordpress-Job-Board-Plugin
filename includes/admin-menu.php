<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Main Job Board Menu & Submenus Manually
 */
function bjb_register_main_menu() {
	add_menu_page(
		__( 'Job Board', 'wp-job-board' ),
		__( 'Job Board', 'wp-job-board' ),
		'manage_options',
		'wp-job-board',
		'bjb_main_page_callback',
		'dashicons-businessperson',
		30
	);
    
    // 1. Dashboard
    add_submenu_page(
        'wp-job-board',
        __( 'Dashboard', 'wp-job-board' ),
        __( 'Dashboard', 'wp-job-board' ),
        'manage_options',
        'wp-job-board',
        'bjb_main_page_callback'
    );

    // 2. All Jobs
    add_submenu_page(
        'wp-job-board',
        __( 'All Jobs', 'wp-job-board' ),
        __( 'All Jobs', 'wp-job-board' ),
        'manage_options',
        'edit.php?post_type=jb_jobs'
    );

    // 3. Post a Job (Direct Link to Add New)
    add_submenu_page(
        'wp-job-board',
        __( 'Post a Job', 'wp-job-board' ),
        __( 'Post a Job', 'wp-job-board' ),
        'manage_options',
        'post-new.php?post_type=jb_jobs'
    );

    // 4. All Companies
    add_submenu_page(
        'wp-job-board',
        __( 'All Companies', 'wp-job-board' ),
        __( 'All Companies', 'wp-job-board' ),
        'manage_options',
        'edit.php?post_type=jb_companies'
    );

    // 5. Add a Company (Direct Link to Add New)
    add_submenu_page(
        'wp-job-board',
        __( 'Add a Company', 'wp-job-board' ),
        __( 'Add a Company', 'wp-job-board' ),
        'manage_options',
        'post-new.php?post_type=jb_companies'
    );

    // 6. Job Categories
    add_submenu_page(
        'wp-job-board',
        __( 'Job Categories', 'wp-job-board' ),
        __( 'Job Categories', 'wp-job-board' ),
        'manage_options',
        'edit-tags.php?taxonomy=jb_job_category&post_type=jb_jobs'
    );

    // 7. Job Types
     add_submenu_page(
        'wp-job-board',
        __( 'Job Types', 'wp-job-board' ),
        __( 'Job Types', 'wp-job-board' ),
        'manage_options',
        'edit-tags.php?taxonomy=jb_job_type&post_type=jb_jobs'
    );
     
    // 8. Applications - REMOVED per Pivot to External Links

    // 9. Settings (Registered in settings.php, but if we want strict order we can add here or ensure settings.php uses 'bjb-settings' correctly)
    // Note: settings.php uses `bjb_add_settings_page` on `admin_menu`.
    // We should either remove that action in settings.php and put it here, or rely on execution order.
    // To be safe, let's allow settings.php to register itself, as it's separate concern. 
    // However, the user wants strict order. Let's add it here to be sure.
     add_submenu_page(
		'wp-job-board',
		__( 'Settings', 'wp-job-board' ),
		__( 'Settings', 'wp-job-board' ),
		'manage_options',
		'bjb-settings',
		'bjb_render_settings_page'
	);
}
add_action( 'admin_menu', 'bjb_register_main_menu' );

// We need to remove the duplicate call in settings.php, OR just remove action there.
// I will edit settings.php to NOT add the menu page, only register settings.

/**
 * Main Page Callback (Dashboard)
 */
function bjb_main_page_callback() {
    // Basic stats
    $job_count = wp_count_posts('jb_jobs')->publish;
    $company_count = wp_count_posts('jb_companies')->publish;

    ?>
    <div class="wrap bjb-admin-wrap">
        <h1><?php _e( 'Job Board Dashboard', 'wp-job-board' ); ?></h1>
        
        <div class="bjb-dashboard-widgets" style="display:flex; gap:20px; margin-top:20px;">
            <div class="bjb-card" style="background:#fff; padding:20px; border-radius:5px; border:1px solid #ddd; flex:1;">
                <h3 style="margin-top:0;">Active Jobs</h3>
                <p style="font-size:2em; margin:0;"><?php echo $job_count; ?></p>
            </div>
            <div class="bjb-card" style="background:#fff; padding:20px; border-radius:5px; border:1px solid #ddd; flex:1;">
                <h3 style="margin-top:0;">Companies</h3>
                <p style="font-size:2em; margin:0;"><?php echo $company_count; ?></p>
            </div>
        </div>

        <div class="bjb-welcome" style="margin-top:30px;">
            <h2><?php _e( 'Quick Links', 'wp-job-board' ); ?></h2>
            <p>
                <a href="admin.php?page=bjb-settings" class="button button-primary">Settings</a>
                <a href="post-new.php?post_type=jb_jobs" class="button">Post a Job</a>
            </p>
        </div>
    </div>
    <?php
}
