<?php
/**
 * Plugin Name: Mim Products Swipe
 * Description: Responsive tabbed WooCommerce product grids and carousels for Elementor.
 * Version: 3.0.0
 * Author: Mahdi Hassani
 * Text Domain: mim-products-swipe
 * Requires Plugins: woocommerce, elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MPS_VERSION', '3.0.0' );
define( 'MPS_FILE', __FILE__ );
define( 'MPS_URL', plugin_dir_url( __FILE__ ) );

final class MPS_Plugin {
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'boot' ) );
	}

	public function boot() {
		if ( ! did_action( 'elementor/loaded' ) || ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'dependency_notice' ) );
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
		require_once __DIR__ . '/includes/class-mps-query.php';
		require_once __DIR__ . '/includes/class-mps-renderer.php';
		require_once __DIR__ . '/includes/class-mps-ajax.php';
		new MPS_Ajax();
	}

	public function register_assets() {
		wp_register_style( 'mim-products-swipe', MPS_URL . 'assets/css/mim-products-swipe.css', array(), MPS_VERSION );
		// Elementor Frontend provides its bundled, version-compatible Swiper utility.
		wp_register_script( 'mim-products-swipe', MPS_URL . 'assets/js/mim-products-swipe.js', array( 'elementor-frontend' ), MPS_VERSION, true );
		wp_localize_script(
			'mim-products-swipe',
			'MPS_DATA',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mps_load_products' ),
				'loading' => esc_html__( 'Loading products…', 'mim-products-swipe' ),
				'error'   => esc_html__( 'Products could not be loaded.', 'mim-products-swipe' ),
			)
		);
	}

	public function register_widget( $widgets_manager ) {
		require_once __DIR__ . '/includes/class-mps-widget.php';
		$widgets_manager->register( new \MPS_Elementor_Widget() );
	}

	public function dependency_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Mim Products Swipe requires WooCommerce and Elementor to be installed and active.', 'mim-products-swipe' ) . '</p></div>';
	}
}

new MPS_Plugin();
