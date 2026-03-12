<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_bjb_submit_application', 'bjb_handle_job_application' );
add_action( 'wp_ajax_nopriv_bjb_submit_application', 'bjb_handle_job_application' );

function bjb_handle_job_application() {
    // Verify Nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bjb_apply_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
    }

    // Validate inputs
    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $name = isset($_POST['candidate_name']) ? sanitize_text_field($_POST['candidate_name']) : '';
    $email = isset($_POST['candidate_email']) ? sanitize_email($_POST['candidate_email']) : '';
    $message = isset($_POST['candidate_message']) ? sanitize_textarea_field($_POST['candidate_message']) : '';

    if ( ! $job_id || ! $name || ! $email ) {
        wp_send_json_error( array( 'message' => 'Please fill in all required fields.' ) );
    }

    // Create Application CPT
    $post_data = array(
        'post_title'    => $name . ' - ' . get_the_title($job_id),
        'post_status'   => 'publish', // Or 'pending'
        'post_type'     => 'jb_application',
        'post_content'  => $message,
    );

    $application_id = wp_insert_post( $post_data );

    if ( ! is_wp_error( $application_id ) ) {
        // Save Meta
        update_post_meta( $application_id, '_jb_job_id', $job_id );
        update_post_meta( $application_id, '_jb_candidate_email', $email );
        update_post_meta( $application_id, '_jb_application_status', 'new' );

        wp_send_json_success( array( 'message' => 'Application submitted successfully!' ) );
    } else {
        wp_send_json_error( array( 'message' => 'Could not save application.' ) );
    }
}

/**
 * AJAX Handler for Filtering Jobs
 */
add_action( 'wp_ajax_bjb_filter_jobs', 'bjb_ajax_filter_jobs' );
add_action( 'wp_ajax_nopriv_bjb_filter_jobs', 'bjb_ajax_filter_jobs' );

