<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MPS_Renderer {
	public static function products_html( $args, $settings, $carousel = false ) {
		$theme_template = 'yes' === ( $settings['theme_template'] ?? 'yes' );
		$products = MPS_Query::get_products( $args );
		$has_banner = 'yes' === ( $settings['show_banner'] ?? '' ) && ! empty( $settings['banner_image']['url'] );
		if ( ! $products && ! $has_banner ) {
			return '<div class="mps-empty">' . esc_html__( 'No products found.', 'mim-products-swipe' ) . '</div>';
		}

		ob_start();
		$item_position = 1;
		$banner_position = max( 1, absint( $settings['banner_position'] ?? 1 ) );
		$banner_rendered = false;
		foreach ( $products as $product ) {
			if ( $has_banner && ! $banner_rendered && $banner_position === $item_position ) {
				self::banner( $settings, $carousel );
				$banner_rendered = true;
				++$item_position;
			}
			$GLOBALS['post'] = get_post( $product->get_id() );
			$GLOBALS['product'] = $product;
			setup_postdata( $GLOBALS['post'] );
			$slide_class = $carousel ? 'mps-slide swiper-slide' : 'mps-grid-item';
			echo '<div class="' . esc_attr( $slide_class ) . '" role="listitem"><div class="mps-product">';
			if ( $theme_template ) {
				echo '<ul class="products columns-1">';
				wc_get_template_part( 'content', 'product' );
				echo '</ul>';
			} else {
					self::card( $product, $settings );
			}
			echo '</div></div>';
			++$item_position;
		}
		if ( $has_banner && ! $banner_rendered ) {
			self::banner( $settings, $carousel );
		}
		wp_reset_postdata();
		return apply_filters( 'mps_products_html', ob_get_clean(), $args, $theme_template, $carousel, $settings );
	}

	private static function banner( $settings, $carousel ) {
		$image = $settings['banner_image'];
		$url = esc_url( $image['url'] ?? '' );
		if ( ! $url ) {
			return;
		}

		$alt = sanitize_text_field( $settings['banner_alt'] ?? '' );
		if ( ! $alt && ! empty( $image['id'] ) ) {
			$alt = get_post_meta( absint( $image['id'] ), '_wp_attachment_image_alt', true );
		}
		$item_class = $carousel ? 'mps-slide mps-banner-item swiper-slide' : 'mps-grid-item mps-banner-item';
		$link = $settings['banner_link'] ?? array();
		$link_url = esc_url( $link['url'] ?? '' );
		$link_attributes = '';
		if ( $link_url ) {
			$link_attributes .= ' href="' . $link_url . '"';
			if ( ! empty( $link['is_external'] ) ) $link_attributes .= ' target="_blank"';
			$rel = array();
			if ( ! empty( $link['nofollow'] ) ) $rel[] = 'nofollow';
			if ( ! empty( $link['is_external'] ) ) $rel[] = 'noopener';
			if ( $rel ) $link_attributes .= ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"';
		}

		echo '<div class="' . esc_attr( $item_class ) . '" role="listitem">';
		$tag = $link_url ? 'a' : 'div';
		echo '<' . esc_html( $tag ) . ' class="mps-banner"' . $link_attributes . '>';
		echo '<img src="' . $url . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async">';
		echo '</' . esc_html( $tag ) . '></div>';
	}

	private static function card( $product, $settings ) {
		$permalink = get_permalink( $product->get_id() );
		do_action( 'mps_before_product_card', $product );
		echo '<article class="mps-card">';
		if ( 'yes' === ( $settings['show_image'] ?? 'yes' ) ) echo '<a class="mps-image" href="' . esc_url( $permalink ) . '">' . wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'decoding' => 'async' ) ) ) . '</a>';
			if ( 'yes' === ( $settings['show_sale_badge'] ?? 'yes' ) && $product->is_on_sale() ) {
				echo '<span class="mps-badge">' . esc_html__( 'Sale', 'mim-products-swipe' ) . '</span>';
			}
		echo '<div class="mps-card-content">';
		if ( 'yes' === ( $settings['show_category'] ?? 'yes' ) ) echo wp_kses_post( wc_get_product_category_list( $product->get_id(), ', ', '<div class="mps-category">', '</div>' ) );
		if ( 'yes' === ( $settings['show_title'] ?? 'yes' ) ) echo '<a href="' . esc_url( $permalink ) . '"><h3 class="mps-title">' . esc_html( $product->get_name() ) . '</h3></a>';
			if ( 'yes' === ( $settings['show_rating'] ?? 'yes' ) && wc_review_ratings_enabled() ) {
				echo '<div class="mps-rating">' . wp_kses_post( wc_get_rating_html( $product->get_average_rating(), $product->get_rating_count() ) ) . '</div>';
			}
		if ( 'yes' === ( $settings['show_price'] ?? 'yes' ) ) echo '<div class="mps-price">' . wp_kses_post( $product->get_price_html() ) . '</div>';
		if ( 'yes' === ( $settings['show_button'] ?? 'yes' ) ) woocommerce_template_loop_add_to_cart( array( 'product' => $product ) );
		echo '</div></article>';
		do_action( 'mps_after_product_card', $product );
	}
}
