<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Meta Boxes for Jobs and Companies
 */
function bjb_register_meta_boxes() {
	// Job Meta Box
	add_meta_box(
		'bjb_job_details',
		__( 'Job Details', 'wp-job-board' ),
		'bjb_job_details_callback',
		'jb_jobs',
		'normal',
		'high'
	);

	// Company Meta Box
	add_meta_box(
		'bjb_company_details',
		__( 'Company Details', 'wp-job-board' ),
		'bjb_company_details_callback',
		'jb_companies',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'bjb_register_meta_boxes' );

/**
 * Job Details Meta Box Callback
 */
function bjb_job_details_callback( $post ) {
	wp_nonce_field( 'bjb_save_job_details', 'bjb_job_details_nonce' );

	// Retrieve values
	$company_id   = get_post_meta( $post->ID, '_bjb_job_company_id', true );
	$salary_min   = get_post_meta( $post->ID, '_bjb_job_salary_min', true );
	$salary_max   = get_post_meta( $post->ID, '_bjb_job_salary_max', true );
	$currency     = get_post_meta( $post->ID, '_bjb_job_currency', true );
	$experience   = get_post_meta( $post->ID, '_bjb_job_experience', true );
	$deadline     = get_post_meta( $post->ID, '_bjb_job_deadline', true );
	$is_featured  = get_post_meta( $post->ID, '_bjb_job_featured', true );
	$deadline     = get_post_meta( $post->ID, '_bjb_job_deadline', true );
	$is_featured  = get_post_meta( $post->ID, '_bjb_job_featured', true );
	$apply_url    = get_post_meta( $post->ID, '_bjb_job_apply_url', true );
    
    // Rich Text Fields (can use wp_editor if we want, but simple textareas often better for meta boxes to avoid conflict)
    $responsibilities = get_post_meta( $post->ID, '_bjb_job_responsibilities', true );
    $requirements     = get_post_meta( $post->ID, '_bjb_job_requirements', true );
    $benefits         = get_post_meta( $post->ID, '_bjb_job_benefits', true );


	// Get companies
	$companies = get_posts( array(
		'post_type'      => 'jb_companies',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'order'          => 'ASC',
	) );
    
    // Description
    $description = get_post_meta( $post->ID, '_bjb_job_description', true );

	?>
	<table class="form-table bjb-meta-table">
        <!-- Basic Info -->
        <tr>
            <th><label for="bjb_job_description"><?php _e('Job Description', 'wp-job-board'); ?></label></th>
            <td>
                <textarea name="bjb_job_description" id="bjb_job_description" rows="8" class="widefat" placeholder="<?php _e('Enter job description...', 'wp-job-board'); ?>"><?php echo esc_textarea( $description ); ?></textarea>
            </td>
        </tr>
		<tr>
			<th><label for="bjb_job_company_id"><?php _e( 'Company', 'wp-job-board' ); ?></label></th>
			<td>
				<select name="bjb_job_company_id" id="bjb_job_company_id" class="widefat">
					<option value=""><?php _e( '-- Select Company --', 'wp-job-board' ); ?></option>
					<?php foreach ( $companies as $company ) : ?>
						<option value="<?php echo esc_attr( $company->ID ); ?>" <?php selected( $company_id, $company->ID ); ?>>
							<?php echo esc_html( $company->post_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>

        <!-- Salary -->
		<tr>
			<th><label><?php _e( 'Salary Range', 'wp-job-board' ); ?></label></th>
			<td>
                <div style="display:flex; gap:10px;">
                    <input type="text" name="bjb_job_salary_min" placeholder="Min" value="<?php echo esc_attr( $salary_min ); ?>" class="regular-text" style="width:100px;">
                    <input type="text" name="bjb_job_salary_max" placeholder="Max" value="<?php echo esc_attr( $salary_max ); ?>" class="regular-text" style="width:100px;">
                    <input type="text" name="bjb_job_currency" placeholder="Currency (USD)" value="<?php echo esc_attr( $currency ); ?>" class="regular-text" style="width:100px;">
                </div>
			</td>
		</tr>

        <!-- Location -->
        <tr>
			<th><label for="bjb_job_location"><?php _e( 'Job Location', 'wp-job-board' ); ?></label></th>
			<td>
				<input type="text" name="bjb_job_location" id="bjb_job_location" value="<?php echo esc_attr( get_post_meta( $post->ID, '_bjb_job_location', true ) ); ?>" class="regular-text" placeholder="<?php _e('e.g. Remote (USA), New York, London', 'wp-job-board'); ?>">
			</td>
		</tr>

        <!-- Details -->
        <tr>
			<th><label for="bjb_job_experience"><?php _e( 'Experience Level', 'wp-job-board' ); ?></label></th>
			<td>
				<input type="text" name="bjb_job_experience" id="bjb_job_experience" value="<?php echo esc_attr( $experience ); ?>" class="regular-text" placeholder="e.g. 3+ Years">
			</td>
		</tr>
        <tr>
			<th><label for="bjb_job_deadline"><?php _e( 'Application Deadline', 'wp-job-board' ); ?></label></th>
			<td>
				<input type="date" name="bjb_job_deadline" id="bjb_job_deadline" value="<?php echo esc_attr( $deadline ); ?>" class="regular-text">
			</td>
		</tr>
        <tr>
			<th><label for="bjb_job_featured"><?php _e( 'Featured Job', 'wp-job-board' ); ?></label></th>
			<td>
				<input type="checkbox" name="bjb_job_featured" id="bjb_job_featured" value="1" <?php checked( $is_featured, 1 ); ?>>
                <label for="bjb_job_featured"><?php _e( 'Mark this job as featured', 'wp-job-board' ); ?></label>
			</td>
		</tr>
        <tr>
			<th><label for="bjb_job_apply_url"><?php _e( 'External Application URL', 'wp-job-board' ); ?></label></th>
			<td>
				<input type="url" name="bjb_job_apply_url" id="bjb_job_apply_url" value="<?php echo esc_attr( $apply_url ); ?>" class="regular-text" placeholder="https://...">
                <p class="description"><?php _e('Link where candidates can apply (e.g. LinkedIn, Company Website).', 'wp-job-board'); ?></p>
			</td>
		</tr>
        
        <!-- Descriptions -->
        <tr>
            <th><label for="bjb_job_responsibilities"><?php _e('Responsibilities', 'wp-job-board'); ?></label></th>
            <td>
                <textarea name="bjb_job_responsibilities" id="bjb_job_responsibilities" rows="5" class="widefat"><?php echo esc_textarea( $responsibilities ); ?></textarea>
            </td>
        </tr>
         <tr>
            <th><label for="bjb_job_requirements"><?php _e('Requirements', 'wp-job-board'); ?></label></th>
            <td>
                <textarea name="bjb_job_requirements" id="bjb_job_requirements" rows="5" class="widefat"><?php echo esc_textarea( $requirements ); ?></textarea>
            </td>
        </tr>
         <tr>
            <th><label for="bjb_job_benefits"><?php _e('Benefits', 'wp-job-board'); ?></label></th>
            <td>
                <textarea name="bjb_job_benefits" id="bjb_job_benefits" rows="5" class="widefat"><?php echo esc_textarea( $benefits ); ?></textarea>
            </td>
        </tr>

	</table>
	<?php
}

/**
 * Company Details Meta Box Callback
 */
function bjb_company_details_callback( $post ) {
	wp_nonce_field( 'bjb_save_company_details', 'bjb_company_details_nonce' );

	$website    = get_post_meta( $post->ID, '_bjb_company_website', true );
	$location   = get_post_meta( $post->ID, '_bjb_company_location', true );
    $email      = get_post_meta( $post->ID, '_bjb_company_email', true );
    $phone      = get_post_meta( $post->ID, '_bjb_company_phone', true );
    $industry   = get_post_meta( $post->ID, '_bjb_company_industry', true );
    $size       = get_post_meta( $post->ID, '_bjb_company_size', true );
    
    // Socials
    $fb         = get_post_meta( $post->ID, '_bjb_company_facebook', true );
    $li         = get_post_meta( $post->ID, '_bjb_company_linkedin', true );
    $tw         = get_post_meta( $post->ID, '_bjb_company_twitter', true );
    
    // About
    $about      = get_post_meta( $post->ID, '_bjb_company_about', true );

	?>
	<table class="form-table">
        <tr>
            <th><label for="bjb_company_about"><?php _e('About Company', 'wp-job-board'); ?></label></th>
            <td>
                <textarea name="bjb_company_about" id="bjb_company_about" rows="5" class="widefat" placeholder="<?php _e('Write a brief description about the company...', 'wp-job-board'); ?>"><?php echo esc_textarea( $about ); ?></textarea>
            </td>
        </tr>
		<tr>
			<th><label for="bjb_company_website"><?php _e( 'Website URL', 'wp-job-board' ); ?></label></th>
			<td><input type="url" name="bjb_company_website" id="bjb_company_website" value="<?php echo esc_attr( $website ); ?>" class="regular-text"></td>
		</tr>
        <tr>
			<th><label for="bjb_company_email"><?php _e( 'Email', 'wp-job-board' ); ?></label></th>
			<td><input type="email" name="bjb_company_email" id="bjb_company_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text"></td>
		</tr>
        <tr>
			<th><label for="bjb_company_phone"><?php _e( 'Phone', 'wp-job-board' ); ?></label></th>
			<td><input type="text" name="bjb_company_phone" id="bjb_company_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="bjb_company_location"><?php _e( 'Location', 'wp-job-board' ); ?></label></th>
			<td><input type="text" name="bjb_company_location" id="bjb_company_location" value="<?php echo esc_attr( $location ); ?>" class="regular-text" placeholder="<?php _e('e.g. Remote (USA), New York, London', 'wp-job-board'); ?>"></td>
		</tr>
        <tr>
			<th><label for="bjb_company_industry"><?php _e( 'Industry', 'wp-job-board' ); ?></label></th>
			<td><input type="text" name="bjb_company_industry" id="bjb_company_industry" value="<?php echo esc_attr( $industry ); ?>" class="regular-text"></td>
		</tr>
        <tr>
			<th><label for="bjb_company_size"><?php _e( 'Company Size', 'wp-job-board' ); ?></label></th>
			<td>
                <select name="bjb_company_size" id="bjb_company_size">
                    <option value="1-10" <?php selected($size, '1-10'); ?>>1-10 Employees</option>
                    <option value="11-50" <?php selected($size, '11-50'); ?>>11-50 Employees</option>
                    <option value="51-200" <?php selected($size, '51-200'); ?>>51-200 Employees</option>
                    <option value="200+" <?php selected($size, '200+'); ?>>200+ Employees</option>
                </select>
            </td>
		</tr>
        
        <tr><td colspan="2"><hr><h3><?php _e('Social Media', 'wp-job-board'); ?></h3></td></tr>
        
        <tr>
			<th><label for="bjb_company_facebook"><?php _e( 'Facebook', 'wp-job-board' ); ?></label></th>
			<td><input type="url" name="bjb_company_facebook" id="bjb_company_facebook" value="<?php echo esc_attr( $fb ); ?>" class="regular-text"></td>
		</tr>
        <tr>
			<th><label for="bjb_company_linkedin"><?php _e( 'LinkedIn', 'wp-job-board' ); ?></label></th>
			<td><input type="url" name="bjb_company_linkedin" id="bjb_company_linkedin" value="<?php echo esc_attr( $li ); ?>" class="regular-text"></td>
		</tr>
         <tr>
			<th><label for="bjb_company_twitter"><?php _e( 'Twitter (X)', 'wp-job-board' ); ?></label></th>
			<td><input type="url" name="bjb_company_twitter" id="bjb_company_twitter" value="<?php echo esc_attr( $tw ); ?>" class="regular-text"></td>
		</tr>
	</table>
	<?php
}

/**
 * Save Meta Box Data
 */
function bjb_save_meta_box_data( $post_id ) {
	// Function to save job data
	if ( isset( $_POST['bjb_job_details_nonce'] ) && wp_verify_nonce( $_POST['bjb_job_details_nonce'], 'bjb_save_job_details' ) ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $fields = [
            '_bjb_job_company_id',
            '_bjb_job_salary_min',
            '_bjb_job_salary_max',
            '_bjb_job_currency',
            '_bjb_job_experience',
            '_bjb_job_deadline',
            '_bjb_job_application_email',
            '_bjb_job_responsibilities',
            '_bjb_job_requirements',
            '_bjb_job_benefits'
        ];
        foreach($fields as $field) {
            if ( isset( $_POST[ str_replace('_', '', substr($field, 1)) ] ) ) { // Logic check: name is usually bjb_job... vs _bjb_job...
                 // Actually my inputs matched the meta keys minus the leading underscore in previous steps but let's be safe
            }
        }
        
        // Manual updates for clarity
		if ( isset( $_POST['bjb_job_company_id'] ) ) update_post_meta( $post_id, '_bjb_job_company_id', sanitize_text_field( $_POST['bjb_job_company_id'] ) );
        if ( isset( $_POST['bjb_job_salary_min'] ) ) update_post_meta( $post_id, '_bjb_job_salary_min', sanitize_text_field( $_POST['bjb_job_salary_min'] ) );
        if ( isset( $_POST['bjb_job_salary_max'] ) ) update_post_meta( $post_id, '_bjb_job_salary_max', sanitize_text_field( $_POST['bjb_job_salary_max'] ) );
        if ( isset( $_POST['bjb_job_currency'] ) ) update_post_meta( $post_id, '_bjb_job_currency', sanitize_text_field( $_POST['bjb_job_currency'] ) );
        if ( isset( $_POST['bjb_job_experience'] ) ) update_post_meta( $post_id, '_bjb_job_experience', sanitize_text_field( $_POST['bjb_job_experience'] ) );
        if ( isset( $_POST['bjb_job_deadline'] ) ) update_post_meta( $post_id, '_bjb_job_deadline', sanitize_text_field( $_POST['bjb_job_deadline'] ) );
        if ( isset( $_POST['bjb_job_location'] ) ) update_post_meta( $post_id, '_bjb_job_location', sanitize_text_field( $_POST['bjb_job_location'] ) );
        if ( isset( $_POST['bjb_job_apply_url'] ) ) update_post_meta( $post_id, '_bjb_job_apply_url', esc_url_raw( $_POST['bjb_job_apply_url'] ) );
        
        update_post_meta( $post_id, '_bjb_job_featured', isset( $_POST['bjb_job_featured'] ) ? '1' : '0' );

        if ( isset( $_POST['bjb_job_description'] ) ) update_post_meta( $post_id, '_bjb_job_description', wp_kses_post( $_POST['bjb_job_description'] ) );
        if ( isset( $_POST['bjb_job_responsibilities'] ) ) update_post_meta( $post_id, '_bjb_job_responsibilities', wp_kses_post( $_POST['bjb_job_responsibilities'] ) );
        if ( isset( $_POST['bjb_job_requirements'] ) ) update_post_meta( $post_id, '_bjb_job_requirements', wp_kses_post( $_POST['bjb_job_requirements'] ) );
        if ( isset( $_POST['bjb_job_benefits'] ) ) update_post_meta( $post_id, '_bjb_job_benefits', wp_kses_post( $_POST['bjb_job_benefits'] ) );
	}

	// Function to save company data
	if ( isset( $_POST['bjb_company_details_nonce'] ) && wp_verify_nonce( $_POST['bjb_company_details_nonce'], 'bjb_save_company_details' ) ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['bjb_company_about'] ) ) update_post_meta( $post_id, '_bjb_company_about', wp_kses_post( $_POST['bjb_company_about'] ) );

		if ( isset( $_POST['bjb_company_website'] ) ) update_post_meta( $post_id, '_bjb_company_website', esc_url_raw( $_POST['bjb_company_website'] ) );
		if ( isset( $_POST['bjb_company_location'] ) ) update_post_meta( $post_id, '_bjb_company_location', sanitize_text_field( $_POST['bjb_company_location'] ) );
        if ( isset( $_POST['bjb_company_email'] ) ) update_post_meta( $post_id, '_bjb_company_email', sanitize_email( $_POST['bjb_company_email'] ) );
        if ( isset( $_POST['bjb_company_phone'] ) ) update_post_meta( $post_id, '_bjb_company_phone', sanitize_text_field( $_POST['bjb_company_phone'] ) );
        if ( isset( $_POST['bjb_company_industry'] ) ) update_post_meta( $post_id, '_bjb_company_industry', sanitize_text_field( $_POST['bjb_company_industry'] ) );
        if ( isset( $_POST['bjb_company_size'] ) ) update_post_meta( $post_id, '_bjb_company_size', sanitize_text_field( $_POST['bjb_company_size'] ) );
        
        if ( isset( $_POST['bjb_company_facebook'] ) ) update_post_meta( $post_id, '_bjb_company_facebook', esc_url_raw( $_POST['bjb_company_facebook'] ) );
        if ( isset( $_POST['bjb_company_linkedin'] ) ) update_post_meta( $post_id, '_bjb_company_linkedin', esc_url_raw( $_POST['bjb_company_linkedin'] ) );
        if ( isset( $_POST['bjb_company_twitter'] ) ) update_post_meta( $post_id, '_bjb_company_twitter', esc_url_raw( $_POST['bjb_company_twitter'] ) );
	}
}
add_action( 'save_post', 'bjb_save_meta_box_data' );
