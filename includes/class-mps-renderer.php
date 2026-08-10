<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MPS_Renderer {
	public static function products_html( $args, $theme_template = true ) {
		$products = MPS_Query::get_products( $args );
		if ( ! $products ) {
			return '<div class="mps-empty">' . esc_html__( 'No products found.', 'mim-products-swipe' ) . '</div>';
		}

		ob_start();
		foreach ( $products as $product ) {
			$GLOBALS['post'] = get_post( $product->get_id() );
			$GLOBALS['product'] = $product;
			setup_postdata( $GLOBALS['post'] );
			echo '<div class="mps-slide" role="group"><div class="mps-product">';
			if ( $theme_template ) {
				echo '<ul class="products columns-1">';
				wc_get_template_part( 'content', 'product' );
				echo '</ul>';
			} else {
				self::card( $product );
			}
			echo '</div></div>';
		}
		wp_reset_postdata();
		return apply_filters( 'mps_products_html', ob_get_clean(), $args, $theme_template );
	}

	private static function card( $product ) {
		$permalink = get_permalink( $product->get_id() );
		do_action( 'mps_before_product_card', $product );
		echo '<article class="mps-card">';
		echo '<a class="mps-image" href="' . esc_url( $permalink ) . '">' . wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) ) . '</a>';
			if ( $product->is_on_sale() ) {
				echo '<span class="mps-badge">' . esc_html__( 'Sale', 'mim-products-swipe' ) . '</span>';
			}
		echo '<div class="mps-card-content">';
		echo wc_get_product_category_list( $product->get_id(), ', ', '<div class="mps-category">', '</div>' );
		echo '<a href="' . esc_url( $permalink ) . '"><h3 class="mps-title">' . esc_html( $product->get_name() ) . '</h3></a>';
			if ( wc_review_ratings_enabled() ) {
				echo '<div class="mps-rating">' . wp_kses_post( wc_get_rating_html( $product->get_average_rating(), $product->get_rating_count() ) ) . '</div>';
			}
		echo '<div class="mps-price">' . wp_kses_post( $product->get_price_html() ) . '</div>';
		woocommerce_template_loop_add_to_cart( array( 'product' => $product ) );
		echo '</div></article>';
		do_action( 'mps_after_product_card', $product );
	}
}
