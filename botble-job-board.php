<?php
/**
 * Plugin Name: Job Board
 * Plugin URI: https://example.com/wp-job-board
 * Description: A powerful Job Board plugin.
 * Version: 1.0.0
 * Author: Inumaki
 * Author URI: https://www.linkedin.com/in/mhassanraza117
 * License: GPL-2.0+
 * Text Domain: wp-job-board
 * Update URI: false
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Plugin Path
define( 'BJB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BJB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Prevent auto-updates by removing this plugin from WP update checks
add_filter( 'site_transient_update_plugins', function( $value ) {
    $plugin_file = plugin_basename( __FILE__ );
    if ( isset( $value->response[ $plugin_file ] ) ) {
        unset( $value->response[ $plugin_file ] );
    }
    return $value;
} );

// Include Core Files
require_once BJB_PLUGIN_DIR . 'includes/admin-menu.php';
require_once BJB_PLUGIN_DIR . 'includes/settings.php';
require_once BJB_PLUGIN_DIR . 'includes/cpt-jobs.php';
require_once BJB_PLUGIN_DIR . 'includes/cpt-companies.php';
require_once BJB_PLUGIN_DIR . 'includes/cpt-applications.php';
require_once BJB_PLUGIN_DIR . 'includes/ajax-functions.php';
require_once BJB_PLUGIN_DIR . 'includes/admin-meta.php';
require_once BJB_PLUGIN_DIR . 'includes/shortcodes.php';
require_once BJB_PLUGIN_DIR . 'includes/shortcodes-dashboards.php';
require_once BJB_PLUGIN_DIR . 'includes/elementor/class-bjb-elementor.php';

// Enqueue Scripts and Styles
function bjb_enqueue_scripts() {
	wp_enqueue_style( 'bjb-style', BJB_PLUGIN_URL . 'assets/css/style.css', array(), '1.0.0' );
    
    wp_enqueue_script( 'bjb-frontend', BJB_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), '1.0.0', true );
    wp_localize_script( 'bjb-frontend', 'bjb_vars', array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ) );
    
    // Font Awesome
    wp_enqueue_style( 'bjb-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4' );
}
add_action( 'wp_enqueue_scripts', 'bjb_enqueue_scripts' );

// Enqueue Admin Scripts and Styles
function bjb_enqueue_admin_scripts() {
	wp_enqueue_style( 'bjb-admin-style', BJB_PLUGIN_URL . 'assets/css/admin-style.css', array(), '1.0.0' );
}
add_action( 'admin_enqueue_scripts', 'bjb_enqueue_admin_scripts' );

// Load Single Template
function bjb_load_single_template( $template ) {
    if ( is_singular( 'jb_jobs' ) ) {
        $plugin_template = BJB_PLUGIN_DIR . 'templates/single-job.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'bjb_load_single_template' );

// Activation Hook
// Activation Hook
function bjb_activate_plugin() {
	// Trigger CPT registration
	bjb_register_jobs_cpt();
	bjb_register_companies_cpt();
	bjb_register_applications_cpt();
	
	// Flush rewrite rules
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bjb_activate_plugin' );

// TEMPORARY: Force flush on admin init to fix immediate user issue.
add_action( 'admin_init', function() {
    if ( ! get_option( 'bjb_flush_rewrites_v5' ) ) {
        flush_rewrite_rules();
        update_option( 'bjb_flush_rewrites_v5', true );
    }
});

/**
 * Debug Shortcode: [bjb_debug_rules]
 * Use this to check if rewrite rules exist and if CPT is registered.
 */
add_shortcode( 'bjb_debug_rules', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return 'You must be an admin to see this.';
    }

    global $wp_rewrite;
    $output = '<div style="background:#fff; border:2px solid red; padding:20px; color:#333; font-family:monospace; z-index:99999; position:relative;">';
    $output .= '<h3>BJB Debugger</h3>';

    // 1. Check CPT
    $cpt = get_post_type_object( 'jb_jobs' );
    $output .= '<p><strong>CPT "jb_jobs":</strong> ' . ( $cpt ? 'REGISTERED' : '<span style="color:red">MISSING</span>' ) . '</p>';
    if ( $cpt ) {
        $output .= '<p>CPT Rewrite Slug: ' . ( isset($cpt->rewrite['slug']) ? $cpt->rewrite['slug'] : 'Not set' ) . '</p>';
    }

    // 2. Check Conflicting Pages
    $p1 = get_page_by_path( 'jobs' );
    $p2 = get_page_by_path( 'job' );
    $output .= '<p><strong>Page "jobs":</strong> ' . ( $p1 ? 'EXISTS (ID: '.$p1->ID.')' : 'Not found' ) . '</p>';
    $output .= '<p><strong>Page "job":</strong> ' . ( $p2 ? 'EXISTS (ID: '.$p2->ID.')' : 'Not found' ) . '</p>';

    // 3. Check Rewrite Rules
    $rules = $wp_rewrite->wp_rewrite_rules();
    $found_rules = array();
    if ( $rules ) {
        foreach ( $rules as $pattern => $query ) {
            if ( strpos( $pattern, 'jobs' ) !== false ) {
                $found_rules[$pattern] = $query;
            }
        }
    }

    $output .= '<h4>Related Rewrite Rules:</h4>';
    if ( ! empty( $found_rules ) ) {
        $output .= '<ul>';
        foreach ( $found_rules as $pattern => $query ) {
            $output .= '<li><strong>' . esc_html( $pattern ) . '</strong> <br>=> ' . esc_html( $query ) . '</li>';
        }
        $output .= '</ul>';
    } else {
        $output .= '<p style="color:red">No rules found containing "jobs".</p>';
    }
    
    // 4. Flush Button (Optional helper link)
    $output .= '<p><em>If rules are missing, try visiting Settings > Permalinks and clicking "Save Changes".</em></p>';

    $output .= '</div>';
    return $output;
} );
