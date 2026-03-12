<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BJB_Elementor_Job_Listing_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'bjb_job_listing';
	}

	public function get_title() {
		return esc_html__( 'Job Listing Premium', 'wp-job-board' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return [ 'wp-job-board' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Content', 'wp-job-board' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__( 'Jobs Per Page', 'wp-job-board' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 50,
				'step' => 1,
				'default' => 6,
			]
		);
        
        $this->add_control(
			'show_search',
			[
				'label'     => esc_html__( 'Show Search Bar', 'wp-job-board' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        
        // Wrapper matches Shortcode for consistent CSS
        echo '<div class="bjb-wrapper">';

        // --- 1. SEARCH SECTION ---
        if ( 'yes' === $settings['show_search'] ) {
            ?>
            <div class="bjb-search-header">
                <h2><?php _e('Find Your Dream Job', 'wp-job-board'); ?></h2>
                <form id="bjb-search-form" class="bjb-search-form" action="" method="GET">
                    <div class="bjb-input-group icon-left">
                        <span class="fas fa-search"></span>
                        <input type="text" id="bjb-search-keywords" name="keyword" placeholder="<?php _e('Job title, keywords, or company', 'wp-job-board'); ?>" value="<?php echo isset($_GET['keyword']) ? esc_attr($_GET['keyword']) : ''; ?>">
                    </div>
                    <div class="bjb-input-group icon-left">
                        <span class="fas fa-map-marker-alt"></span>
                        <input type="text" id="bjb-search-location" name="location" placeholder="<?php _e('City, state, or country', 'wp-job-board'); ?>" value="<?php echo isset($_GET['location']) ? esc_attr($_GET['location']) : ''; ?>">
                    </div>
                    <button type="submit" class="bjb-search-btn"><?php _e('Search Jobs', 'wp-job-board'); ?></button>
                </form>
            </div>
            <?php
        }

        // --- 2. MAIN CONTAINER (Sidebar + Jobs) ---
        echo '<div class="bjb-job-board-container">';

        // --- SIDEBAR ---
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

        // Location Filter (Sidebar)
        echo '<div class="bjb-filter-group">';
        echo '<label>' . __( 'Location', 'wp-job-board' ) . '</label>';
        echo '<select id="bjb-filter-location-sidebar" class="bjb-select">';
        echo '<option value="">' . __( 'All locations', 'wp-job-board' ) . '</option>';
        
        $locations = $this->get_unique_locations();
        if ( ! empty( $locations ) ) {
            foreach ( $locations as $loc ) {
                echo '<option value="' . esc_attr( $loc ) . '">' . esc_html( $loc ) . '</option>';
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

        // --- JOBS COLUMN ---
        echo '<div class="bjb-jobs-column">';

        // Initial Query
        $args = array(
            'post_type'      => 'jb_jobs',
            'posts_per_page' => $settings['posts_per_page'],
            'post_status'    => 'publish',
        );
        
        // Fallback for simple GET search if JS fails
        if ( !empty($_GET['keyword']) ) {
            $args['s'] = sanitize_text_field($_GET['keyword']);
        }
        
        $jobs = new WP_Query( $args );
        
        if ( $jobs->have_posts() ) {
            // Target container for AJAX
            echo '<div class="bjb-job-list" id="bjb-results-container">';
            
            while ( $jobs->have_posts() ) {
                $jobs->the_post();
                
                // DATA PREP
                $job_id = get_the_ID();
                $company_id   = get_post_meta( $job_id, '_bjb_job_company_id', true );
                $salary_min   = get_post_meta( $job_id, '_bjb_job_salary_min', true );
                $salary_max   = get_post_meta( $job_id, '_bjb_job_salary_max', true );
                if ($salary_min && $salary_max) $salary = $salary_min . ' - ' . $salary_max;
                else $salary = $salary_min;
                
                $company_name = $company_id ? get_the_title( $company_id ) : 'Confidential';
                $company_logo = $company_id ? get_the_post_thumbnail_url( $company_id, 'thumbnail' ) : '';
                
                 // Meta
                $location = get_post_meta( $job_id, '_bjb_job_location', true );
                if ( ! $location && $company_id ) {
                    $location = get_post_meta( $company_id, '_bjb_company_location', true );
                }
                
                $types = get_the_terms( $job_id, 'jb_job_type' );
                $type_name = $types && !is_wp_error($types) ? $types[0]->name : '';
                $time_ago = human_time_diff( get_the_time('U'), current_time('timestamp') ) . ' ago';
                
                // RENDER ITEM (Matches AJAX Loop / Shortcode Loop)
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

            // --- LOAD MORE BUTTON (Initial State) ---
            $max_pages = $jobs->max_num_pages;
            
            // Pass max pages to JS via hidden input
            echo '<input type="hidden" id="bjb-initial-max-pages" value="' . esc_attr($max_pages) . '">';

            if ( $max_pages > 1 ) {
                echo '<div class="bjb-load-more-container" style="text-align:center; margin-top:20px;">';
                echo '<button id="bjb-load-more-btn" class="button bjb-btn">' . __('View More Jobs', 'wp-job-board') . '</button>';
                echo '</div>';
            }

            wp_reset_postdata();
        } else {
             echo '<div id="bjb-results-container"><p>' . __('No jobs found.', 'wp-job-board') . '</p></div>';
        }
        
        echo '</div>'; // .bjb-jobs-column
        echo '</div>'; // .bjb-job-board-container
        echo '</div>'; // .bjb-wrapper
	}

    /**
     * Helper to get distinct locations from Job Meta.
     */
    private function get_unique_locations() {
        global $wpdb;
        
        // Query Distinct Job Locations only from PUBLISHED jobs
        $sql = "
            SELECT DISTINCT pm.meta_value 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_bjb_job_location' 
            AND pm.meta_value != ''
            AND p.post_type = 'jb_jobs'
            AND p.post_status = 'publish'
            ORDER BY pm.meta_value ASC
        ";
        
        $results = $wpdb->get_col( $sql );
        
        if ( ! $results ) {
            return array();
        }
        
        return $results;
    }
}
