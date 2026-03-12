<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Elementor Integration Class
 */
final class BJB_Elementor_Extension {

	const VERSION = '1.0.0';
	const MINIMUM_ELEMENTOR_VERSION = '2.0.0';
	const MINIMUM_PHP_VERSION = '7.0';

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	public function init() {
		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		// Register Widgets
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        
        // Register Categories
        add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories' ] );
	}

    public function add_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'wp-job-board',
			[
				'title' => esc_html__( 'WP Job Board', 'wp-job-board' ),
				'icon'  => 'fa fa-plug',
			]
		);
	}

	public function register_widgets( $widgets_manager ) {
		require_once( __DIR__ . '/widgets/class-bjb-job-listing-widget.php' );
		$widgets_manager->register( new \BJB_Elementor_Job_Listing_Widget() );
	}
}

BJB_Elementor_Extension::instance();