function bjb_ajax_filter_jobs() {
    // 1. Build Query Args
    $args = array(
        'post_type'      => 'jb_jobs',
        'post_status'    => 'publish',
        'posts_per_page' => 10, // Can be dynamic if needed
    );

    $tax_query = array();

    // Job Type
    if ( ! empty( $_POST['job_type'] ) ) {
        $tax_query[] = array(
            'taxonomy' => 'jb_job_type',
            'field'    => 'slug',
            'terms'    => sanitize_text_field( $_POST['job_type'] ),
        );
    }

    // Category
    if ( ! empty( $_POST['job_category'] ) ) {
        $tax_query[] = array(
            'taxonomy' => 'jb_job_category',
            'field'    => 'slug',
            'terms'    => sanitize_text_field( $_POST['job_category'] ),
        );
    }

    if ( count( $tax_query ) > 0 ) {
        if ( count( $tax_query ) > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        $args['tax_query'] = $tax_query;
    }

    // Keywords (Search Title/Content)
    if ( ! empty( $_POST['keywords'] ) ) {
        $args['s'] = sanitize_text_field( $_POST['keywords'] );
    }
    
    // Meta Query (Location & Remote)
    $meta_query = array();

    // Location
    if ( ! empty( $_POST['location'] ) ) {
        $search_loc = sanitize_text_field( $_POST['location'] );
        
        // 1. Find Companies matching location
        $company_ids = get_posts( array(
            'post_type' => 'jb_companies',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key'     => '_bjb_company_location',
                    'value'   => $search_loc,
                    'compare' => 'LIKE'
                )
            )
        ) );
        
        // 2. Build Query: Match Job Location OR (Company ID IN matched companies)
        $location_query = array( 'relation' => 'OR' );
        
        // Check Job's specific location
        $location_query[] = array(
             'key'     => '_bjb_job_location',
             'value'   => $search_loc,
             'compare' => 'LIKE' 
        );
        
        // Check Linked Company
        if ( ! empty( $company_ids ) ) {
            $location_query[] = array(
                'key'     => '_bjb_job_company_id',
                'value'   => $company_ids,
                'compare' => 'IN'
            );
        }
        
        $meta_query[] = $location_query;
    }

    // Remote
    if ( ! empty( $_POST['remote'] ) && $_POST['remote'] == '1' ) {
         // Assuming remote is stored or we fallback to search. 
         // Since we don't have a specific remote meta yet, I'll fallback to adding "Remote" to the search query 
         // or if we had a meta:
         /*
         $meta_query[] = array(
             'key' => '_bjb_job_remote',
             'value' => '1',
             'compare' => '='
         );
         */
         // For now, if keywords is empty, set s to Remote, else append? 
         // Actually, let's assume the user puts "Remote" in the location or keywords. 
         // But for the checkbox, let's try a loose search if no meta exists.
         if ( empty( $args['s'] ) ) {
             $args['s'] = 'Remote';
         } else {
             $args['s'] .= ' Remote';
         }
    }

    if ( count( $meta_query ) > 0 ) {
        if ( count( $meta_query ) > 1 ) {
            $meta_query['relation'] = 'AND';
        }
        $args['meta_query'] = $meta_query;
    }

    // Pagination
    $paged = ( isset( $_POST['paged'] ) ) ? absint( $_POST['paged'] ) : 1;
    $args['paged'] = $paged;

    $query = new WP_Query( $args );

    ob_start();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            
            // --- REUSE LOOP CODE (Should ideally be a template part) ---
			$job_id = get_the_ID();
			$salary = get_post_meta( $job_id, '_bjb_job_salary_min', true );
            $salary_max = get_post_meta( $job_id, '_bjb_job_salary_max', true );
            if ($salary && $salary_max) $salary .= ' - ' . $salary_max;
            
			$company_id = get_post_meta( $job_id, '_bjb_job_company_id', true );
			$company_name = $company_id ? get_the_title( $company_id ) : '';
            $company_logo = $company_id ? get_the_post_thumbnail_url( $company_id, 'thumbnail' ) : '';
			$location = $company_id ? get_post_meta( $company_id, '_bjb_company_location', true ) : '';
			$types = get_the_terms( $job_id, 'jb_job_type' );
			$type_name = $types ? $types[0]->name : '';
            $time_ago = human_time_diff( get_the_time('U'), current_time('timestamp') ) . ' ago';
			
			?>
			<div class="bjb-job-item">
                <?php if ($company_logo) : ?>
                    <div class="bjb-item-logo">
                        <img src="<?php echo esc_url($company_logo); ?>" alt="<?php echo esc_attr($company_name); ?>">
                    </div>
                <?php else: ?>
                     <div class="bjb-item-logo-placeholder">
                        <?php echo substr($company_name, 0, 1); ?>
                    </div>
                <?php endif; ?>
                
				<div class="bjb-job-info">
					<h3 class="bjb-job-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <div class="bjb-company-name"><?php echo esc_html($company_name); ?></div>
					<div class="bjb-job-meta">
						<?php if ( $location ) : ?>
							<span class="bjb-meta-item"><span class="fas fa-map-marker-alt"></span> <?php echo esc_html( $location ); ?></span>
						<?php endif; ?>
                        <?php if ( $type_name ) : ?>
							<span class="bjb-meta-item"><span class="fas fa-briefcase"></span> <?php echo esc_html( $type_name ); ?></span>
						<?php endif; ?>
                         <?php if ( $salary ) : ?>
                            <span class="bjb-meta-item"><span class="fas fa-dollar-sign"></span> <?php echo esc_html( $salary ); ?></span>
                        <?php endif; ?>
                         <span class="bjb-meta-item"><span class="far fa-clock"></span> <?php echo esc_html( $time_ago ); ?></span>
					</div>
				</div>
				<div class="bjb-job-actions">
					<a href="<?php the_permalink(); ?>" class="button bjb-btn bjb-view-btn"><?php _e( 'View Details', 'wp-job-board' ); ?></a>
				</div>
			</div>
			<?php
            // --- END LOOP ---
        }
        wp_reset_postdata();
    } else {
        if ( $paged === 1 ) {
            echo '<p>' . __( 'No jobs found matching your criteria.', 'wp-job-board' ) . '</p>';
        }
    }

    $html = ob_get_clean();
    
    wp_send_json_success( array( 
        'html' => $html,
        'max_pages' => $query->max_num_pages,
        'found_posts' => $query->found_posts
    ) );
}
