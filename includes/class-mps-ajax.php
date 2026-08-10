<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MPS_Ajax {
	public function __construct() {
		add_action( 'wp_ajax_mps_load_products', array( $this, 'load' ) );
		add_action( 'wp_ajax_nopriv_mps_load_products', array( $this, 'load' ) );
	}

	public function load() {
		check_ajax_referer( 'mps_load_products', 'nonce' );

		$payload   = isset( $_POST['payload'] ) ? sanitize_text_field( wp_unslash( $_POST['payload'] ) ) : '';
		$signature = isset( $_POST['signature'] ) ? sanitize_text_field( wp_unslash( $_POST['signature'] ) ) : '';

		if ( ! $payload || ! hash_equals( hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) ), $signature ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid request.', 'mim-products-swipe' ) ), 403 );
		}

		$decoded = base64_decode( $payload, true );
		$data    = $decoded ? json_decode( $decoded, true ) : null;

		if ( ! is_array( $data ) || empty( $data['args'] ) || ! is_array( $data['args'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid query.', 'mim-products-swipe' ) ), 400 );
		}

		wp_send_json_success( array( 'html' => MPS_Renderer::products_html( $data['args'], ! empty( $data['theme'] ) ) ) );
	}
}
