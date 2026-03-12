<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode: [jb_employer_dashboard]
 */
function bjb_employer_dashboard_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>' . __( 'Please login to access the employer dashboard.', 'wp-job-board' ) . '</p>';
    }
    
    $current_user = wp_get_current_user();
    
    // Logic to list Jobs posted by this user
    // Note: In real world, we need to associate jobs with users. 
    // For now, we assume admin is the only one or 'author' is the user.
    $args = array(
        'post_type' => 'jb_jobs',
        'author'    => $current_user->ID,
        'post_status' => array('publish', 'draft', 'pending'),
        'posts_per_page' => -1
    );
    $jobs = new WP_Query($args);
    
    ob_start();
    ?>
    <div class="bjb-dashboard">
        <h2><?php _e('Employer Dashboard', 'wp-job-board'); ?></h2>
        <div class="bjb-actions">
            <!-- Link to backend for now, or build sophisticated frontend form -->
            <a href="<?php echo admin_url('post-new.php?post_type=jb_jobs'); ?>" class="button bjb-btn"><?php _e('Post a New Job', 'wp-job-board'); ?></a>
        </div>
        
        <table class="bjb-table">
            <thead>
                <tr>
                    <th><?php _e('Job Title', 'wp-job-board'); ?></th>
                    <th><?php _e('Status', 'wp-job-board'); ?></th>
                    <th><?php _e('Date Posted', 'wp-job-board'); ?></th>
                    <th><?php _e('Applications', 'wp-job-board'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($jobs->have_posts()) : while ($jobs->have_posts()) : $jobs->the_post(); 
                    $app_count = 0; // Need query to count applications for this job
                ?>
                <tr>
                    <td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
                    <td><?php echo get_post_status_object(get_post_status())->label; ?></td>
                    <td><?php echo get_the_date(); ?></td>
                    <td><?php echo $app_count; // Placeholder ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="4"><?php _e('No jobs found.', 'wp-job-board'); ?></td></tr>
                <?php endif; wp_reset_postdata(); ?>
            </tbody>
        </table>
    </div>
    <style>
        .bjb-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .bjb-table th, .bjb-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .bjb-table th { background-color: #f2f2f2; }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('jb_employer_dashboard', 'bjb_employer_dashboard_shortcode');

/**
 * Shortcode: [jb_candidate_dashboard]
 */
function bjb_candidate_dashboard_shortcode() {
     if ( ! is_user_logged_in() ) {
        return '<p>' . __( 'Please login to access the candidate dashboard.', 'wp-job-board' ) . '</p>';
    }
    // Placeholder for candidate applications list
    // Need to query jb_application by meta key _jb_candidate_email matching user email
    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;
    
    $args = array(
        'post_type' => 'jb_application',
        'meta_query' => array(
            array(
                'key' => '_jb_candidate_email',
                'value' => $user_email,
                'compare' => '='
            )
        )
    );
    $apps = new WP_Query($args);
    
    ob_start();
    ?>
    <div class="bjb-dashboard">
        <h2><?php _e('Candidate Dashboard', 'wp-job-board'); ?></h2>
        <h3><?php _e('My Applications', 'wp-job-board'); ?></h3>
        <table class="bjb-table">
            <thead>
                <tr>
                    <th><?php _e('Job Title', 'wp-job-board'); ?></th>
                    <th><?php _e('Date Applied', 'wp-job-board'); ?></th>
                    <th><?php _e('Status', 'wp-job-board'); ?></th>
                </tr>
            </thead>
             <tbody>
                <?php if ($apps->have_posts()) : while ($apps->have_posts()) : $apps->the_post(); 
                    $job_id = get_post_meta(get_the_ID(), '_jb_job_id', true);
                    $status = get_post_meta(get_the_ID(), '_jb_application_status', true);
                ?>
                <tr>
                    <td><a href="<?php echo get_permalink($job_id); ?>"><?php echo get_the_title($job_id); ?></a></td>
                    <td><?php echo get_the_date(); ?></td>
                    <td><?php echo ucfirst($status ? $status : 'new'); ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="3"><?php _e('No applications found.', 'wp-job-board'); ?></td></tr>
                <?php endif; wp_reset_postdata(); ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('jb_candidate_dashboard', 'bjb_candidate_dashboard_shortcode');
