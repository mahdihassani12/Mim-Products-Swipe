<?php
if ( ! defined( 'ABSPATH' ) ) exit;

final class MPS_Query {
	public static function brand_taxonomy() {
		foreach ( array( 'product_brand', 'pwb-brand', 'yith_product_brand', 'pa_brand' ) as $taxonomy ) {
			if ( taxonomy_exists( $taxonomy ) ) return $taxonomy;
		}
		return '';
	}

	public static function term_options( $taxonomy ) {
		$options = array();
		if ( ! taxonomy_exists( $taxonomy ) ) return $options;
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'number' => 500, 'orderby' => 'name', 'order' => 'ASC' ) );
		if ( is_wp_error( $terms ) ) return $options;
		foreach ( $terms as $term ) $options[ (string) $term->term_id ] = $term->name;
		return $options;
	}

	public static function attribute_options() {
		$options = array();
		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
			foreach ( self::term_options( $taxonomy ) as $id => $label ) $options[ $taxonomy . ':' . $id ] = $attribute->attribute_label . ': ' . $label;
		}
		return $options;
	}

	private static function ids( $value ) { return array_values( array_filter( array_map( 'absint', (array) $value ) ) ); }

	private static function add_attributes( &$tax_query, $values, $relation = 'AND' ) {
		$groups = array();
		foreach ( (array) $values as $value ) {
			$parts = explode( ':', sanitize_text_field( $value ), 2 );
			if ( 2 !== count( $parts ) || ! taxonomy_exists( $parts[0] ) ) continue;
			$groups[ $parts[0] ][] = absint( $parts[1] );
		}
		if ( ! $groups ) return;
		$attribute_query = array( 'relation' => 'OR' === $relation ? 'OR' : 'AND' );
		foreach ( $groups as $taxonomy => $term_ids ) $attribute_query[] = array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => array_unique( $term_ids ) );
		$tax_query[] = $attribute_query;
	}

	public static function build( $settings ) {
		$limit = min( 100, max( 1, absint( $settings['products_limit'] ?? 12 ) ) );
		$args = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => $limit, 'ignore_sticky_posts' => true, 'no_found_rows' => true );
		$tax_query = WC()->query->get_tax_query();
		$meta_query = WC()->query->get_meta_query();
		$brand_tax = self::brand_taxonomy();

		$global_categories = self::ids( $settings['categories'] ?? array() );
		$product_tags = self::ids( $settings['product_tags'] ?? array() );
		$global_brands = self::ids( $settings['brands'] ?? array() );
		if ( $global_categories ) $tax_query[] = array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $global_categories, 'operator' => ( $settings['category_operator'] ?? 'IN' ) === 'AND' ? 'AND' : 'IN' );
		if ( $product_tags ) $tax_query[] = array( 'taxonomy' => 'product_tag', 'field' => 'term_id', 'terms' => $product_tags );
		if ( $brand_tax && $global_brands ) $tax_query[] = array( 'taxonomy' => $brand_tax, 'field' => 'term_id', 'terms' => $global_brands );
		if ( ! $global_categories && ! empty( $settings['category_filter'] ) ) $tax_query[] = array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array_filter( array_map( 'sanitize_title', explode( ',', $settings['category_filter'] ) ) ) );
		if ( $brand_tax && ! $global_brands && ! empty( $settings['brand_filter'] ) ) $tax_query[] = array( 'taxonomy' => $brand_tax, 'field' => 'slug', 'terms' => array_filter( array_map( 'sanitize_title', explode( ',', $settings['brand_filter'] ) ) ) );
		self::add_attributes( $tax_query, $settings['attributes'] ?? array(), $settings['attribute_relation'] ?? 'AND' );

		if ( 'yes' === ( $settings['stock_only'] ?? 'yes' ) ) $meta_query[] = array( 'key' => '_stock_status', 'value' => 'instock' );
		$include = self::ids( $settings['include_products'] ?? array() );
		$exclude = self::ids( $settings['exclude_products'] ?? array() );
		if ( $include ) $args['post__in'] = $include;
		if ( $exclude ) $args['post__not_in'] = $exclude;

		$source = sanitize_key( $settings['source'] ?? 'latest' );
		if ( 'featured' === $source ) $tax_query[] = array( 'taxonomy' => 'product_visibility', 'field' => 'name', 'terms' => array( 'featured' ) );
		elseif ( 'sale' === $source ) { $sale_ids = wc_get_product_ids_on_sale(); $args['post__in'] = array_merge( array( 0 ), isset( $args['post__in'] ) ? array_intersect( $args['post__in'], $sale_ids ) : $sale_ids ); }
		elseif ( 'best_selling' === $source ) { $args['meta_key'] = 'total_sales'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
		elseif ( 'top_rated' === $source ) { $args['meta_key'] = '_wc_average_rating'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
		elseif ( 'random' === $source ) $args['orderby'] = 'rand';
		else {
			$orderby = sanitize_key( $settings['orderby'] ?? 'date' );
			$allowed_orderby = array( 'date', 'title', 'menu_order', 'price', 'rand' );
			$orderby = in_array( $orderby, $allowed_orderby, true ) ? $orderby : 'date';
			if ( 'price' === $orderby ) { $args['meta_key'] = '_price'; $args['orderby'] = 'meta_value_num'; } else $args['orderby'] = $orderby;
			$args['order'] = 'ASC' === ( $settings['order'] ?? 'DESC' ) ? 'ASC' : 'DESC';
		}

		$args['tax_query'] = $tax_query;
		$args['meta_query'] = $meta_query;
		return apply_filters( 'mps_product_query_args', $args, $settings );
	}

	public static function get_products( $args ) {
		if ( ! empty( $args['_mps_no_products'] ) ) {
			return array();
		}
		$key = md5( wp_json_encode( $args ) . MPS_VERSION . wp_cache_get_last_changed( 'posts' ) );
		$ids = wp_cache_get( $key, 'mim_products_swipe' );
		if ( false === $ids ) {
			$query = new WP_Query( array_merge( $args, array( 'fields' => 'ids' ) ) );
			$ids = $query->posts;
			wp_cache_set( $key, $ids, 'mim_products_swipe', 10 * MINUTE_IN_SECONDS );
		}
		// Taxonomy and metadata joins can return the same post ID more than once.
		// Keep every product to a single slide instead of filling the row with
		// repeated copies when the filtered result contains only one product.
		$ids = array_values( array_unique( array_map( 'absint', $ids ) ) );
		return array_filter( array_map( 'wc_get_product', $ids ) );
	}
}
