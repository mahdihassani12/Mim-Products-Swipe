(function ($) {
  "use strict";

  function MPSCarousel(root) {
    this.root = root;
    this.config = JSON.parse(root.dataset.config || "{}");
    this.tabs = Array.from(root.querySelectorAll(".mps-tab"));
    this.panels = Array.from(root.querySelectorAll(".mps-panel"));
    this.status = root.querySelector(".mps-status");
    this.bindTabs();
    this.activateTab(0, false);
  }

  MPSCarousel.prototype.options = function (panel) {
    var c = this.config;
    return {
      items: Math.max(1, c.mobile || 1),
      margin: Math.max(0, c.gapMobile || 0),
      nav: false,
      dots: !!c.dots,
      dotsContainer: panel.querySelector(".mps-dots") || false,
      mouseDrag: c.draggable !== false,
      touchDrag: c.draggable !== false,
      pullDrag: c.draggable !== false,
      loop: !!c.loop,
      rtl: !!c.rtl,
      autoplay: !!c.autoplay,
      autoplayTimeout: c.speed || 4000,
      autoplayHoverPause: true,
      smartSpeed: 350,
      responsiveRefreshRate: 100,
      responsive: {
        0: {
          items: Math.max(1, c.mobile || 1),
          margin: Math.max(0, c.gapMobile || 0),
        },
        768: {
          items: Math.max(1, c.tablet || 2),
          margin: Math.max(0, c.gapTablet || 0),
        },
        1025: {
          items: Math.max(1, c.desktop || 4),
          margin: Math.max(0, c.gapDesktop || 0),
        },
      },
      onInitialized: this.updateAccessibility.bind(this),
      onRefreshed: this.updateAccessibility.bind(this),
      onChanged: this.updateAccessibility.bind(this),
    };
  };

  MPSCarousel.prototype.initPanel = function (panel) {
    var self = this,
      $track = $(panel).find(".mps-track");
    if (!$track.length || $track.hasClass("owl-loaded")) return;
    $track.owlCarousel(this.options(panel));
    $(panel)
      .find(".mps-prev")
      .off("click.mps")
      .on("click.mps", function () {
        $track.trigger("prev.owl.carousel");
      });
    $(panel)
      .find(".mps-next")
      .off("click.mps")
      .on("click.mps", function () {
        $track.trigger("next.owl.carousel");
      });
    $track.on("changed.owl.carousel.mps", function (event) {
      var current = event.item ? event.item.index + 1 : 1;
      self.announce("Slide " + current + " of " + event.item.count);
    });
  };

  MPSCarousel.prototype.refreshPanel = function (panel) {
    var $track = $(panel).find(".mps-track");
    if ($track.hasClass("owl-loaded")) {
      $track.trigger("refresh.owl.carousel");
    } else {
      this.initPanel(panel);
    }
  };

  MPSCarousel.prototype.updateAccessibility = function (event) {
    if (!event || !event.currentTarget) return;
    var slides = event.currentTarget.querySelectorAll(".mps-slide"),
      count = slides.length,
      panel = event.currentTarget.closest(".mps-panel"),
      dots = panel ? panel.querySelectorAll(".owl-dot") : [],
      pages = event.page ? event.page.count : 0;
    Array.prototype.forEach.call(slides, function (slide, index) {
      slide.setAttribute("aria-label", index + 1 + " of " + count);
    });
    Array.prototype.forEach.call(dots, function (dot) {
      dot.classList.add("mps-dot");
      dot.classList.toggle("is-active", dot.classList.contains("active"));
    });
    if (panel) {
      Array.prototype.forEach.call(
        panel.querySelectorAll(".mps-arrow, .mps-dots"),
        function (control) {
          control.hidden = pages <= 1;
        },
      );
    }
  };

  MPSCarousel.prototype.bindTabs = function () {
    var self = this;
    this.tabs.forEach(function (tab, index) {
      tab.addEventListener("click", function () {
        self.activateTab(index, true);
      });
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

  MPSCarousel.prototype.activateTab = function (index, announce) {
    var self = this,
      selected = this.tabs[index],
      panel = this.panels[index];
    this.tabs.forEach(function (tab, tabIndex) {
      var active = tabIndex === index;
      tab.classList.toggle("is-active", active);
      tab.setAttribute("aria-selected", active ? "true" : "false");
      tab.tabIndex = active ? 0 : -1;
      self.panels[tabIndex].classList.toggle("is-active", active);
      self.panels[tabIndex].hidden = !active;
    });
    this.load(panel).then(function () {
      self.refreshPanel(panel);
      if (announce) self.announce(selected.textContent.trim() + " selected");
    });
  };

  MPSCarousel.prototype.load = function (panel) {
    var self = this;
    if (!panel.dataset.payload || panel.dataset.loaded) return Promise.resolve();
    panel.dataset.loaded = "loading";
    this.announce((window.MPS_DATA && MPS_DATA.loading) || "Loading products…");
    return fetch(MPS_DATA.ajaxUrl, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body: new URLSearchParams({
        action: "mps_load_products",
        nonce: MPS_DATA.nonce,
        payload: panel.dataset.payload,
        signature: panel.dataset.signature,
      }).toString(),
    })
      .then(function (response) {
        if (!response.ok) throw new Error("Request failed");
        return response.json();
      })
      .then(function (response) {
        if (!response.success) throw new Error("Invalid response");
        panel.querySelector(".mps-track").innerHTML = response.data.html;
        panel.dataset.loaded = "yes";
        delete panel.dataset.payload;
        delete panel.dataset.signature;
      })
      .catch(function () {
        panel.dataset.loaded = "";
        panel.querySelector(".mps-track").innerHTML =
          '<div class="mps-error">' +
          ((window.MPS_DATA && MPS_DATA.error) || "Products could not be loaded.") +
          "</div>";
        self.refreshPanel(panel);
      });
  };

  MPSCarousel.prototype.announce = function (message) {
    if (this.status) this.status.textContent = message;
  };

  function init(scope) {
    $(scope || document)
      .find(".mps:not([data-mps-ready])")
      .addBack(".mps:not([data-mps-ready])")
      .each(function () {
        this.dataset.mpsReady = "1";
        new MPSCarousel(this);
      });
  }

  $(function () {
    init(document);
  });
  $(window).on("elementor/frontend/init", function () {
    if (window.elementorFrontend) {
      elementorFrontend.hooks.addAction(
        "frontend/element_ready/mim-products-swipe.default",
        function ($scope) {
          init($scope[0] || $scope);
        },
      );
    }
  });
})(jQuery);
