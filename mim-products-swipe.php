<?php
/**
 * Plugin Name: Sova Products Swipe
 * Description: Responsive WooCommerce product grids and carousels for Elementor.
 * Version: 3.6.0
 * Author: Mahdi Hassani
 * Text Domain: mim-products-swipe
 * Requires Plugins: woocommerce, elementor
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MPS_VERSION', '3.6.0' );
define( 'MPS_FILE', __FILE__ );
define( 'MPS_URL', plugin_dir_url( __FILE__ ) );

final class MPS_Plugin {
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'boot' ) );
	}

	public function boot() {
		load_plugin_textdomain( 'mim-products-swipe', false, dirname( plugin_basename( MPS_FILE ) ) . '/languages' );

		if ( ! did_action( 'elementor/loaded' ) || ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'dependency_notice' ) );
			return;
		}

		if ( version_compare( ELEMENTOR_VERSION, '3.20.0', '<' ) || version_compare( WC_VERSION, '8.0.0', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'version_notice' ) );
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_assets' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
		require_once __DIR__ . '/includes/class-mps-query.php';
		require_once __DIR__ . '/includes/class-mps-renderer.php';
	}

	public function register_assets() {
		wp_register_style( 'mim-products-swipe', MPS_URL . 'assets/css/mim-products-swipe.css', array(), MPS_VERSION );
		// Elementor Frontend provides its bundled, version-compatible Swiper utility.
		wp_register_script( 'mim-products-swipe', MPS_URL . 'assets/js/mim-products-swipe.js', array( 'elementor-frontend' ), MPS_VERSION, true );
	}

	public function register_widget( $widgets_manager ) {
		require_once __DIR__ . '/includes/class-mps-widget.php';
		$widgets_manager->register( new \MPS_Elementor_Widget() );
	}

	public function dependency_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Sova Products Swipe requires WooCommerce and Elementor to be installed and active.', 'mim-products-swipe' ) . '</p></div>';
	}

	public function version_notice() {
		if ( current_user_can( 'update_plugins' ) ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Sova Products Swipe requires Elementor 3.20 or newer and WooCommerce 8.0 or newer.', 'mim-products-swipe' ) . '</p></div>';
		}
	}
}

new MPS_Plugin();
