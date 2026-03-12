<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bjb_register_companies_cpt() {
	$labels = array(
		'name'                  => _x( 'Companies', 'Post Type General Name', 'wp-job-board' ),
		'singular_name'         => _x( 'Company', 'Post Type Singular Name', 'wp-job-board' ),
		'menu_name'             => __( 'Companies', 'wp-job-board' ),
		'name_admin_bar'        => __( 'Company', 'wp-job-board' ),
		'archives'              => __( 'Company Archives', 'wp-job-board' ),
		'attributes'            => __( 'Company Attributes', 'wp-job-board' ),
		'parent_item_colon'     => __( 'Parent Company:', 'wp-job-board' ),
		'all_items'             => __( 'All Companies', 'wp-job-board' ),
		'add_new_item'          => __( 'Add a Company', 'wp-job-board' ),
		'add_new'               => __( 'Add a Company', 'wp-job-board' ),
		'new_item'              => __( 'New Company', 'wp-job-board' ),
		'edit_item'             => __( 'Edit Company', 'wp-job-board' ),
		'update_item'           => __( 'Update Company', 'wp-job-board' ),
		'view_item'             => __( 'View Company', 'wp-job-board' ),
		'view_items'            => __( 'View Companies', 'wp-job-board' ),
		'search_items'          => __( 'Search Company', 'wp-job-board' ),
		'not_found'             => __( 'Not found', 'wp-job-board' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'wp-job-board' ),
		'featured_image'        => __( 'Company Logo', 'wp-job-board' ),
		'set_featured_image'    => __( 'Set company logo', 'wp-job-board' ),
		'remove_featured_image' => __( 'Remove company logo', 'wp-job-board' ),
		'use_featured_image'    => __( 'Use as company logo', 'wp-job-board' ),
		'items_list'            => __( 'Companies list', 'wp-job-board' ),
		'items_list_navigation' => __( 'Companies list navigation', 'wp-job-board' ),
		'filter_items_list'     => __( 'Filter companies list', 'wp-job-board' ),
	);
	$args = array(
		'label'                 => __( 'Company', 'wp-job-board' ),
		'description'           => __( 'Post Type for Companies', 'wp-job-board' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'thumbnail', 'excerpt' ), // Removed 'editor'
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => false, // Manual Control
		'menu_position'         => 10,
		'menu_icon'             => 'dashicons-building',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'show_in_rest'          => true,
	);
	register_post_type( 'jb_companies', $args );
}
add_action( 'init', 'bjb_register_companies_cpt' );
