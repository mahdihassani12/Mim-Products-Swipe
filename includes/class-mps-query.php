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
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'number' => 500 ) );
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

	public static function build( $tab, $settings ) {
		$limit = min( 100, max( 1, absint( $settings['products_limit'] ?? 12 ) ) );
		$args = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => $limit, 'ignore_sticky_posts' => true, 'no_found_rows' => true );
		$tax_query = WC()->query->get_tax_query();
		$meta_query = WC()->query->get_meta_query();
		$brand_tax = self::brand_taxonomy();

		$global_categories = self::ids( $settings['categories'] ?? array() );
		$global_brands = self::ids( $settings['brands'] ?? array() );
		if ( $global_categories ) $tax_query[] = array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $global_categories, 'operator' => ( $settings['category_operator'] ?? 'IN' ) === 'AND' ? 'AND' : 'IN' );
		if ( $brand_tax && $global_brands ) $tax_query[] = array( 'taxonomy' => $brand_tax, 'field' => 'term_id', 'terms' => $global_brands );
		if ( ! $global_categories && ! empty( $settings['category_filter'] ) ) $tax_query[] = array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array_filter( array_map( 'sanitize_title', explode( ',', $settings['category_filter'] ) ) ) );
		if ( $brand_tax && ! $global_brands && ! empty( $settings['brand_filter'] ) ) $tax_query[] = array( 'taxonomy' => $brand_tax, 'field' => 'slug', 'terms' => array_filter( array_map( 'sanitize_title', explode( ',', $settings['brand_filter'] ) ) ) );
		self::add_attributes( $tax_query, $settings['attributes'] ?? array(), $settings['attribute_relation'] ?? 'AND' );

		$tab_categories = self::ids( $tab['categories'] ?? array() );
		$tab_brands = self::ids( $tab['brands'] ?? array() );
		if ( $tab_categories ) $tax_query[] = array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $tab_categories );
		if ( $brand_tax && $tab_brands ) $tax_query[] = array( 'taxonomy' => $brand_tax, 'field' => 'term_id', 'terms' => $tab_brands );
		self::add_attributes( $tax_query, $tab['attributes'] ?? array(), $tab['attribute_relation'] ?? 'AND' );

		if ( 'yes' === ( $settings['stock_only'] ?? 'yes' ) ) $meta_query[] = array( 'key' => '_stock_status', 'value' => 'instock' );
		$include = self::ids( $settings['include_products'] ?? array() );
		$exclude = self::ids( $settings['exclude_products'] ?? array() );
		if ( $include ) $args['post__in'] = $include;
		if ( $exclude ) $args['post__not_in'] = $exclude;

		$source = sanitize_key( $tab['source'] ?? 'latest' );
		if ( 'featured' === $source ) $tax_query[] = array( 'taxonomy' => 'product_visibility', 'field' => 'name', 'terms' => array( 'featured' ) );
		elseif ( 'sale' === $source ) { $sale_ids = wc_get_product_ids_on_sale(); $args['post__in'] = array_merge( array( 0 ), isset( $args['post__in'] ) ? array_intersect( $args['post__in'], $sale_ids ) : $sale_ids ); }
		elseif ( 'best_selling' === $source ) { $args['meta_key'] = 'total_sales'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
		elseif ( 'top_rated' === $source ) { $args['meta_key'] = '_wc_average_rating'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
		elseif ( 'random' === $source ) $args['orderby'] = 'rand';
		elseif ( 'category' === $source && ! empty( $tab['source_value'] ) ) $tax_query[] = array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => sanitize_title( $tab['source_value'] ) );
		elseif ( 'brand' === $source && $brand_tax && ! empty( $tab['source_value'] ) ) $tax_query[] = array( 'taxonomy' => $brand_tax, 'field' => 'slug', 'terms' => sanitize_title( $tab['source_value'] ) );
		elseif ( 'attribute' === $source && ! empty( $tab['source_value'] ) && false !== strpos( $tab['source_value'], ':' ) ) { list( $legacy_tax, $legacy_term ) = array_map( 'trim', explode( ':', $tab['source_value'], 2 ) ); if ( taxonomy_exists( $legacy_tax ) ) $tax_query[] = array( 'taxonomy' => sanitize_key( $legacy_tax ), 'field' => 'slug', 'terms' => sanitize_title( $legacy_term ) ); }
		else {
			$orderby = sanitize_key( $tab['orderby'] ?? $settings['orderby'] ?? 'date' );
			if ( 'price' === $orderby ) { $args['meta_key'] = '_price'; $args['orderby'] = 'meta_value_num'; } else $args['orderby'] = $orderby;
			$args['order'] = 'ASC' === ( $tab['order'] ?? $settings['order'] ?? 'DESC' ) ? 'ASC' : 'DESC';
		}

		$args['tax_query'] = $tax_query;
		$args['meta_query'] = $meta_query;
		return apply_filters( 'mps_product_query_args', $args, $tab, $settings );
	}

	public static function get_products( $args ) {
		$key = 'mps_' . md5( wp_json_encode( $args ) . MPS_VERSION . wp_cache_get_last_changed( 'posts' ) );
		$ids = get_transient( $key );
		if ( false === $ids ) {
			$query = new WP_Query( array_merge( $args, array( 'fields' => 'ids' ) ) );
			$ids = $query->posts;
			set_transient( $key, $ids, 10 * MINUTE_IN_SECONDS );
		}
		return array_filter( array_map( 'wc_get_product', $ids ) );
	}
}
