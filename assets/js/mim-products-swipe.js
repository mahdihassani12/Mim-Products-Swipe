(function () {
  "use strict";

  function initCarousel(root) {
    var config = JSON.parse(root.dataset.config || "{}");
    var viewport = root.querySelector(".mps-viewport");
    if (config.layout === "grid" || !viewport || root.mpsSwiper) return;
    if (!window.elementorFrontend || !elementorFrontend.utils || !elementorFrontend.utils.swiper) return;

    var options = {
      a11y: { enabled: true },
      slidesPerView: Math.max(1, config.mobile || 1),
      spaceBetween: Math.max(0, config.gapMobile || 0),
      speed: 500,
      loop: Boolean(config.loop),
      allowTouchMove: config.draggable !== false,
      grabCursor: config.draggable !== false,
      watchOverflow: true,
      breakpoints: {
        768: { slidesPerView: Math.max(1, config.tablet || 2), spaceBetween: Math.max(0, config.gapTablet || 0) },
        1025: { slidesPerView: Math.max(1, config.desktop || 4), spaceBetween: Math.max(0, config.gapDesktop || 0) }
      }
    };
    if (config.arrows) options.navigation = { prevEl: root.querySelector(".mps-prev"), nextEl: root.querySelector(".mps-next") };
    if (config.dots) options.pagination = { el: root.querySelector(".mps-dots"), clickable: true };
    if (config.autoplay) options.autoplay = { delay: Math.max(1000, config.speed || 4000), disableOnInteraction: false, pauseOnMouseEnter: true };

    new elementorFrontend.utils.swiper(viewport, options).then(function (swiper) {
      root.mpsSwiper = swiper;
    });
  }

  function init(scope) {
    var context = scope || document;
    var widgets = Array.prototype.slice.call(context.querySelectorAll(".mps:not([data-mps-ready])"));
    if (context.matches && context.matches(".mps:not([data-mps-ready])")) widgets.unshift(context);
    widgets.forEach(function (root) {
      root.dataset.mpsReady = "1";
      initCarousel(root);
    });
  }

  document.addEventListener("DOMContentLoaded", function () { init(document); });
  window.addEventListener("elementor/frontend/init", function () {
    elementorFrontend.hooks.addAction("frontend/element_ready/mim-products-swipe.default", function ($scope) {
      init($scope[0] || $scope);
    });
  });
}());
