<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bjb_register_jobs_cpt() {
	$labels = array(
		'name'                  => _x( 'Jobs', 'Post Type General Name', 'wp-job-board' ),
		'singular_name'         => _x( 'Job', 'Post Type Singular Name', 'wp-job-board' ),
		'menu_name'             => __( 'Jobs', 'wp-job-board' ),
		'name_admin_bar'        => __( 'Job', 'wp-job-board' ),
		'archives'              => __( 'Job Archives', 'wp-job-board' ),
		'attributes'            => __( 'Job Attributes', 'wp-job-board' ),
		'parent_item_colon'     => __( 'Parent Job:', 'wp-job-board' ),
		'all_items'             => __( 'All Jobs', 'wp-job-board' ),
		'add_new_item'          => __( 'Post a Job', 'wp-job-board' ),
		'add_new'               => __( 'Post a Job', 'wp-job-board' ),
		'new_item'              => __( 'New Job', 'wp-job-board' ),
		'edit_item'             => __( 'Edit Job', 'wp-job-board' ),
		'update_item'           => __( 'Update Job', 'wp-job-board' ),
		'view_item'             => __( 'View Job', 'wp-job-board' ),
		'view_items'            => __( 'View Jobs', 'wp-job-board' ),
		'search_items'          => __( 'Search Job', 'wp-job-board' ),
		'not_found'             => __( 'Not found', 'wp-job-board' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'wp-job-board' ),
		'featured_image'        => __( 'Featured Image', 'wp-job-board' ),
		'set_featured_image'    => __( 'Set featured image', 'wp-job-board' ),
		'remove_featured_image' => __( 'Remove featured image', 'wp-job-board' ),
		'use_featured_image'    => __( 'Use as featured image', 'wp-job-board' ),
		'insert_into_item'      => __( 'Insert into job', 'wp-job-board' ),
		'uploaded_to_this_item' => __( 'Uploaded to this job', 'wp-job-board' ),
		'items_list'            => __( 'Jobs list', 'wp-job-board' ),
		'items_list_navigation' => __( 'Jobs list navigation', 'wp-job-board' ),
		'filter_items_list'     => __( 'Filter jobs list', 'wp-job-board' ),
	);
	$args = array(
		'label'                 => __( 'Job', 'wp-job-board' ),
		'description'           => __( 'Post Type for Job Listings', 'wp-job-board' ),
		'labels'                => $labels,
		'labels'                => $labels,
		'supports'              => array( 'title', 'thumbnail', 'excerpt' ), // Removed 'editor'
		'taxonomies'            => array( 'jb_job_category', 'jb_job_type' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => false, // Parent slug
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-businessman',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'show_in_rest'          => true, // Block editor support
        'rewrite'               => array( 'slug' => 'careers', 'with_front' => false ),
	);
	register_post_type( 'jb_jobs', $args );

	// Register Taxonomies
	bjb_register_taxonomies();
}
add_action( 'init', 'bjb_register_jobs_cpt' );

function bjb_register_taxonomies() {
	// Job Category
	register_taxonomy( 'jb_job_category', array( 'jb_jobs' ), array(
		'hierarchical'      => true,
		'labels'            => array(
			'name'              => _x( 'Job Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Job Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Categories' ),
			'all_items'         => __( 'All Categories' ),
			'parent_item'       => __( 'Parent Category' ),
			'parent_item_colon' => __( 'Parent Category:' ),
			'edit_item'         => __( 'Edit Category' ),
			'update_item'       => __( 'Update Category' ),
			'add_new_item'      => __( 'Add New Category' ),
			'new_item_name'     => __( 'New Category Name' ),
			'menu_name'         => __( 'Job Categories' ),
		),
		'show_ui'           => true,
		'show_admin_column' => true,
        'show_in_menu'      => false, // Manual Control
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'job-category' ),
		'show_in_rest'      => true,
	) );

	// Job Type
	register_taxonomy( 'jb_job_type', array( 'jb_jobs' ), array(
		'hierarchical'      => true,
		'labels'            => array(
			'name'              => _x( 'Job Types', 'taxonomy general name' ),
			'singular_name'     => _x( 'Job Type', 'taxonomy singular name' ),
			'menu_name'         => __( 'Job Types' ),
		),
		'show_ui'           => true,
		'show_admin_column' => true,
        'show_in_menu'      => false, // Manual Control
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'job-type' ),
		'show_in_rest'      => true,
	) );
}
