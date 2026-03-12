<?php
/**
 * Single Job Template
 * 
 * This file renders the content of a single job. 
 * Depending on the theme integration, this might need to include get_header() and get_footer().
 */

get_header();

$job_id = get_the_ID();
$salary_min = get_post_meta( $job_id, '_bjb_job_salary_min', true );
$salary_max = get_post_meta( $job_id, '_bjb_job_salary_max', true );
$salary_currency = get_post_meta( $job_id, '_bjb_job_currency', true ) ?: '$';

$salary = '';
if ( $salary_min ) {
    $salary = $salary_currency . $salary_min;
    if ( $salary_max ) {
        $salary .= ' - ' . $salary_currency . $salary_max;
    }
}

$company_id = get_post_meta( $job_id, '_bjb_job_company_id', true );
$company_name = $company_id ? get_the_title( $company_id ) : '';
$location = $company_id ? get_post_meta( $company_id, '_bjb_company_location', true ) : '';
$website = $company_id ? get_post_meta( $company_id, '_bjb_company_website', true ) : '';
$expiry = get_post_meta( $job_id, '_bjb_job_expiry_date', true );
$application_email = get_post_meta( $job_id, '_bjb_job_application_email', true );

while ( have_posts() ) : the_post();
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'bjb-single-job' ); ?>>
            <div class="bjb-single-container">
                <!-- Header (Title + Company) -->
                <div class="bjb-single-header">
                    <?php if ( $company_id ) : 
                        $logo = get_the_post_thumbnail_url( $company_id, 'thumbnail' );
                         if ($logo) : ?>
                            <div class="bjb-single-logo">
                                <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr( $company_name ); ?>">
                            </div>
                        <?php else: 
                             $letter = substr($company_name, 0, 1);
                        ?>
                             <div class="bjb-single-logo-placeholder"><?php echo esc_html($letter); ?></div>
                        <?php endif; 
                    endif; ?>
                    
                    <div class="bjb-header-content">
                        <?php the_title( '<h1 class="entry-title bjb-main-title">', '</h1>' ); ?>
                        
                        <div class="bjb-header-meta">
                            <?php if ( $company_name ) : ?>
                                <span class="bjb-h-meta"><span class="far fa-building"></span> <?php echo esc_html( $company_name ); ?></span>
                            <?php endif; ?>
                            
                             <span class="bjb-h-meta"><span class="far fa-calendar-alt"></span> <?php echo human_time_diff( get_the_time('U'), current_time('timestamp') ) . ' ago'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="bjb-single-body">
                    <!-- Left Column: Description + Requirements -->
                    <div class="bjb-content-column">
                        <div class="bjb-section bjb-description">
                            <h3><?php _e( 'Job Description', 'wp-job-board' ); ?></h3>
                            <?php 
                                $description = get_post_meta( get_the_ID(), '_bjb_job_description', true );
                                echo wpautop( wp_kses_post( $description ) ); 
                            ?>
                        </div>

                         <?php 
                        $res = get_post_meta( get_the_ID(), '_bjb_job_responsibilities', true );
                        if ($res) : ?>
                        <div class="bjb-section bjb-responsibilities">
                            <h3><?php _e( 'Key Responsibilities', 'wp-job-board' ); ?></h3>
                            <?php echo wpautop( wp_kses_post( $res ) ); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php 
                        $req = get_post_meta( get_the_ID(), '_bjb_job_requirements', true );
                        if ($req) : ?>
                        <div class="bjb-section bjb-requirements">
                            <h3><?php _e( 'Requirements', 'wp-job-board' ); ?></h3>
                            <?php echo wpautop( wp_kses_post( $req ) ); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php 
                        $ben = get_post_meta( get_the_ID(), '_bjb_job_benefits', true );
                        if ($ben) : ?>
                        <div class="bjb-section bjb-benefits">
                            <h3><?php _e( 'Benefits', 'wp-job-board' ); ?></h3>
                            <?php echo wpautop( wp_kses_post( $ben ) ); ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right Column: Sidebar (Overview, Salary, Apply) -->
                    <div class="bjb-sidebar-column">
                        <div class="bjb-job-overview-card">
                            <h3><?php _e('Job Overview', 'wp-job-board'); ?></h3>
                            
                            <ul class="bjb-overview-list">
                                <?php 
                                $location = get_post_meta( $job_id, '_bjb_job_location', true );
                                if (!$location && $company_id) $location = get_post_meta($company_id, '_bjb_company_location', true); // Fallback
                                ?>
                                
                                <?php if ($location) : ?>
                                    <li>
                                        <div class="icon"><span class="fas fa-map-marker-alt"></span></div>
                                        <div class="details">
                                            <strong><?php _e('Location', 'wp-job-board'); ?></strong>
                                            <span><?php echo esc_html($location); ?></span>
                                        </div>
                                    </li>
                                <?php endif; ?>

                                <?php 
                                $types = get_the_terms( get_the_ID(), 'jb_job_type' );
                                if ( $types && ! is_wp_error( $types ) ) : 
                                    $type_name = $types[0]->name;
                                ?>
                                    <li>
                                        <div class="icon"><span class="fas fa-briefcase"></span></div>
                                        <div class="details">
                                            <strong><?php _e('Job Type', 'wp-job-board'); ?></strong>
                                            <span><?php echo esc_html($type_name); ?></span>
                                        </div>
                                    </li>
                                <?php endif; ?>

                                <?php 
                                $experience = get_post_meta( get_the_ID(), '_bjb_job_experience', true );
                                if ( $experience ) : 
                                ?>
                                    <li>
                                        <div class="icon"><span class="fas fa-history"></span></div>
                                        <div class="details">
                                            <strong><?php _e('Experience', 'wp-job-board'); ?></strong>
                                            <span><?php echo esc_html($experience); ?></span>
                                        </div>
                                    </li>
                                <?php endif; ?>

                                <?php if ($salary) : ?>
                                    <li>
                                        <div class="icon"><span class="fas fa-dollar-sign"></span></div>
                                        <div class="details">
                                            <strong><?php _e('Salary', 'wp-job-board'); ?></strong>
                                            <span><?php echo esc_html($salary); ?></span>
                                        </div>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($expiry) : ?>
                                    <li>
                                        <div class="icon"><span class="fas fa-hourglass-half"></span></div>
                                        <div class="details">
                                            <strong><?php _e('Deadline', 'wp-job-board'); ?></strong>
                                            <span><?php echo date_i18n( get_option( 'date_format' ), strtotime( $expiry ) ); ?></span>
                                        </div>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($website) : ?>
                                     <li>
                                        <div class="icon"><span class="fas fa-external-link-alt"></span></div>
                                        <div class="details">
                                            <strong><?php _e('Website', 'wp-job-board'); ?></strong>
                                            <a href="<?php echo esc_url( $website ); ?>" target="_blank"><?php _e('Visit Site', 'wp-job-board'); ?></a>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>

                            <div class="bjb-apply-section">
                                <?php 
                                $apply_url = get_post_meta( get_the_ID(), '_bjb_job_apply_url', true ); 
                                if ( $apply_url ) :
                                ?>
                                    <a href="<?php echo esc_url( $apply_url ); ?>" class="button bjb-btn bjb-block-btn" target="_blank" rel="noopener noreferrer"><?php _e( 'Apply Now', 'wp-job-board' ); ?> <span class="fas fa-arrow-right"></span></a>
                                <?php else : ?>
                                    <p><em><?php _e( 'Application details unavailable.', 'wp-job-board' ); ?></em></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                         <!-- Share / Company Mini Profile could go here -->
                    </div>
                </div>
            </div>

        </article>
    </main>
</div>
<?php
endwhile;

get_footer();
