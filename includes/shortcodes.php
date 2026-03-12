<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode to display jobs: [jb_jobs]
 */
function bjb_jobs_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'posts_per_page' => 10,
	), $atts, 'jb_jobs' );

	$args = array(
		'post_type'      => 'jb_jobs',
		'posts_per_page' => $atts['posts_per_page'],
		'post_status'    => 'publish',
	);

	$query = new WP_Query( $args );

	ob_start();
    
    // Wrapper
    echo '<div class="bjb-wrapper">';
    
    // --- Search Header ---
    echo '<div class="bjb-search-header">';
    echo '<h2>' . __( 'Find Your Dream Job', 'wp-job-board' ) . '</h2>';
    echo '<form id="bjb-search-form" class="bjb-search-form">';
    
    // Keyword Input
    echo '<div class="bjb-input-group icon-left">';
    echo '<span class="fas fa-search"></span>';
    echo '<input type="text" id="bjb-search-keywords" placeholder="' . __( 'Job title, keywords, or company', 'wp-job-board' ) . '">';
    echo '</div>';
    
    // Location Input
    echo '<div class="bjb-input-group icon-left">';
    echo '<span class="fas fa-map-marker-alt"></span>';
    echo '<input type="text" id="bjb-search-location" placeholder="' . __( 'City, state, or country', 'wp-job-board' ) . '">';
    echo '</div>';

    // Search Button
    echo '<button type="submit" class="bjb-search-btn">' . __( 'SEARCH JOBS', 'wp-job-board' ) . '</button>';
    
    echo '</form>';
    echo '</div>'; // .bjb-search-header

    // Main Container
    echo '<div class="bjb-job-board-container">';

    // --- Sidebar (Left) ---
    echo '<div class="bjb-sidebar">';
    echo '<div class="bjb-filter-card">';
    echo '<h3><span class="fas fa-filter"></span> ' . __( 'Filters', 'wp-job-board' ) . '</h3>';
    
    // Job Type Filter
    echo '<div class="bjb-filter-group">';
    echo '<label>' . __( 'Job Type', 'wp-job-board' ) . '</label>';
    echo '<select id="bjb-filter-type" class="bjb-select">';
    echo '<option value="">' . __( 'All types', 'wp-job-board' ) . '</option>';
    $types = get_terms( array( 'taxonomy' => 'jb_job_type', 'hide_empty' => false ) );
    if ( ! is_wp_error( $types ) && ! empty( $types ) ) {
        foreach ( $types as $term ) {
            echo '<option value="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</option>';
        }
    }
    echo '</select>';
    echo '</div>';

    // Category Filter
    echo '<div class="bjb-filter-group">';
    echo '<label>' . __( 'Category', 'wp-job-board' ) . '</label>';
    echo '<select id="bjb-filter-category" class="bjb-select">';
    echo '<option value="">' . __( 'All categories', 'wp-job-board' ) . '</option>';
    $cats = get_terms( array( 'taxonomy' => 'jb_job_category', 'hide_empty' => false ) );
    if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) {
        foreach ( $cats as $term ) {
            echo '<option value="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</option>';
        }
    }
    echo '</select>';
    echo '</div>';

    // Remote Checkbox
    echo '<div class="bjb-filter-group bjb-checkbox-group">';
    echo '<label class="bjb-checkbox-label">';
    echo '<input type="checkbox" id="bjb-filter-remote" value="1">';
    echo '<span>' . __( 'Remote positions only', 'wp-job-board' ) . '</span>';
    echo '</label>';
    echo '</div>';

    echo '</div>'; // .bjb-filter-card
    echo '</div>'; // .bjb-sidebar

    // --- Job List (Right) ---
    echo '<div class="bjb-jobs-column">';
	if ( $query->have_posts() ) {
		echo '<div class="bjb-job-list" id="bjb-results-container">';
		while ( $query->have_posts() ) {
			$query->the_post();
			// Include the loop part here or separate function? 
            // For cleaner code, let's keep it inline for now but matching the exact structure.
            
			$job_id = get_the_ID();
			$salary = get_post_meta( $job_id, '_bjb_job_salary_min', true ); // Updated to match single-job
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
		}
		echo '</div>'; // #bjb-results-container
		wp_reset_postdata();
	} else {
		echo '<div id="bjb-results-container"><p>' . __( 'No jobs found.', 'wp-job-board' ) . '</p></div>';
	}
    echo '</div>'; // .bjb-jobs-column
    echo '</div>'; // .bjb-job-board-container
    echo '</div>'; // .bjb-wrapper

	return ob_get_clean();
}
add_shortcode( 'jb_jobs', 'bjb_jobs_shortcode' );
