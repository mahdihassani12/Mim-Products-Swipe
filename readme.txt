=== Mim Products Swipe ===
Contributors: mahdihassani
Tags: woocommerce, elementor, products, carousel, slider
Requires at least: 6.2
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 3.5.3
License: GPLv2 or later

A simple, responsive WooCommerce product grid and carousel for Elementor.

== Description ==

Mim Products Swipe displays a selected WooCommerce product collection in a responsive grid or carousel. Choose the product source, apply category, brand, attribute, stock, include, and exclude filters, and customize the responsive layout in Elementor.

An optional headline includes a title, subtitle, and view-more link. Carousel mode uses Elementor's bundled Swiper library, so no additional carousel dependency or tab-related AJAX request is loaded.

== Installation ==

1. Install and activate WooCommerce and Elementor.
2. Upload and activate Mim Products Swipe.
3. Edit a page with Elementor and add the “Mim Products Swipe” widget.
4. Choose a product source and customize the layout and optional headline.

== Customization ==

Headline typography and colors, product cards, navigation, spacing, and responsive item counts can be changed in Elementor. Developers can use the `mps_carousel_config`, `mps_product_query_args`, and `mps_products_html` filters.

== Changelog ==

= 3.5.3 =
* Added responsive Start, Middle, and End alignment for carousel navigation.
* Scoped navigation and Grid pagination classes to the product widget.
* Added controlled important rules so Elementor styles reliably override theme styles.
* Replaced font chevrons with centered SVG icons that remain aligned at every button size.

= 3.5.2 =
* Added full Grid pagination styling for normal, hover, and active states.
* Added pagination border, active border, radius, dot gap, and z-index controls.

= 3.5.1 =
* Restored fully customizable carousel arrow styles by removing blocking important rules.
* Added arrow background, hover background, border, radius, button size, and z-index controls.
* Added responsive Grid pagination alignment and top-spacing controls.

= 3.5.0 =
* Moved carousel navigation to a simple bottom-right chevron design, 10px below products.
* Removed carousel pagination.
* Added responsive client-side Grid pagination with centered dots 10px below products.
* Added a separate responsive Items per page control and pagination style controls.

= 3.4.1 =
* Replaced the banner minimum-height behavior with a true responsive fixed-height control.
* Prevented the banner from stretching to the grid or carousel row height.

= 3.4.0 =
* Added an optional banner item for both Grid and Carousel layouts.
* Banner counts as one item and supports image, link, position, height, border, radius, overlay, and object position controls.
* Included the carousel edge safety gutter fix.

= 3.3.1 =
* Kept carousel edges and navigation inside the widget container.
* Applied carousel gap changes immediately in the Elementor editor.

= 3.3.0 =
* Added product element visibility controls for custom cards.
* Added product tag filtering, image ratio, and image fit controls.
* Added centered slides and automatic carousel height.
* Added safer loop fallback, reduced-motion support, and automatic overflow handling.
* Added minimum Elementor and WooCommerce compatibility checks.
* Expanded card, headline, arrow, and pagination styling controls.

= 3.2.0 =
* Added fully separate Grid and Elementor Swiper markup.
* Removed the extra Swiper stylesheet dependency.
* Added responsive carousel slide controls, slide grouping, transition speed, and pause-on-hover.
* Improved carousel reinitialization in the Elementor editor.
* Improved query validation, caching, accessibility, styling, and production packaging.
