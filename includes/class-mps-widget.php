<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

class MPS_Elementor_Widget extends Widget_Base {
	public function get_name() { return 'mim-products-swipe'; }
	public function get_title() { return esc_html__( 'Mim Products Swipe', 'mim-products-swipe' ); }
	public function get_icon() { return 'eicon-products'; }
	public function get_categories() { return array( 'general' ); }
	public function get_keywords() { return array( 'woocommerce', 'products', 'carousel', 'tabs', 'slider' ); }
	public function get_style_depends() { return array( 'mim-products-swipe' ); }
	public function get_script_depends() { return array( 'mim-products-swipe' ); }
	private function select2( $label, $options, $multiple = true ) { return array( 'label' => $label, 'type' => Controls_Manager::SELECT2, 'multiple' => $multiple, 'label_block' => true, 'options' => $options ); }
	private function product_options() {
		static $options = null;
		if ( null !== $options ) return $options;
		$options = array();
		foreach ( wc_get_products( array( 'limit' => 300, 'status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ) ) as $product ) $options[ $product->get_id() ] = $product->get_name();
		return $options;
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => esc_html__( 'Content', 'mim-products-swipe' ) ) );
		$this->add_control( 'heading', array( 'label' => esc_html__( 'Heading', 'mim-products-swipe' ), 'type' => Controls_Manager::TEXT, 'default' => 'Bestsellers' ) );
		$this->add_control( 'products_limit', array( 'label' => esc_html__( 'Products per tab', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'default' => 12, 'min' => 1, 'max' => 100 ) );
		$this->add_control( 'theme_template', array( 'label' => esc_html__( 'Use theme product template', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'label_on' => 'Yes', 'label_off' => 'No', 'return_value' => 'yes', 'default' => 'yes', 'description' => esc_html__( 'Uses your theme\'s WooCommerce product card. Disable for the built-in card.', 'mim-products-swipe' ) ) );

		$repeater = new Repeater();
		$repeater->add_control( 'label', array( 'label' => esc_html__( 'Tab label', 'mim-products-swipe' ), 'type' => Controls_Manager::TEXT, 'default' => 'Latest' ) );
		$repeater->add_control( 'source', array( 'label' => esc_html__( 'Product source', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'latest', 'options' => array(
			'latest' => 'Latest', 'featured' => 'Featured', 'sale' => 'On sale', 'best_selling' => 'Best selling', 'top_rated' => 'Top rated', 'random' => 'Random'
		) ) );
		$repeater->add_control( 'categories', $this->select2( esc_html__( 'Categories', 'mim-products-swipe' ), MPS_Query::term_options( 'product_cat' ) ) );
		$repeater->add_control( 'brands', $this->select2( esc_html__( 'Brands', 'mim-products-swipe' ), MPS_Query::term_options( MPS_Query::brand_taxonomy() ) ) );
		$repeater->add_control( 'attributes', $this->select2( esc_html__( 'Attributes', 'mim-products-swipe' ), MPS_Query::attribute_options() ) );
		$repeater->add_control( 'attribute_relation', array( 'label' => esc_html__( 'Attribute relation', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'AND', 'options' => array( 'AND' => 'Match all', 'OR' => 'Match any' ) ) );
		$repeater->add_control( 'orderby', array( 'label' => esc_html__( 'Order by', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'date', 'options' => array( 'date' => 'Date', 'title' => 'Title', 'menu_order' => 'Menu order', 'price' => 'Price', 'rand' => 'Random' ), 'condition' => array( 'source' => 'latest' ) ) );
		$repeater->add_control( 'order', array( 'label' => esc_html__( 'Order', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'DESC', 'options' => array( 'DESC' => 'Descending', 'ASC' => 'Ascending' ), 'condition' => array( 'source' => 'latest' ) ) );
		$this->add_control( 'tabs', array( 'label' => esc_html__( 'Tabs', 'mim-products-swipe' ), 'type' => Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => '{{{ label }}}', 'default' => array(
			array( 'label' => 'Top products', 'source' => 'best_selling' ), array( 'label' => 'Latest', 'source' => 'latest' )
		) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'shared_filters', array( 'label' => esc_html__( 'Shared Filters', 'mim-products-swipe' ) ) );
		$this->add_control( 'categories', $this->select2( esc_html__( 'Categories', 'mim-products-swipe' ), MPS_Query::term_options( 'product_cat' ) ) );
		$this->add_control( 'category_operator', array( 'label' => esc_html__( 'Category relation', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'IN', 'options' => array( 'IN' => 'Match any', 'AND' => 'Match all' ) ) );
		$this->add_control( 'brands', $this->select2( esc_html__( 'Brands', 'mim-products-swipe' ), MPS_Query::term_options( MPS_Query::brand_taxonomy() ) ) );
		$this->add_control( 'attributes', $this->select2( esc_html__( 'Attributes', 'mim-products-swipe' ), MPS_Query::attribute_options() ) );
		$this->add_control( 'attribute_relation', array( 'label' => esc_html__( 'Attribute relation', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'AND', 'options' => array( 'AND' => 'Match all attributes', 'OR' => 'Match any attribute' ) ) );
		$this->add_control( 'include_products', $this->select2( esc_html__( 'Include products', 'mim-products-swipe' ), $this->product_options() ) );
		$this->add_control( 'exclude_products', $this->select2( esc_html__( 'Exclude products', 'mim-products-swipe' ), $this->product_options() ) );
		$this->add_control( 'stock_only', array( 'label' => esc_html__( 'In-stock only', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'ajax_tabs', array( 'label' => esc_html__( 'AJAX-load inactive tabs', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'carousel', array( 'label' => esc_html__( 'Carousel', 'mim-products-swipe' ) ) );
		$this->add_responsive_control( 'items', array( 'label' => esc_html__( 'Items per row', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'desktop_default' => 4, 'tablet_default' => 2, 'mobile_default' => 1, 'min' => 1, 'max' => 8, 'description' => esc_html__( 'Use the device icons to choose separate values for large, medium, and small screens.', 'mim-products-swipe' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => esc_html__( 'Gap', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'desktop_default' => array( 'size' => 24 ), 'tablet_default' => array( 'size' => 18 ), 'mobile_default' => array( 'size' => 12 ) ) );
		$this->add_control( 'arrows', array( 'label' => esc_html__( 'Navigation arrows', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'dots', array( 'label' => esc_html__( 'Pagination dots', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'draggable', array( 'label' => esc_html__( 'Mouse drag & touch swipe', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'autoplay', array( 'label' => esc_html__( 'Autoplay', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$this->add_control( 'autoplay_speed', array( 'label' => esc_html__( 'Autoplay delay (ms)', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'default' => 4000, 'min' => 1000, 'step' => 100, 'condition' => array( 'autoplay' => 'yes' ) ) );
		$this->add_control( 'loop', array( 'label' => esc_html__( 'Loop', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->style_controls();
	}

	private function style_controls() {
		$this->start_controls_section( 'style_heading', array( 'label' => esc_html__( 'Heading', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'heading_typography', 'selector' => '{{WRAPPER}} .mps-heading' ) );
		$this->add_control( 'heading_color', array( 'label' => 'Color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-heading' => 'color: {{VALUE}}' ) ) );
		$this->add_control( 'accent_color', array( 'label' => 'Accent color', 'type' => Controls_Manager::COLOR, 'default' => '#09b9d4', 'selectors' => array( '{{WRAPPER}} .mps-heading:after' => 'background: {{VALUE}}', '{{WRAPPER}} .mps-dot.is-active' => 'background: {{VALUE}}' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_tabs', array( 'label' => esc_html__( 'Tabs', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->start_controls_tabs( 'tab_style_states' );

		$this->start_controls_tab( 'tab_style_inactive', array( 'label' => esc_html__( 'Normal', 'mim-products-swipe' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'tab_inactive_typography', 'selector' => '{{WRAPPER}} .mps-tab:not(.is-active)' ) );
		$this->add_control( 'tab_inactive_color', array( 'label' => 'Text color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active), {{WRAPPER}} button.mps-tab:not(.is-active):focus, {{WRAPPER}} button.mps-tab:not(.is-active):focus-visible' => 'color: {{VALUE}} !important' ) ) );
		$this->add_control( 'tab_inactive_background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active), {{WRAPPER}} button.mps-tab:not(.is-active):focus, {{WRAPPER}} button.mps-tab:not(.is-active):focus-visible' => 'background: {{VALUE}} !important' ) ) );
		$this->add_control( 'tab_inactive_border_type', array( 'label' => 'Border type', 'type' => Controls_Manager::SELECT, 'default' => 'none', 'options' => array( 'none' => 'None', 'solid' => 'Solid', 'double' => 'Double', 'dotted' => 'Dotted', 'dashed' => 'Dashed' ), 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active)' => 'border-style: {{VALUE}} !important' ) ) );
		$this->add_control( 'tab_inactive_border_width', array( 'label' => 'Border width', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 10 ) ), 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active)' => 'border-width: {{SIZE}}{{UNIT}} !important' ), 'condition' => array( 'tab_inactive_border_type!' => 'none' ) ) );
		$this->add_control( 'tab_inactive_border_color', array( 'label' => 'Border color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active)' => 'border-color: {{VALUE}} !important' ), 'condition' => array( 'tab_inactive_border_type!' => 'none' ) ) );
		$this->add_responsive_control( 'tab_inactive_padding', array( 'label' => 'Padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em' ), 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important' ) ) );
		$this->add_responsive_control( 'tab_inactive_radius', array( 'label' => 'Border radius', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important' ) ) );
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_style_hover', array( 'label' => esc_html__( 'Hover', 'mim-products-swipe' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'tab_hover_typography', 'selector' => '{{WRAPPER}} button.mps-tab:not(.is-active):hover' ) );
		$this->add_control( 'tab_hover_color', array( 'label' => 'Text color', 'type' => Controls_Manager::COLOR, 'default' => '#344052', 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active):hover' => 'color: {{VALUE}} !important' ) ) );
		$this->add_control( 'tab_hover_background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF00', 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active):hover' => 'background: {{VALUE}} !important' ) ) );
		$this->add_control( 'tab_hover_border_type', array( 'label' => 'Border type', 'type' => Controls_Manager::SELECT, 'default' => 'none', 'options' => array( 'none' => 'None', 'solid' => 'Solid', 'double' => 'Double', 'dotted' => 'Dotted', 'dashed' => 'Dashed' ), 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active):hover' => 'border-style: {{VALUE}} !important' ) ) );
		$this->add_control( 'tab_hover_border_width', array( 'label' => 'Border width', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 10 ) ), 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active):hover' => 'border-width: {{SIZE}}{{UNIT}} !important' ), 'condition' => array( 'tab_hover_border_type!' => 'none' ) ) );
		$this->add_control( 'tab_hover_border_color', array( 'label' => 'Border color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active):hover' => 'border-color: {{VALUE}} !important' ), 'condition' => array( 'tab_hover_border_type!' => 'none' ) ) );
		$this->add_responsive_control( 'tab_hover_padding', array( 'label' => 'Padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em' ), 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active):hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important' ) ) );
		$this->add_responsive_control( 'tab_hover_radius', array( 'label' => 'Border radius', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} button.mps-tab:not(.is-active):hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important' ) ) );
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_style_active', array( 'label' => esc_html__( 'Active', 'mim-products-swipe' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'tab_active_typography', 'selector' => '{{WRAPPER}} button.mps-tab.is-active, {{WRAPPER}} button.mps-tab.is-active:hover, {{WRAPPER}} button.mps-tab.is-active:focus, {{WRAPPER}} button.mps-tab.is-active:focus-visible' ) );
		$this->add_control( 'tab_active_color', array( 'label' => 'Text color', 'type' => Controls_Manager::COLOR, 'default' => '#344052', 'selectors' => array( '{{WRAPPER}} button.mps-tab.is-active, {{WRAPPER}} button.mps-tab.is-active:hover, {{WRAPPER}} button.mps-tab.is-active:focus, {{WRAPPER}} button.mps-tab.is-active:focus-visible' => 'color: {{VALUE}} !important' ) ) );
		$this->add_control( 'tab_active_background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF00', 'selectors' => array( '{{WRAPPER}} button.mps-tab.is-active, {{WRAPPER}} button.mps-tab.is-active:hover, {{WRAPPER}} button.mps-tab.is-active:focus, {{WRAPPER}} button.mps-tab.is-active:focus-visible' => 'background: {{VALUE}} !important' ) ) );
		$this->add_control( 'tab_active_border_type', array( 'label' => 'Border type', 'type' => Controls_Manager::SELECT, 'default' => 'solid', 'options' => array( 'none' => 'None', 'solid' => 'Solid', 'double' => 'Double', 'dotted' => 'Dotted', 'dashed' => 'Dashed' ), 'selectors' => array( '{{WRAPPER}} button.mps-tab.is-active, {{WRAPPER}} button.mps-tab.is-active:hover, {{WRAPPER}} button.mps-tab.is-active:focus, {{WRAPPER}} button.mps-tab.is-active:focus-visible' => 'border-style: {{VALUE}} !important' ) ) );
		$this->add_control( 'tab_active_border_width', array( 'label' => 'Border width', 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 2, 'unit' => 'px' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 10 ) ), 'selectors' => array( '{{WRAPPER}} button.mps-tab.is-active, {{WRAPPER}} button.mps-tab.is-active:hover, {{WRAPPER}} button.mps-tab.is-active:focus, {{WRAPPER}} button.mps-tab.is-active:focus-visible' => 'border-width: {{SIZE}}{{UNIT}} !important' ), 'condition' => array( 'tab_active_border_type!' => 'none' ) ) );
		$this->add_control( 'tab_active_border_color', array( 'label' => 'Border color', 'type' => Controls_Manager::COLOR, 'default' => '#09b9d4', 'selectors' => array( '{{WRAPPER}} button.mps-tab.is-active, {{WRAPPER}} button.mps-tab.is-active:hover, {{WRAPPER}} button.mps-tab.is-active:focus, {{WRAPPER}} button.mps-tab.is-active:focus-visible' => 'border-color: {{VALUE}} !important' ), 'condition' => array( 'tab_active_border_type!' => 'none' ) ) );
		$this->add_responsive_control( 'tab_active_padding', array( 'label' => 'Padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em' ), 'default' => array( 'top' => 5, 'right' => 20, 'bottom' => 5, 'left' => 20, 'unit' => 'px', 'isLinked' => false ), 'selectors' => array( '{{WRAPPER}} button.mps-tab.is-active, {{WRAPPER}} button.mps-tab.is-active:hover, {{WRAPPER}} button.mps-tab.is-active:focus, {{WRAPPER}} button.mps-tab.is-active:focus-visible' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important' ) ) );
		$this->add_responsive_control( 'tab_active_radius', array( 'label' => 'Border radius', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'default' => array( 'top' => 99, 'right' => 99, 'bottom' => 99, 'left' => 99, 'unit' => 'px', 'isLinked' => true ), 'selectors' => array( '{{WRAPPER}} button.mps-tab.is-active, {{WRAPPER}} button.mps-tab.is-active:hover, {{WRAPPER}} button.mps-tab.is-active:focus, {{WRAPPER}} button.mps-tab.is-active:focus-visible' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important' ) ) );
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section( 'style_card', array( 'label' => esc_html__( 'Product Card', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'card_background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-product' => 'background: {{VALUE}}' ) ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'card_border', 'selector' => '{{WRAPPER}} .mps-product' ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'card_shadow', 'selector' => '{{WRAPPER}} .mps-product' ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'card_hover_shadow', 'selector' => '{{WRAPPER}} .mps-product:hover' ) );
		$this->add_control( 'card_hover_lift', array( 'label' => esc_html__( 'Hover lift', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => -20, 'max' => 0 ) ), 'default' => array( 'size' => -3 ), 'selectors' => array( '{{WRAPPER}} .mps-product:hover' => 'transform: translateY({{SIZE}}{{UNIT}})' ) ) );
		$this->add_responsive_control( 'card_padding', array( 'label' => 'Padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px' ), 'selectors' => array( '{{WRAPPER}} .mps-product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ) ) );
		$this->add_control( 'card_radius', array( 'label' => 'Border radius', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'max' => 50 ) ), 'selectors' => array( '{{WRAPPER}} .mps-product' => 'border-radius: {{SIZE}}{{UNIT}}' ) ) );
		$this->add_responsive_control( 'image_height', array( 'label' => 'Image height', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 80, 'max' => 600 ) ), 'selectors' => array( '{{WRAPPER}} .mps-product img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_text', array( 'label' => esc_html__( 'Product Text', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typography', 'selector' => '{{WRAPPER}} .woocommerce-loop-product__title, {{WRAPPER}} .mps-title' ) );
		$this->add_control( 'title_color', array( 'label' => 'Title color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .woocommerce-loop-product__title, {{WRAPPER}} .mps-title' => 'color: {{VALUE}}' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'price_typography', 'selector' => '{{WRAPPER}} .price, {{WRAPPER}} .mps-price' ) );
		$this->add_control( 'price_color', array( 'label' => 'Price color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .price, {{WRAPPER}} .mps-price' => 'color: {{VALUE}}' ) ) );
		$this->add_control( 'button_color', array( 'label' => 'Button text', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .button' => 'color: {{VALUE}}' ) ) );
		$this->add_control( 'button_background', array( 'label' => 'Button background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .button' => 'background: {{VALUE}}' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_navigation', array( 'label' => esc_html__( 'Navigation', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'arrow_color', array( 'label' => 'Arrow color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-arrow' => 'color: {{VALUE}}' ) ) );
		$this->add_control( 'arrow_background', array( 'label' => 'Arrow background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-arrow' => 'background: {{VALUE}}' ) ) );
		$this->add_control( 'arrow_border_color', array( 'label' => 'Arrow border color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-arrow' => 'border-color: {{VALUE}}' ) ) );
		$this->add_control( 'arrow_size', array( 'label' => 'Arrow size', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 24, 'max' => 80 ) ), 'selectors' => array( '{{WRAPPER}} .mps-arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}' ) ) );
		$this->add_control( 'arrow_icon_size', array( 'label' => 'Arrow icon size', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 12, 'max' => 60 ) ), 'selectors' => array( '{{WRAPPER}} .mps-arrow' => 'font-size: {{SIZE}}{{UNIT}}' ) ) );
		$this->add_responsive_control( 'arrow_offset', array( 'label' => 'Arrow edge offset', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => -60, 'max' => 60 ) ), 'selectors' => array( '{{WRAPPER}} .mps-prev' => 'inset-inline-start: {{SIZE}}{{UNIT}}', '{{WRAPPER}} .mps-next' => 'inset-inline-end: {{SIZE}}{{UNIT}}' ) ) );
		$this->add_control( 'dot_color', array( 'label' => 'Dot color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-dot' => 'background: {{VALUE}}' ) ) );
		$this->add_control( 'dot_active_color', array( 'label' => 'Active dot color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-dot.is-active' => 'background: {{VALUE}}' ) ) );
		$this->add_control( 'dot_size', array( 'label' => 'Dot size', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 4, 'max' => 30 ) ), 'selectors' => array( '{{WRAPPER}} .mps-dot' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}' ) ) );
		$this->add_control( 'dot_active_width', array( 'label' => 'Active dot width', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 4, 'max' => 80 ) ), 'selectors' => array( '{{WRAPPER}} .mps-dot.is-active' => 'width: {{SIZE}}{{UNIT}}' ) ) );
		$this->add_responsive_control( 'pagination_spacing', array( 'label' => 'Pagination spacing', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .mps-dots' => 'margin-top: {{SIZE}}{{UNIT}}' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$tabs = ! empty( $s['tabs'] )
			? $s['tabs']
			: array( array( 'label' => 'Products', 'source' => 'latest' ) );
		$tabs = apply_filters( 'mps_widget_tabs', $tabs, $s, $this );
		$uid  = 'mps-' . $this->get_id();

		$config = array(
			'desktop'    => max( 1, absint( $s['items'] ?? 4 ) ),
			'tablet'     => max( 1, absint( $s['items_tablet'] ?? 2 ) ),
			'mobile'     => max( 1, absint( $s['items_mobile'] ?? 1 ) ),
			'gapDesktop' => absint( $s['gap']['size'] ?? 24 ),
			'gapTablet'  => absint( $s['gap_tablet']['size'] ?? 18 ),
			'gapMobile'  => absint( $s['gap_mobile']['size'] ?? 12 ),
			'arrows'     => 'yes' === ( $s['arrows'] ?? '' ),
			'dots'       => 'yes' === ( $s['dots'] ?? '' ),
			'draggable'  => 'yes' === ( $s['draggable'] ?? 'yes' ),
			'autoplay'   => 'yes' === ( $s['autoplay'] ?? '' ),
			'speed'      => absint( $s['autoplay_speed'] ?? 4000 ),
			'loop'       => 'yes' === ( $s['loop'] ?? '' ),
			'rtl'        => is_rtl(),
		);

		$config    = apply_filters( 'mps_carousel_config', $config, $s, $this );
		$is_editor = class_exists( '\\Elementor\\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode();
		?>
		<section id="<?php echo esc_attr( $uid ); ?>" class="mps" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>" aria-roledescription="carousel" aria-label="<?php echo esc_attr( $s['heading'] ?: __( 'Products', 'mim-products-swipe' ) ); ?>">
			<div class="mps-top"><h2 class="mps-heading"><?php echo esc_html( $s['heading'] ); ?></h2><div class="mps-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Product groups', 'mim-products-swipe' ); ?>">
			<?php foreach ( $tabs as $i => $tab ) : $tab_id = $uid . '-tab-' . $i; $panel_id = $uid . '-panel-' . $i; ?><button id="<?php echo esc_attr( $tab_id ); ?>" class="mps-tab <?php echo 0 === $i ? 'is-active' : ''; ?>" type="button" role="tab" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>" tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>" data-tab="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $tab['label'] ); ?></button><?php endforeach; ?>
			</div></div>
			<div class="mps-status screen-reader-text" aria-live="polite"></div>
			<?php foreach ( $tabs as $i => $tab ) :
				$args = MPS_Query::build( $tab, $s );
				$lazy = 0 !== $i && 'yes' === ( $s['ajax_tabs'] ?? 'yes' ) && ! $is_editor;
				$payload = base64_encode( wp_json_encode( array( 'args' => $args, 'theme' => 'yes' === ( $s['theme_template'] ?? 'yes' ) ) ) );
				$signature = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
			?>
			<div id="<?php echo esc_attr( $uid . '-panel-' . $i ); ?>" class="mps-panel <?php echo 0 === $i ? 'is-active' : ''; ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr( $uid . '-tab-' . $i ); ?>" data-panel="<?php echo esc_attr( $i ); ?>" <?php echo 0 === $i ? '' : 'hidden'; ?> <?php if ( $lazy ) : ?>data-payload="<?php echo esc_attr( $payload ); ?>" data-signature="<?php echo esc_attr( $signature ); ?>"<?php endif; ?>>
				<div class="mps-viewport" tabindex="0"><div class="mps-track"><?php echo $lazy ? '<div class="mps-loading" aria-hidden="true"></div>' : MPS_Renderer::products_html( $args, 'yes' === ( $s['theme_template'] ?? 'yes' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div>
				<?php if ( 'yes' === ( $s['arrows'] ?? '' ) ) : ?><button class="mps-arrow mps-prev" type="button" aria-label="<?php esc_attr_e( 'Previous products', 'mim-products-swipe' ); ?>">&#8249;</button><button class="mps-arrow mps-next" type="button" aria-label="<?php esc_attr_e( 'Next products', 'mim-products-swipe' ); ?>">&#8250;</button><?php endif; ?>
				<?php if ( 'yes' === ( $s['dots'] ?? '' ) ) : ?><div class="mps-dots" aria-label="<?php esc_attr_e( 'Carousel pagination', 'mim-products-swipe' ); ?>"></div><?php endif; ?>
			</div><?php endforeach; ?>
		</section>
		<?php
	}
}
