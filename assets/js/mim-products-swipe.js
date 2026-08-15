( function ( window, document ) {
	'use strict';

	function getConfig( root ) {
		try {
			return JSON.parse( root.getAttribute( 'data-config' ) || '{}' );
		} catch ( error ) {
			return {};
		}
	}

	function destroyCarousel( root ) {
		if ( root.mpsGapWatcher ) {
			window.clearInterval( root.mpsGapWatcher );
			root.mpsGapWatcher = null;
		}
		if ( root.mpsSwiper && 'function' === typeof root.mpsSwiper.destroy ) {
			root.mpsSwiper.destroy( true, true );
		}
		root.mpsSwiper = null;
	}

	function destroyGridPagination( root ) {
		if ( root.mpsGridResize ) {
			window.removeEventListener( 'resize', root.mpsGridResize );
			root.mpsGridResize = null;
		}
	}

	function getItemsPerPage( config ) {
		if ( window.innerWidth <= 767 ) {
			return Math.max( 1, Number( config.gridItemsPerPageMobile ) || 2 );
		}
		if ( window.innerWidth <= 1024 ) {
			return Math.max( 1, Number( config.gridItemsPerPageTablet ) || 4 );
		}
		return Math.max( 1, Number( config.gridItemsPerPage ) || 8 );
	}

	function initGridPagination( root ) {
		var config = getConfig( root );
		var grid = root.querySelector( '.mps-grid' );
		var pagination = root.querySelector( '.mps-products-grid-pagination' );

		destroyGridPagination( root );
		if ( 'grid' !== config.layout || ! config.gridPagination || ! grid || ! pagination ) {
			return;
		}

		function renderPage( requestedPage ) {
			var items = Array.prototype.slice.call( grid.children );
			var perPage = getItemsPerPage( config );
			var pageCount = Math.ceil( items.length / perPage );
			var page = Math.min( Math.max( 0, requestedPage ), Math.max( 0, pageCount - 1 ) );

			items.forEach( function ( item, index ) {
				item.hidden = index < page * perPage || index >= ( page + 1 ) * perPage;
			} );
			pagination.innerHTML = '';
			pagination.hidden = pageCount <= 1;

			for ( var index = 0; index < pageCount; index++ ) {
				( function ( pageIndex ) {
					var button = document.createElement( 'button' );
					button.type = 'button';
					button.className = 'mps-products-grid-page' + ( pageIndex === page ? ' is-active' : '' );
					button.setAttribute( 'aria-label', 'Page ' + ( pageIndex + 1 ) );
					if ( pageIndex === page ) {
						button.setAttribute( 'aria-current', 'page' );
					}
					button.addEventListener( 'click', function () { renderPage( pageIndex ); } );
					pagination.appendChild( button );
				}( index ) );
			}
			root.mpsGridPage = page;
		}

		renderPage( root.mpsGridPage || 0 );
		root.mpsGridResize = function () { renderPage( root.mpsGridPage || 0 ); };
		window.addEventListener( 'resize', root.mpsGridResize );
	}

	function getLiveGap( root, fallback ) {
		var value = parseFloat( window.getComputedStyle( root ).getPropertyValue( '--mps-live-gap' ) );
		return Number.isFinite( value ) ? Math.max( 0, value ) : fallback;
	}

	function watchEditorGap( root ) {
		if ( ! window.elementorFrontend || ! window.elementorFrontend.isEditMode || ! window.elementorFrontend.isEditMode() ) {
			return;
		}

		root.mpsGapWatcher = window.setInterval( function () {
			if ( ! root.isConnected ) {
				window.clearInterval( root.mpsGapWatcher );
				return;
			}
			if ( ! root.mpsSwiper ) {
				return;
			}

			var gap = getLiveGap( root, root.mpsGap || 0 );
			if ( gap !== root.mpsGap ) {
				root.mpsGap = gap;
				root.mpsSwiper.params.spaceBetween = gap;
				root.mpsSwiper.update();
			}
		}, 150 );
	}

	function initCarousel( root ) {
		var config = getConfig( root );
		var viewport = root.querySelector( '.mps-carousel' );
		var shell = root.querySelector( '.mps-carousel-shell' );
		var Swiper = window.elementorFrontend && window.elementorFrontend.utils && window.elementorFrontend.utils.swiper;
		var slideCount = viewport ? viewport.querySelectorAll( '.swiper-slide' ).length : 0;
		var largestView = Math.max( Number( config.mobile ) || 1, Number( config.tablet ) || 2, Number( config.desktop ) || 4 );
		var canLoop = Boolean( config.loop ) && slideCount > largestView;
		var liveGap = getLiveGap( root, Math.max( 0, Number( config.gapMobile ) || 0 ) );

		destroyCarousel( root );
		if ( 'carousel' !== config.layout || ! viewport || ! shell || ! Swiper ) {
			return;
		}

		var options = {
			a11y: { enabled: true },
			allowTouchMove: false !== config.draggable,
			grabCursor: false !== config.draggable,
			keyboard: { enabled: true },
			autoHeight: Boolean( config.autoHeight ),
			centeredSlides: Boolean( config.centeredSlides ),
			loop: canLoop,
			rewind: Boolean( config.loop ) && ! canLoop,
			slidesPerGroup: Math.max( 1, Number( config.slidesToScroll ) || 1 ),
			slidesPerView: Math.max( 1, Number( config.mobile ) || 1 ),
			spaceBetween: liveGap,
			speed: Math.max( 100, Number( config.transitionSpeed ) || 500 ),
			watchOverflow: true,
			breakpoints: {
				768: { slidesPerView: Math.max( 1, Number( config.tablet ) || 2 ), spaceBetween: Math.max( 0, Number( config.gapTablet ) || 0 ) },
				1025: { slidesPerView: Math.max( 1, Number( config.desktop ) || 4 ), spaceBetween: Math.max( 0, Number( config.gapDesktop ) || 0 ) }
			}
		};

		if ( config.arrows ) {
			options.navigation = { prevEl: shell.querySelector( '.mps-products-carousel-prev' ), nextEl: shell.querySelector( '.mps-products-carousel-next' ) };
		}
		if ( config.autoplay && ! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			options.autoplay = {
				delay: Math.max( 1000, Number( config.autoplayDelay ) || 4000 ),
				disableOnInteraction: false,
				pauseOnMouseEnter: false !== config.pauseOnHover
			};
		}

		Promise.resolve( new Swiper( viewport, options ) ).then( function ( swiper ) {
			root.mpsSwiper = swiper;
			root.mpsGap = liveGap;
			watchEditorGap( root );
		} );
	}

	function init( scope ) {
		var context = scope && scope.querySelectorAll ? scope : document;
		var widgets = Array.prototype.slice.call( context.querySelectorAll( '.mps' ) );

		if ( context.matches && context.matches( '.mps' ) ) {
			widgets.unshift( context );
		}
		widgets.forEach( function ( root ) {
			initCarousel( root );
			initGridPagination( root );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () { init( document ); } );
	window.addEventListener( 'elementor/frontend/init', function () {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/mim-products-swipe.default', function ( scope ) {
				init( scope && scope[0] ? scope[0] : scope );
			} );
		}
	} );
}( window, document ) );
