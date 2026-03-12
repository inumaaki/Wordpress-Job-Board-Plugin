<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bjb_register_applications_cpt() {
	$labels = array(
		'name'                  => _x( 'Applications', 'Post Type General Name', 'wp-job-board' ),
		'singular_name'         => _x( 'Application', 'Post Type Singular Name', 'wp-job-board' ),
		'menu_name'             => __( 'Applications', 'wp-job-board' ),
		'name_admin_bar'        => __( 'Application', 'wp-job-board' ),
		'archives'              => __( 'Application Archives', 'wp-job-board' ),
		'attributes'            => __( 'Application Attributes', 'wp-job-board' ),
		'parent_item_colon'     => __( 'Parent Application:', 'wp-job-board' ),
		'all_items'             => __( 'All Applications', 'wp-job-board' ),
		'add_new_item'          => __( 'New Application', 'wp-job-board' ),
		'add_new'               => __( 'New Application', 'wp-job-board' ),
		'new_item'              => __( 'New Application', 'wp-job-board' ),
		'edit_item'             => __( 'Edit Application', 'wp-job-board' ),
		'update_item'           => __( 'Update Application', 'wp-job-board' ),
		'view_item'             => __( 'View Application', 'wp-job-board' ),
		'view_items'            => __( 'View Applications', 'wp-job-board' ),
		'search_items'          => __( 'Search Application', 'wp-job-board' ),
		'not_found'             => __( 'No applications found', 'wp-job-board' ),
		'not_found_in_trash'    => __( 'No applications found in Trash', 'wp-job-board' ),
	);
	$args = array(
		'label'                 => __( 'Application', 'wp-job-board' ),
		'description'           => __( 'Job Applications', 'wp-job-board' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor' ), // Title = Candidate Name
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => false, // Manual Control
		'menu_position'         => 20,
		'menu_icon'             => 'dashicons-id-alt',
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'capability_type'       => 'post',
	);
	register_post_type( 'jb_application', $args );
}
add_action( 'init', 'bjb_register_applications_cpt' );

/**
 * Add Custom Columns for Applications
 */
function bjb_application_columns($columns) {
    unset($columns['date']); // Remove date, we'll add it back later if needed or rely on 'Submitted'
    $columns['job_title'] = __( 'Job Role', 'wp-job-board' );
    $columns['candidate_email'] = __( 'Email', 'wp-job-board' );
    $columns['status'] = __( 'Status', 'wp-job-board' );
    $columns['date'] = __( 'Date', 'wp-job-board' );
    return $columns;
}
add_filter('manage_jb_application_posts_columns', 'bjb_application_columns');

/**
 * Fill Custom Columns
 */
function bjb_application_custom_column($column, $post_id) {
    switch ($column) {
        case 'job_title':
            $job_id = get_post_meta($post_id, '_jb_job_id', true);
            if ($job_id) {
                echo '<a href="'.get_edit_post_link($job_id).'">'.get_the_title($job_id).'</a>';
            } else {
                echo '-';
            }
            break;
        case 'candidate_email':
            echo esc_html(get_post_meta($post_id, '_jb_candidate_email', true));
            break;
        case 'status':
            $status = get_post_meta($post_id, '_jb_application_status', true);
            echo $status ? '<span class="bjb-badge status-'.esc_attr($status).'">' . ucfirst($status) . '</span>' : 'New';
            break;
    }
}
add_action('manage_jb_application_posts_custom_column', 'bjb_application_custom_column', 10, 2);
