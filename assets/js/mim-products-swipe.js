(function () {
  "use strict";

  function ProductsWidget(root) {
    this.root = root;
    this.config = JSON.parse(root.dataset.config || "{}");
    this.tabs = Array.prototype.slice.call(root.querySelectorAll(".mps-tab"));
    this.panels = Array.prototype.slice.call(root.querySelectorAll(".mps-panel"));
    this.status = root.querySelector(".mps-status");
    this.bindTabs();
    this.activateTab(0, false);
  }

  ProductsWidget.prototype.announce = function (message) {
    if (this.status) this.status.textContent = message;
  };

  ProductsWidget.prototype.bindTabs = function () {
    var self = this;
    this.tabs.forEach(function (tab, index) {
      tab.addEventListener("click", function () { self.activateTab(index, true); });
      tab.addEventListener("keydown", function (event) {
        var next = index;
        if (event.key === "ArrowRight") next += self.config.rtl ? -1 : 1;
        else if (event.key === "ArrowLeft") next += self.config.rtl ? 1 : -1;
        else if (event.key === "Home") next = 0;
        else if (event.key === "End") next = self.tabs.length - 1;
        else return;
        event.preventDefault();
        next = (next + self.tabs.length) % self.tabs.length;
        self.activateTab(next, true);
        self.tabs[next].focus();
      });
    });
  };

  ProductsWidget.prototype.activateTab = function (index, announce) {
    var self = this;
    this.tabs.forEach(function (tab, tabIndex) {
      var active = tabIndex === index;
      tab.classList.toggle("is-active", active);
      tab.setAttribute("aria-selected", active ? "true" : "false");
      tab.tabIndex = active ? 0 : -1;
      self.panels[tabIndex].classList.toggle("is-active", active);
      self.panels[tabIndex].hidden = !active;
    });
    this.load(this.panels[index]).then(function () {
      return self.initCarousel(self.panels[index]);
    }).then(function () {
      if (announce) self.announce(self.tabs[index].textContent.trim() + " selected");
    });
  };

  ProductsWidget.prototype.initCarousel = function (panel) {
    var viewport = panel.querySelector(".mps-viewport");
    if (this.config.layout === "grid" || !viewport || panel.mpsSwiper) return Promise.resolve();
    if (!window.elementorFrontend || !elementorFrontend.utils || !elementorFrontend.utils.swiper) return Promise.resolve();

    var config = this.config;
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
    if (config.arrows) options.navigation = { prevEl: panel.querySelector(".mps-prev"), nextEl: panel.querySelector(".mps-next") };
    if (config.dots) options.pagination = { el: panel.querySelector(".mps-dots"), clickable: true };
    if (config.autoplay) options.autoplay = { delay: Math.max(1000, config.speed || 4000), disableOnInteraction: false, pauseOnMouseEnter: true };

    return new elementorFrontend.utils.swiper(viewport, options).then(function (swiper) {
      panel.mpsSwiper = swiper;
    });
  };

  ProductsWidget.prototype.load = function (panel) {
    var self = this;
    if (!panel.dataset.payload || panel.dataset.loaded) return Promise.resolve();
    panel.dataset.loaded = "loading";
    this.announce((window.MPS_DATA && window.MPS_DATA.loading) || "Loading products…");
    return window.fetch(window.MPS_DATA.ajaxUrl, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body: new URLSearchParams({ action: "mps_load_products", nonce: window.MPS_DATA.nonce, payload: panel.dataset.payload, signature: panel.dataset.signature }).toString()
    }).then(function (response) {
      if (!response.ok) throw new Error("Request failed");
      return response.json();
    }).then(function (response) {
      if (!response.success) throw new Error("Invalid response");
      panel.querySelector(".mps-track").innerHTML = response.data.html;
      panel.dataset.loaded = "yes";
      delete panel.dataset.payload;
      delete panel.dataset.signature;
    }).catch(function () {
      panel.dataset.loaded = "";
      panel.querySelector(".mps-track").innerHTML = '<div class="mps-error">' + ((window.MPS_DATA && window.MPS_DATA.error) || "Products could not be loaded.") + "</div>";
      self.announce((window.MPS_DATA && window.MPS_DATA.error) || "Products could not be loaded.");
    });
  };

  function init(scope) {
    var context = scope || document;
    var widgets = Array.prototype.slice.call(context.querySelectorAll(".mps:not([data-mps-ready])"));
    if (context.matches && context.matches(".mps:not([data-mps-ready])")) widgets.unshift(context);
    widgets.forEach(function (root) {
      root.dataset.mpsReady = "1";
      root.mpsInstance = new ProductsWidget(root);
    });
  }

  document.addEventListener("DOMContentLoaded", function () { init(document); });
  window.addEventListener("elementor/frontend/init", function () {
    elementorFrontend.hooks.addAction("frontend/element_ready/mim-products-swipe.default", function ($scope) {
      init($scope[0] || $scope);
    });
  });
}());
