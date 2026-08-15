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
	public function get_title() { return esc_html__( 'Mim Products Swipe', 'mim-products-swipe' ); }
	public function get_icon() { return 'eicon-products'; }
	public function get_categories() { return array( 'general' ); }
	public function get_keywords() { return array( 'woocommerce', 'products', 'carousel', 'slider' ); }
	public function get_style_depends() { return array( 'swiper', 'mim-products-swipe' ); }
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
		$this->add_control( 'products_limit', array( 'label' => esc_html__( 'Number of products', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'default' => 12, 'min' => 1, 'max' => 100 ) );
		$this->add_control( 'orderby', array( 'label' => esc_html__( 'Order by', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'date', 'options' => array( 'date' => esc_html__( 'Date', 'mim-products-swipe' ), 'title' => esc_html__( 'Title', 'mim-products-swipe' ), 'menu_order' => esc_html__( 'Menu order', 'mim-products-swipe' ), 'price' => esc_html__( 'Price', 'mim-products-swipe' ), 'rand' => esc_html__( 'Random', 'mim-products-swipe' ) ), 'condition' => array( 'source' => 'latest' ) ) );
		$this->add_control( 'order', array( 'label' => esc_html__( 'Order', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'DESC', 'options' => array( 'DESC' => esc_html__( 'Descending', 'mim-products-swipe' ), 'ASC' => esc_html__( 'Ascending', 'mim-products-swipe' ) ), 'condition' => array( 'source' => 'latest' ) ) );
		$this->add_control( 'categories', $this->select2( esc_html__( 'Categories', 'mim-products-swipe' ), MPS_Query::term_options( 'product_cat' ) ) );
		$this->add_control( 'category_operator', array( 'label' => esc_html__( 'Category relation', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'IN', 'options' => array( 'IN' => esc_html__( 'Match any', 'mim-products-swipe' ), 'AND' => esc_html__( 'Match all', 'mim-products-swipe' ) ) ) );
		$this->add_control( 'brands', $this->select2( esc_html__( 'Brands', 'mim-products-swipe' ), MPS_Query::term_options( MPS_Query::brand_taxonomy() ) ) );
		$this->add_control( 'attributes', $this->select2( esc_html__( 'Attributes', 'mim-products-swipe' ), MPS_Query::attribute_options() ) );
		$this->add_control( 'attribute_relation', array( 'label' => esc_html__( 'Attribute relation', 'mim-products-swipe' ), 'type' => Controls_Manager::SELECT, 'default' => 'AND', 'options' => array( 'AND' => esc_html__( 'Match all', 'mim-products-swipe' ), 'OR' => esc_html__( 'Match any', 'mim-products-swipe' ) ) ) );
		$this->add_control( 'include_products', $this->select2( esc_html__( 'Include products', 'mim-products-swipe' ), $this->product_options() ) );
		$this->add_control( 'exclude_products', $this->select2( esc_html__( 'Exclude products', 'mim-products-swipe' ), $this->product_options() ) );
		$this->add_control( 'stock_only', array( 'label' => esc_html__( 'In-stock only', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'theme_template', array( 'label' => esc_html__( 'Use theme product template', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'layout_section', array( 'label' => esc_html__( 'Layout', 'mim-products-swipe' ) ) );
		$this->add_control( 'layout', array( 'label' => esc_html__( 'Layout', 'mim-products-swipe' ), 'type' => Controls_Manager::CHOOSE, 'default' => 'carousel', 'toggle' => false, 'options' => array( 'grid' => array( 'title' => esc_html__( 'Grid', 'mim-products-swipe' ), 'icon' => 'eicon-gallery-grid' ), 'carousel' => array( 'title' => esc_html__( 'Carousel', 'mim-products-swipe' ), 'icon' => 'eicon-slider-push' ) ) ) );
		$this->add_responsive_control( 'items', array( 'label' => esc_html__( 'Items per row', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'desktop_default' => 4, 'tablet_default' => 2, 'mobile_default' => 1, 'min' => 1, 'max' => 8, 'selectors' => array( '{{WRAPPER}} .mps-layout-grid .mps-track' => '--mps-items: {{VALUE}}' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => esc_html__( 'Gap', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'desktop_default' => array( 'size' => 24 ), 'tablet_default' => array( 'size' => 18 ), 'mobile_default' => array( 'size' => 12 ), 'selectors' => array( '{{WRAPPER}} .mps-layout-grid .mps-track' => '--mps-gap: {{SIZE}}{{UNIT}}' ) ) );
		foreach ( array( 'arrows' => 'Navigation arrows', 'dots' => 'Pagination dots', 'draggable' => 'Mouse drag & touch swipe', 'loop' => 'Loop' ) as $name => $label ) {
			$this->add_control( $name, array( 'label' => esc_html__( $label, 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'layout' => 'carousel' ) ) );
		}
		$this->add_control( 'autoplay', array( 'label' => esc_html__( 'Autoplay', 'mim-products-swipe' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'condition' => array( 'layout' => 'carousel' ) ) );
		$this->add_control( 'autoplay_speed', array( 'label' => esc_html__( 'Autoplay delay (ms)', 'mim-products-swipe' ), 'type' => Controls_Manager::NUMBER, 'default' => 4000, 'min' => 1000, 'step' => 100, 'condition' => array( 'autoplay' => 'yes' ) ) );
		$this->end_controls_section();

		$this->style_controls();
	}

	private function style_controls() {
		$this->start_controls_section( 'style_headline', array( 'label' => esc_html__( 'Headline', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => array( 'show_headline' => 'yes' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'headline_title_typography', 'selector' => '{{WRAPPER}} .mps-headline-title' ) );
		$this->add_control( 'headline_title_color', array( 'label' => esc_html__( 'Title color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'default' => '#09b9d4', 'selectors' => array( '{{WRAPPER}} .mps-headline-title' => 'color: {{VALUE}}' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'headline_subtitle_typography', 'selector' => '{{WRAPPER}} .mps-headline-subtitle' ) );
		$this->add_control( 'headline_subtitle_color', array( 'label' => esc_html__( 'Subtitle color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-headline-subtitle' => 'color: {{VALUE}}' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'headline_link_typography', 'selector' => '{{WRAPPER}} .mps-headline-link' ) );
		$this->add_control( 'headline_link_color', array( 'label' => esc_html__( 'Link color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'default' => '#09b9d4', 'selectors' => array( '{{WRAPPER}} .mps-headline-link' => 'color: {{VALUE}}' ) ) );
		$this->add_control( 'headline_border_color', array( 'label' => esc_html__( 'Divider color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-headline' => 'border-color: {{VALUE}}' ) ) );
		$this->add_responsive_control( 'headline_spacing', array( 'label' => esc_html__( 'Bottom spacing', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .mps-headline' => 'margin-bottom: {{SIZE}}{{UNIT}}' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_card', array( 'label' => esc_html__( 'Product Card', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'card_background', array( 'label' => esc_html__( 'Background', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-product' => 'background: {{VALUE}}' ) ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'card_border', 'selector' => '{{WRAPPER}} .mps-product' ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'card_shadow', 'selector' => '{{WRAPPER}} .mps-product' ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'card_hover_shadow', 'selector' => '{{WRAPPER}} .mps-product:hover' ) );
		$this->add_responsive_control( 'card_padding', array( 'label' => esc_html__( 'Padding', 'mim-products-swipe' ), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => array( '{{WRAPPER}} .mps-product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ) ) );
		$this->add_control( 'card_radius', array( 'label' => esc_html__( 'Border radius', 'mim-products-swipe' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'max' => 50 ) ), 'selectors' => array( '{{WRAPPER}} .mps-product' => 'border-radius: {{SIZE}}{{UNIT}}' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_navigation', array( 'label' => esc_html__( 'Navigation', 'mim-products-swipe' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'accent_color', array( 'label' => esc_html__( 'Accent color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'default' => '#09b9d4', 'selectors' => array( '{{WRAPPER}} .swiper-pagination-bullet-active' => 'background: {{VALUE}}' ) ) );
		$this->add_control( 'arrow_color', array( 'label' => esc_html__( 'Arrow color', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-arrow' => 'color: {{VALUE}}' ) ) );
		$this->add_control( 'arrow_background', array( 'label' => esc_html__( 'Arrow background', 'mim-products-swipe' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .mps-arrow' => 'background: {{VALUE}}' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$config = array(
			'layout' => 'grid' === ( $s['layout'] ?? 'carousel' ) ? 'grid' : 'carousel',
			'desktop' => max( 1, absint( $s['items'] ?? 4 ) ), 'tablet' => max( 1, absint( $s['items_tablet'] ?? 2 ) ), 'mobile' => max( 1, absint( $s['items_mobile'] ?? 1 ) ),
			'gapDesktop' => absint( $s['gap']['size'] ?? 24 ), 'gapTablet' => absint( $s['gap_tablet']['size'] ?? 18 ), 'gapMobile' => absint( $s['gap_mobile']['size'] ?? 12 ),
			'arrows' => 'yes' === ( $s['arrows'] ?? '' ), 'dots' => 'yes' === ( $s['dots'] ?? '' ), 'draggable' => 'yes' === ( $s['draggable'] ?? 'yes' ),
			'autoplay' => 'yes' === ( $s['autoplay'] ?? '' ), 'speed' => absint( $s['autoplay_speed'] ?? 4000 ), 'loop' => 'yes' === ( $s['loop'] ?? '' ),
		);
		$config = apply_filters( 'mps_carousel_config', $config, $s, $this );
		$args   = MPS_Query::build( $s );
		$label  = ! empty( $s['headline_title'] ) ? $s['headline_title'] : __( 'Products', 'mim-products-swipe' );
		?>
		<section class="mps mps-layout-<?php echo esc_attr( $config['layout'] ); ?>" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
			<?php $this->render_headline( $s ); ?>
			<div class="mps-panel">
				<div class="mps-viewport swiper" tabindex="0"><div class="mps-track swiper-wrapper"><?php echo MPS_Renderer::products_html( $args, 'yes' === ( $s['theme_template'] ?? 'yes' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div>
				<?php if ( 'carousel' === $config['layout'] && $config['arrows'] ) : ?><button class="mps-arrow mps-prev" type="button" aria-label="<?php esc_attr_e( 'Previous products', 'mim-products-swipe' ); ?>">&#8249;</button><button class="mps-arrow mps-next" type="button" aria-label="<?php esc_attr_e( 'Next products', 'mim-products-swipe' ); ?>">&#8250;</button><?php endif; ?>
				<?php if ( 'carousel' === $config['layout'] && $config['dots'] ) : ?><div class="mps-dots swiper-pagination" aria-label="<?php esc_attr_e( 'Carousel pagination', 'mim-products-swipe' ); ?>"></div><?php endif; ?>
			</div>
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
