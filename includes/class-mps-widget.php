<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

class MPS_Elementor_Widget extends Widget_Base {
	public function get_name() { return 'mim-products-swipe'; }
	public function get_title() { return esc_html__( 'Sova Products Swipe', 'mim-products-swipe' ); }
	public function get_icon() { return 'eicon-products'; }
	public function get_categories() { return array( 'general' ); }
	public function get_keywords() { return array( 'woocommerce', 'products', 'carousel', 'slider' ); }
	public function get_style_depends() { return array( 'mim-products-swipe' ); }
	public function get_script_depends() { return array( 'mim-products-swipe' ); }

	private function select2( $label, $options ) {
		return array( 'label' => $label, 'type' => Controls_Manager::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $options );
	}

	private function product_options() {
		static $options = null;
		if ( null !== $options ) {
			return $options;
		}
		$options = array();
		$products = wc_get_products( array( 'limit' => 300, 'status' => 'publish', 'orderby' => 'date', 'order' => 'DESC', 'return' => 'objects' ) );
		foreach ( $products as $product ) {
			$options[ $product->get_id() ] = $product->get_name();
		}
		return $options;
	}

	protected function register_controls() {
		$this->start_controls_section( 'headline', array( 'label' => esc_html__( 'Headline', 'mim-products-swipe' ) ) );
		$this->add_control( 'show_headline', array( 'label' => esc_html__( 'Show headline', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'label_on' => esc_html__( 'Show', 'mim-products-swipe' ), 'label_off' => esc_html__( 'Hide', 'mim-products-swipe' ), 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'headline_title', array( 'label' => esc_html__( 'Title', 'mim-products-swipe' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'Featured Products', 'mim-products-swipe' ), 'label_block' => true, 'condition' => array( 'show_headline' => 'yes' ) ) );
		$this->add_control( 'headline_subtitle', array( 'label' => esc_html__( 'Subtitle', 'mim-products-swipe' ), 'type' => Controls_Manager::TEXTAREA, 'default' => esc_html__( 'Choose your favorite products from this featured collection.', 'mim-products-swipe' ), 'rows' => 2, 'condition' => array( 'show_headline' => 'yes' ) ) );
		$this->add_control( 'headline_link_text', array( 'label' => esc_html__( 'View more text', 'mim-products-swipe' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'View All Products', 'mim-products-swipe' ), 'condition' => array( 'show_headline' => 'yes' ) ) );
		$this->add_control( 'headline_link', array( 'label' => esc_html__( 'View more link', 'mim-products-swipe' ), 'type' => Controls_Manager::URL, 'placeholder' => 'https://example.com/shop/', 'options' => array( 'url', 'is_external', 'nofollow' ), 'condition' => array( 'show_headline' => 'yes' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'products', array( 'label' => esc_html__( 'Products', 'mim-products-swipe' ) ) );
		$this->add_control( 'source', array( 'label' => esc_html__( 'Product source', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'latest', 'options' => array( 'latest' => esc_html__( 'Latest', 'mim-products-swipe' ), 'featured' => esc_html__( 'Featured', 'mim-products-swipe' ), 'sale' => esc_html__( 'On sale', 'mim-products-swipe' ), 'best_selling' => esc_html__( 'Best selling', 'mim-products-swipe' ), 'top_rated' => esc_html__( 'Top rated', 'mim-products-swipe' ), 'random' => esc_html__( 'Random', 'mim-products-swipe' ) ) ) );
		$this->add_control( 'products_limit', array( 'label' => esc_html__( 'Total items', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'default' => 12, 'min' => 1, 'max' => 100, 'description' => esc_html__( 'When the banner is enabled, it replaces one product in this total.', 'mim-products-swipe' ) ) );
		$this->add_control( 'orderby', array( 'label' => esc_html__( 'Order by', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'date', 'options' => array( 'date' => esc_html__( 'Date', 'mim-products-swipe' ), 'title' => esc_html__( 'Title', 'mim-products-swipe' ), 'menu_order' => esc_html__( 'Menu order', 'mim-products-swipe' ), 'price' => esc_html__( 'Price', 'mim-products-swipe' ), 'rand' => esc_html__( 'Random', 'mim-products-swipe' ) ), 'condition' => array( 'source' => 'latest' ) ) );
		$this->add_control( 'order', array( 'label' => esc_html__( 'Order', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'DESC', 'options' => array( 'DESC' => esc_html__( 'Descending', 'mim-products-swipe' ), 'ASC' => esc_html__( 'Ascending', 'mim-products-swipe' ) ), 'condition' => array( 'source' => 'latest' ) ) );
		$this->add_control( 'categories', $this->select2( esc_html__( 'Categories', 'mim-products-swipe' ), MPS_Query::term_options( 'product_cat' ) ) );
		$this->add_control( 'product_tags', $this->select2( esc_html__( 'Tags', 'mim-products-swipe' ), MPS_Query::term_options( 'product_tag' ) ) );
		$this->add_control( 'category_operator', array( 'label' => esc_html__( 'Category relation', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'IN', 'options' => array( 'IN' => esc_html__( 'Match any', 'mim-products-swipe' ), 'AND' => esc_html__( 'Match all', 'mim-products-swipe' ) ) ) );
		$this->add_control( 'brands', $this->select2( esc_html__( 'Brands', 'mim-products-swipe' ), MPS_Query::term_options( MPS_Query::brand_taxonomy() ) ) );
		$this->add_control( 'attributes', $this->select2( esc_html__( 'Attributes', 'mim-products-swipe' ), MPS_Query::attribute_options() ) );
		$this->add_control( 'attribute_relation', array( 'label' => esc_html__( 'Attribute relation', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'AND', 'options' => array( 'AND' => esc_html__( 'Match all', 'mim-products-swipe' ), 'OR' => esc_html__( 'Match any', 'mim-products-swipe' ) ) ) );
		$this->add_control( 'include_products', $this->select2( esc_html__( 'Include products', 'mim-products-swipe' ), $this->product_options() ) );
		$this->add_control( 'exclude_products', $this->select2( esc_html__( 'Exclude products', 'mim-products-swipe' ), $this->product_options() ) );
		$this->add_control( 'stock_only', array( 'label' => esc_html__( 'In-stock only', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'theme_template', array( 'label' => esc_html__( 'Use theme product template', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'banner', array( 'label' => esc_html__( 'Banner', 'mim-products-swipe' ) ) );
		$this->add_control( 'show_banner', array( 'label' => esc_html__( 'Show banner', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'banner_image', array( 'label' => esc_html__( 'Banner image', 'mim-products-swipe' ), 'type' => Controls_Manager::MEDIA, 'dynamic' => array( 'active' => true ), 'condition' => array( 'show_banner' => 'yes' ) ) );
		$this->add_control( 'banner_alt', array( 'label' => esc_html__( 'Alternative text', 'mim-products-swipe' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'condition' => array( 'show_banner' => 'yes' ) ) );
		$this->add_control( 'banner_link', array( 'label' => esc_html__( 'Banner link', 'mim-products-swipe' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ), 'options' => array( 'url', 'is_external', 'nofollow' ), 'condition' => array( 'show_banner' => 'yes' ) ) );
		$this->add_control( 'banner_position', array( 'label' => esc_html__( 'Item position', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'default' => 1, 'min' => 1, 'max' => 100, 'description' => esc_html__( 'Use 1 for the first item, 2 for the second item, and so on.', 'mim-products-swipe' ), 'condition' => array( 'show_banner' => 'yes' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'product_elements', array( 'label' => esc_html__( 'Product Elements', 'mim-products-swipe' ), 'condition' => array( 'theme_template!' => 'yes' ) ) );
		foreach ( array( 'show_image' => 'Image', 'show_category' => 'Category', 'show_title' => 'Title', 'show_rating' => 'Rating', 'show_price' => 'Price', 'show_sale_badge' => 'Sale badge', 'show_button' => 'Add to cart button' ) as $name => $label ) {
			$this->add_control( $name, array( 'label' => esc_html__( $label, 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		}
		$this->end_controls_section();

		$this->start_controls_section( 'style_banner', array( 'label' => esc_html__( 'Banner', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => array( 'show_banner' => 'yes' ) ) );
		$this->add_responsive_control( 'banner_height', array( 'label' => esc_html__( 'Fixed height', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 100, 'max' => 1000 ), 'vh' => array( 'min' => 10, 'max' => 100 ) ), 'default' => array( 'size' => 320, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .mps .mps-banner' => 'height: {{SIZE}}{{UNIT}}; min-height: 0;' ) ) );
		$this->add_control( 'banner_object_position', array( 'label' => esc_html__( 'Image position', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'center center', 'options' => array( 'center center' => esc_html__( 'Center', 'mim-products-swipe' ), 'center top' => esc_html__( 'Top', 'mim-products-swipe' ), 'center bottom' => esc_html__( 'Bottom', 'mim-products-swipe' ), 'left center' => esc_html__( 'Left', 'mim-products-swipe' ), 'right center' => esc_html__( 'Right', 'mim-products-swipe' ) ), 'selectors' => array( '{{WRAPPER}} .mps .mps-banner img' => 'object-position: {{VALUE}};' ) ) );
		$this->add_control( 'banner_overlay', array( 'label' => esc_html__( 'Overlay color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps .mps-banner::after' => 'background-color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'banner_border', 'selector' => '{{WRAPPER}} .mps .mps-banner' ) );
		$this->add_control( 'banner_radius', array( 'label' => esc_html__( 'Border radius', 'mim-products-swipe' ), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .mps .mps-banner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'layout_section', array( 'label' => esc_html__( 'Layout', 'mim-products-swipe' ) ) );
		$this->add_control( 'layout', array( 'label' => esc_html__( 'Layout', 'mim-products-swipe' ), 'type' => Controls_Manager::CHOOSE, 'default' => 'carousel', 'toggle' => false, 'options' => array( 'grid' => array( 'title' => esc_html__( 'Grid', 'mim-products-swipe' ), 'icon' => 'eicon-gallery-grid' ), 'carousel' => array( 'title' => esc_html__( 'Carousel', 'mim-products-swipe' ), 'icon' => 'eicon-slider-push' ) ) ) );
		$this->add_responsive_control( 'items', array( 'label' => esc_html__( 'Items per row', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'desktop_default' => 4, 'tablet_default' => 2, 'mobile_default' => 1, 'min' => 1, 'max' => 12, 'selectors' => array( '{{WRAPPER}} .mps .mps-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ), 'condition' => array( 'layout' => 'grid' ) ) );
		$this->add_responsive_control( 'slides_visible', array( 'label' => esc_html__( 'Slides visible', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'desktop_default' => 4, 'tablet_default' => 2, 'mobile_default' => 1, 'min' => 1, 'max' => 12, 'condition' => array( 'layout' => 'carousel' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => esc_html__( 'Gap', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'desktop_default' => array( 'size' => 24, 'unit' => 'px' ), 'tablet_default' => array( 'size' => 18, 'unit' => 'px' ), 'mobile_default' => array( 'size' => 12, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .mps .mps-grid' => 'gap: {{SIZE}}{{UNIT}};', '{{WRAPPER}} .mps' => '--mps-live-gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'slides_to_scroll', array( 'label' => esc_html__( 'Slides to scroll', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'default' => 1, 'min' => 1, 'max' => 12, 'condition' => array( 'layout' => 'carousel' ) ) );
		$this->add_control( 'transition_speed', array( 'label' => esc_html__( 'Transition speed (ms)', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'default' => 500, 'min' => 100, 'max' => 5000, 'step' => 50, 'condition' => array( 'layout' => 'carousel' ) ) );
		foreach ( array( 'arrows' => 'Navigation arrows', 'draggable' => 'Mouse drag & touch swipe', 'loop' => 'Loop' ) as $name => $label ) {
			$this->add_control( $name, array( 'label' => esc_html__( $label, 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'layout' => 'carousel' ) ) );
		}
		$this->add_control( 'grid_pagination', array( 'label' => esc_html__( 'Grid pagination', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'layout' => 'grid' ) ) );
		$this->add_responsive_control( 'grid_items_per_page', array( 'label' => esc_html__( 'Items per page', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'desktop_default' => 8, 'tablet_default' => 4, 'mobile_default' => 2, 'min' => 1, 'max' => 100, 'condition' => array( 'layout' => 'grid', 'grid_pagination' => 'yes' ) ) );
		$this->add_control( 'autoplay', array( 'label' => esc_html__( 'Autoplay', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'condition' => array( 'layout' => 'carousel' ) ) );
		$this->add_control( 'autoplay_speed', array( 'label' => esc_html__( 'Autoplay delay (ms)', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'default' => 4000, 'min' => 1000, 'step' => 100, 'condition' => array( 'autoplay' => 'yes' ) ) );
		$this->add_control( 'pause_on_hover', array( 'label' => esc_html__( 'Pause on hover', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'layout' => 'carousel', 'autoplay' => 'yes' ) ) );
		$this->add_control( 'centered_slides', array( 'label' => esc_html__( 'Center slides', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'condition' => array( 'layout' => 'carousel' ) ) );
		$this->add_control( 'auto_height', array( 'label' => esc_html__( 'Automatic height', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'condition' => array( 'layout' => 'carousel' ) ) );
		$this->end_controls_section();

		$this->style_controls();
	}

	private function style_controls() {
		$this->start_controls_section( 'style_headline', array( 'label' => esc_html__( 'Headline', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => array( 'show_headline' => 'yes' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'headline_title_typography', 'selector' => '{{WRAPPER}} .mps .mps-headline-title' ) );
		$this->add_control( 'headline_title_color', array( 'label' => esc_html__( 'Title color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'default' => '#09b9d4', 'selectors' => array( '{{WRAPPER}} .mps .mps-headline-title' => 'color: {{VALUE}}' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'headline_subtitle_typography', 'selector' => '{{WRAPPER}} .mps .mps-headline-subtitle' ) );
		$this->add_control( 'headline_subtitle_color', array( 'label' => esc_html__( 'Subtitle color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps .mps-headline-subtitle' => 'color: {{VALUE}}' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'headline_link_typography', 'selector' => '{{WRAPPER}} .mps .mps-headline-link' ) );
		$this->add_control( 'headline_link_color', array( 'label' => esc_html__( 'Link color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'default' => '#09b9d4', 'selectors' => array( '{{WRAPPER}} .mps .mps-headline-link' => 'color: {{VALUE}}' ) ) );
		$this->add_control( 'headline_link_hover_color', array( 'label' => esc_html__( 'Link hover color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps .mps-headline-link:hover' => 'color: {{VALUE}}' ) ) );
		$this->add_control( 'headline_border_color', array( 'label' => esc_html__( 'Divider color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps .mps-headline' => 'border-color: {{VALUE}}' ) ) );
		$this->add_responsive_control( 'headline_spacing', array( 'label' => esc_html__( 'Bottom spacing', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .mps .mps-headline' => 'margin-bottom: {{SIZE}}{{UNIT}}' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_card', array( 'label' => esc_html__( 'Product Card', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'card_background', array( 'label' => esc_html__( 'Background', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps .mps-product' => 'background: {{VALUE}}' ) ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'card_border', 'selector' => '{{WRAPPER}} .mps .mps-product' ) );
		$this->add_control( 'card_hover_border_color', array( 'label' => esc_html__( 'Hover border color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps .mps-product:hover' => 'border-color: {{VALUE}}' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'card_shadow', 'selector' => '{{WRAPPER}} .mps .mps-product' ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'card_hover_shadow', 'selector' => '{{WRAPPER}} .mps .mps-product:hover' ) );
		$this->add_responsive_control( 'card_padding', array( 'label' => esc_html__( 'Padding', 'mim-products-swipe' ), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => array( '{{WRAPPER}} .mps .mps-product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ) ) );
		$this->add_control( 'card_radius', array( 'label' => esc_html__( 'Border radius', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'max' => 50 ) ), 'selectors' => array( '{{WRAPPER}} .mps .mps-product' => 'border-radius: {{SIZE}}{{UNIT}}' ) ) );
		$this->add_control( 'image_ratio', array( 'label' => esc_html__( 'Image aspect ratio', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => '1 / 1', 'options' => array( '1 / 1' => esc_html__( 'Square', 'mim-products-swipe' ), '4 / 3' => '4:3', '3 / 4' => '3:4', '16 / 9' => '16:9', 'auto' => esc_html__( 'Original', 'mim-products-swipe' ) ), 'selectors' => array( '{{WRAPPER}} .mps .mps-image' => 'aspect-ratio: {{VALUE}};' ) ) );
		$this->add_control( 'image_fit', array( 'label' => esc_html__( 'Image fit', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'cover', 'options' => array( 'cover' => esc_html__( 'Cover', 'mim-products-swipe' ), 'contain' => esc_html__( 'Contain', 'mim-products-swipe' ) ), 'selectors' => array( '{{WRAPPER}} .mps .mps-image img' => 'object-fit: {{VALUE}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_navigation', array( 'label' => esc_html__( 'Navigation', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => array( 'layout' => 'carousel' ) ) );
		$this->add_control( 'arrow_color', array( 'label' => esc_html__( 'Arrow color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'default' => '#b7c0c8', 'selectors' => array( '{{WRAPPER}} .mps .mps-products-carousel-arrow' => 'color: {{VALUE}} !important;' ) ) );
		$this->add_control( 'arrow_background', array( 'label' => esc_html__( 'Background', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'default' => 'transparent', 'selectors' => array( '{{WRAPPER}} .mps .mps-products-carousel-arrow' => 'background-color: {{VALUE}} !important;' ) ) );
		$this->add_control( 'arrow_hover_color', array( 'label' => esc_html__( 'Arrow hover color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps .mps-products-carousel-arrow:hover' => 'color: {{VALUE}} !important;' ) ) );
		$this->add_control( 'arrow_hover_background', array( 'label' => esc_html__( 'Hover background', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps .mps-products-carousel-arrow:hover' => 'background-color: {{VALUE}} !important;' ) ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'arrow_border', 'selector' => '{{WRAPPER}} .mps .mps-products-carousel-arrow' ) );
		$this->add_responsive_control( 'arrow_icon_size', array( 'label' => esc_html__( 'Icon size', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 10, 'max' => 60 ) ), 'default' => array( 'size' => 24, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .mps .mps-products-carousel-arrow' => 'font-size: {{SIZE}}{{UNIT}} !important;' ) ) );
		$this->add_responsive_control( 'arrow_button_size', array( 'label' => esc_html__( 'Button size', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 16, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .mps .mps-products-carousel-arrow' => 'height: {{SIZE}}{{UNIT}} !important; width: {{SIZE}}{{UNIT}} !important;' ) ) );
		$this->add_control( 'arrow_radius', array( 'label' => esc_html__( 'Border radius', 'mim-products-swipe' ), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .mps .mps-products-carousel-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ) ) );
		$this->add_responsive_control( 'arrow_alignment', array( 'label' => esc_html__( 'Alignment', 'mim-products-swipe' ), 'type' => Controls_Manager::CHOOSE, 'options' => array( 'flex-start' => array( 'title' => esc_html__( 'Start', 'mim-products-swipe' ), 'icon' => 'eicon-text-align-left' ), 'center' => array( 'title' => esc_html__( 'Middle', 'mim-products-swipe' ), 'icon' => 'eicon-text-align-center' ), 'flex-end' => array( 'title' => esc_html__( 'End', 'mim-products-swipe' ), 'icon' => 'eicon-text-align-right' ) ), 'default' => 'flex-end', 'toggle' => false, 'selectors' => array( '{{WRAPPER}} .mps .mps-products-carousel-nav' => 'justify-content: {{VALUE}} !important;' ) ) );
		$this->add_control( 'arrow_z_index', array( 'label' => esc_html__( 'Z-index', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'default' => 5, 'min' => 0, 'max' => 9999, 'selectors' => array( '{{WRAPPER}} .mps .mps-products-carousel-nav' => 'z-index: {{VALUE}} !important;' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_grid_pagination', array( 'label' => esc_html__( 'Grid Pagination', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => array( 'layout' => 'grid', 'grid_pagination' => 'yes' ) ) );
		$this->add_control( 'grid_dot_color', array( 'label' => esc_html__( 'Dot color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'default' => '#bfc3c7', 'selectors' => array( '{{WRAPPER}} .mps .mps-products-grid-page' => 'background-color: {{VALUE}} !important;' ) ) );
		$this->add_control( 'grid_dot_hover_color', array( 'label' => esc_html__( 'Hover color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps .mps-products-grid-page:hover' => 'background-color: {{VALUE}} !important;' ) ) );
		$this->add_control( 'grid_dot_active_color', array( 'label' => esc_html__( 'Active color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'default' => '#10bdd3', 'selectors' => array( '{{WRAPPER}} .mps .mps-products-grid-page.is-active' => 'background-color: {{VALUE}} !important;' ) ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'grid_dot_border', 'selector' => '{{WRAPPER}} .mps .mps-products-grid-page' ) );
		$this->add_control( 'grid_dot_active_border_color', array( 'label' => esc_html__( 'Active border color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps .mps-products-grid-page.is-active' => 'border-color: {{VALUE}} !important;' ) ) );
		$this->add_responsive_control( 'grid_dot_size', array( 'label' => esc_html__( 'Dot size', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 4, 'max' => 20 ) ), 'default' => array( 'size' => 8, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .mps .mps-products-grid-page' => 'height: {{SIZE}}{{UNIT}} !important; width: {{SIZE}}{{UNIT}} !important;' ) ) );
		$this->add_responsive_control( 'grid_active_width', array( 'label' => esc_html__( 'Active width', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 10, 'max' => 60 ) ), 'default' => array( 'size' => 30, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .mps .mps-products-grid-page.is-active' => 'width: {{SIZE}}{{UNIT}} !important;' ) ) );
		$this->add_control( 'grid_dot_radius', array( 'label' => esc_html__( 'Border radius', 'mim-products-swipe' ), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .mps .mps-products-grid-page' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ) ) );
		$this->add_responsive_control( 'grid_pagination_gap', array( 'label' => esc_html__( 'Space between dots', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 60 ) ), 'default' => array( 'size' => 12, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .mps .mps-products-grid-pagination' => 'gap: {{SIZE}}{{UNIT}} !important;' ) ) );
		$this->add_responsive_control( 'grid_pagination_alignment', array( 'label' => esc_html__( 'Alignment', 'mim-products-swipe' ), 'type' => Controls_Manager::CHOOSE, 'options' => array( 'flex-start' => array( 'title' => esc_html__( 'Start', 'mim-products-swipe' ), 'icon' => 'eicon-text-align-left' ), 'center' => array( 'title' => esc_html__( 'Middle', 'mim-products-swipe' ), 'icon' => 'eicon-text-align-center' ), 'flex-end' => array( 'title' => esc_html__( 'End', 'mim-products-swipe' ), 'icon' => 'eicon-text-align-right' ) ), 'default' => 'center', 'toggle' => false, 'selectors' => array( '{{WRAPPER}} .mps .mps-products-grid-pagination' => 'justify-content: {{VALUE}} !important;' ) ) );
		$this->add_responsive_control( 'grid_pagination_top_space', array( 'label' => esc_html__( 'Top spacing', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 150 ) ), 'default' => array( 'size' => 10, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .mps .mps-products-grid-pagination' => 'margin-top: {{SIZE}}{{UNIT}} !important;' ) ) );
		$this->add_control( 'grid_pagination_z_index', array( 'label' => esc_html__( 'Z-index', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'default' => 5, 'min' => 0, 'max' => 9999, 'selectors' => array( '{{WRAPPER}} .mps .mps-products-grid-pagination' => 'z-index: {{VALUE}} !important;' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$config = array(
			'layout' => 'grid' === ( $s['layout'] ?? 'carousel' ) ? 'grid' : 'carousel',
			'desktop' => max( 1, absint( $s['slides_visible'] ?? 4 ) ), 'tablet' => max( 1, absint( $s['slides_visible_tablet'] ?? 2 ) ), 'mobile' => max( 1, absint( $s['slides_visible_mobile'] ?? 1 ) ),
			'gapDesktop' => absint( $s['gap']['size'] ?? 24 ), 'gapTablet' => absint( $s['gap_tablet']['size'] ?? 18 ), 'gapMobile' => absint( $s['gap_mobile']['size'] ?? 12 ),
			'arrows' => 'yes' === ( $s['arrows'] ?? '' ), 'draggable' => 'yes' === ( $s['draggable'] ?? 'yes' ),
			'gridPagination' => 'yes' === ( $s['grid_pagination'] ?? 'yes' ), 'gridItemsPerPage' => max( 1, absint( $s['grid_items_per_page'] ?? 8 ) ), 'gridItemsPerPageTablet' => max( 1, absint( $s['grid_items_per_page_tablet'] ?? 4 ) ), 'gridItemsPerPageMobile' => max( 1, absint( $s['grid_items_per_page_mobile'] ?? 2 ) ),
			'autoplay' => 'yes' === ( $s['autoplay'] ?? '' ), 'autoplayDelay' => max( 1000, absint( $s['autoplay_speed'] ?? 4000 ) ), 'transitionSpeed' => max( 100, absint( $s['transition_speed'] ?? 500 ) ),
			'slidesToScroll' => max( 1, absint( $s['slides_to_scroll'] ?? 1 ) ), 'pauseOnHover' => 'yes' === ( $s['pause_on_hover'] ?? 'yes' ), 'loop' => 'yes' === ( $s['loop'] ?? '' ),
			'centeredSlides' => 'yes' === ( $s['centered_slides'] ?? '' ), 'autoHeight' => 'yes' === ( $s['auto_height'] ?? '' ),
		);
		$has_banner = 'yes' === ( $s['show_banner'] ?? '' ) && ! empty( $s['banner_image']['url'] );
		$config['fixedBanner'] = $has_banner;
		$config = apply_filters( 'mps_carousel_config', $config, $s, $this );
		$query_settings = $s;
		if ( $has_banner ) {
			$total_items = max( 1, absint( $s['products_limit'] ?? 12 ) );
			if ( 1 === $total_items ) {
				$args = array( '_mps_no_products' => true );
			} else {
				$query_settings['products_limit'] = $total_items - 1;
				$args = MPS_Query::build( $query_settings );
			}
		} else {
			$args = MPS_Query::build( $query_settings );
		}
		$label  = ! empty( $s['headline_title'] ) ? $s['headline_title'] : __( 'Products', 'mim-products-swipe' );
		?>
		<section class="mps mps-layout-<?php echo esc_attr( $config['layout'] ); ?>" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
			<?php $this->render_headline( $s ); ?>
			<?php if ( 'carousel' === $config['layout'] ) : ?>
				<div class="mps-panel mps-carousel-shell<?php echo $has_banner ? ' mps-has-fixed-banner' : ''; ?>">
					<?php if ( $has_banner ) : ?><div class="mps-fixed-banner" role="list"><?php echo MPS_Renderer::banner_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
					<div class="mps-viewport mps-carousel swiper" tabindex="0"><div class="mps-track swiper-wrapper" role="list"><?php echo MPS_Renderer::products_html( $args, $s, true, ! $has_banner ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div>
					<?php if ( $config['arrows'] ) : ?><div class="mps-products-carousel-nav"><button class="mps-products-carousel-arrow mps-products-carousel-prev" type="button" aria-label="<?php esc_attr_e( 'Previous products', 'mim-products-swipe' ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6" /></svg></button><button class="mps-products-carousel-arrow mps-products-carousel-next" type="button" aria-label="<?php esc_attr_e( 'Next products', 'mim-products-swipe' ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6" /></svg></button></div><?php endif; ?>
				</div>
			<?php else : ?>
				<div class="mps-grid" role="list"><?php echo MPS_Renderer::products_html( $args, $s, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php if ( $config['gridPagination'] ) : ?><div class="mps-products-grid-pagination" aria-label="<?php esc_attr_e( 'Grid pagination', 'mim-products-swipe' ); ?>"></div><?php endif; ?>
			<?php endif; ?>
		</section>
		<?php
	}

	private function render_headline( $settings ) {
		if ( 'yes' !== ( $settings['show_headline'] ?? 'yes' ) ) {
			return;
		}
		$link = $settings['headline_link']['url'] ?? '';
		if ( $link ) {
			$this->add_link_attributes( 'headline_link', $settings['headline_link'] );
			$this->add_render_attribute( 'headline_link', 'class', 'mps-headline-link' );
		}
		?>
		<header class="mps-headline">
			<div class="mps-headline-copy">
				<?php if ( ! empty( $settings['headline_title'] ) ) : ?><h2 class="mps-headline-title"><?php echo esc_html( $settings['headline_title'] ); ?></h2><?php endif; ?>
				<?php if ( ! empty( $settings['headline_subtitle'] ) ) : ?><p class="mps-headline-subtitle"><?php echo esc_html( $settings['headline_subtitle'] ); ?></p><?php endif; ?>
			</div>
			<?php if ( $link && ! empty( $settings['headline_link_text'] ) ) : ?><a <?php echo $this->get_render_attribute_string( 'headline_link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $settings['headline_link_text'] ); ?><span aria-hidden="true">&#8594;</span></a><?php endif; ?>
		</header>
		<?php
	}
}
