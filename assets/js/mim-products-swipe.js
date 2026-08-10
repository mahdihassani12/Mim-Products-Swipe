(function ($) {
  "use strict";

  function MPSSlider(root) {
    this.root = root;
    this.config = JSON.parse(root.dataset.config || "{}");
    this.tabs = Array.from(root.querySelectorAll(".mps-tab"));
    this.panels = Array.from(root.querySelectorAll(".mps-panel"));
    this.status = root.querySelector(".mps-status");
    this.timer = null;
    root.classList.toggle("mps-swipe-disabled", this.config.draggable === false);
    this.bindTabs();
    this.activateTab(0, false);
  }

  MPSSlider.prototype.items = function () {
    if (window.matchMedia("(min-width: 1025px)").matches) return Math.max(1, this.config.desktop || 4);
    if (window.matchMedia("(min-width: 768px)").matches) return Math.max(1, this.config.tablet || 2);
    return Math.max(1, this.config.mobile || 1);
  };

  MPSSlider.prototype.gap = function () {
    if (window.matchMedia("(min-width: 1025px)").matches) return Math.max(0, this.config.gapDesktop || 0);
    if (window.matchMedia("(min-width: 768px)").matches) return Math.max(0, this.config.gapTablet || 0);
    return Math.max(0, this.config.gapMobile || 0);
  };

  MPSSlider.prototype.initPanel = function (panel) {
    var self = this,
      viewport = panel.querySelector(".mps-viewport"),
      previous = panel.querySelector(".mps-prev"),
      next = panel.querySelector(".mps-next");
    if (!viewport || panel.dataset.sliderReady) return;
    panel.dataset.sliderReady = "1";

    if (previous) previous.addEventListener("click", function () { self.move(panel, -1); });
    if (next) next.addEventListener("click", function () { self.move(panel, 1); });
    viewport.addEventListener("scroll", function () {
      window.clearTimeout(panel.mpsScrollTimer);
      panel.mpsScrollTimer = window.setTimeout(function () { self.update(panel, true); }, 80);
    }, { passive: true });
    viewport.addEventListener("keydown", function (event) {
      if (event.key !== "ArrowLeft" && event.key !== "ArrowRight") return;
      event.preventDefault();
      self.move(panel, event.key === "ArrowRight" ? 1 : -1);
    });
    viewport.addEventListener("mouseenter", function () { self.stopAutoplay(); });
    viewport.addEventListener("mouseleave", function () { self.startAutoplay(panel); });
    viewport.addEventListener("focusin", function () { self.stopAutoplay(); });
    viewport.addEventListener("focusout", function () { self.startAutoplay(panel); });
    this.bindMouseSwipe(viewport);
    this.refreshPanel(panel);
  };

  MPSSlider.prototype.bindMouseSwipe = function (viewport) {
    if (this.config.draggable === false) return;
    var startX = 0, startScroll = 0, dragging = false;
    viewport.addEventListener("pointerdown", function (event) {
      if (event.pointerType === "touch" || event.button !== 0) return;
      dragging = true;
      startX = event.clientX;
      startScroll = viewport.scrollLeft;
      viewport.classList.add("is-dragging");
      viewport.setPointerCapture(event.pointerId);
    });
    viewport.addEventListener("pointermove", function (event) {
      if (!dragging) return;
      viewport.scrollLeft = startScroll - (event.clientX - startX);
    });
    function finish(event) {
      if (!dragging) return;
      dragging = false;
      viewport.classList.remove("is-dragging");
      if (viewport.hasPointerCapture(event.pointerId)) viewport.releasePointerCapture(event.pointerId);
    }
    viewport.addEventListener("pointerup", finish);
    viewport.addEventListener("pointercancel", finish);
  };

  MPSSlider.prototype.refreshPanel = function (panel) {
    var viewport = panel.querySelector(".mps-viewport"), track = panel.querySelector(".mps-track");
    if (!viewport || !track) return;
    track.style.setProperty("--mps-items", this.items());
    track.style.setProperty("--mps-gap", this.gap() + "px");
    this.buildDots(panel);
    this.update(panel, false);
    this.startAutoplay(panel);
  };

  MPSSlider.prototype.pageCount = function (panel) {
    return Math.max(1, Math.ceil(panel.querySelectorAll(".mps-slide").length / this.items()));
  };

  MPSSlider.prototype.currentPage = function (panel) {
    var viewport = panel.querySelector(".mps-viewport"), max = viewport.scrollWidth - viewport.clientWidth;
    if (max <= 1) return 0;
    return Math.min(this.pageCount(panel) - 1, Math.round(Math.abs(viewport.scrollLeft) / (viewport.clientWidth + this.gap())));
  };

  MPSSlider.prototype.goTo = function (panel, page, smooth) {
    var pages = this.pageCount(panel), viewport = panel.querySelector(".mps-viewport");
    page = Math.max(0, Math.min(pages - 1, page));
    viewport.scrollTo({ left: (this.config.rtl ? -1 : 1) * page * (viewport.clientWidth + this.gap()), behavior: smooth ? "smooth" : "auto" });
  };

  MPSSlider.prototype.move = function (panel, direction) {
    var page = this.currentPage(panel), pages = this.pageCount(panel), next = page + direction;
    if (this.config.loop && pages > 1) next = (next + pages) % pages;
    this.goTo(panel, next, true);
  };

  MPSSlider.prototype.buildDots = function (panel) {
    var self = this, container = panel.querySelector(".mps-dots"), pages = this.pageCount(panel);
    if (!container) return;
    container.innerHTML = "";
    for (var i = 0; i < pages; i += 1) {
      (function (page) {
        var dot = document.createElement("button");
        dot.type = "button";
        dot.className = "mps-dot";
        dot.setAttribute("aria-label", "Go to slide group " + (page + 1));
        dot.addEventListener("click", function () { self.goTo(panel, page, true); });
        container.appendChild(dot);
      }(i));
    }
  };

  MPSSlider.prototype.update = function (panel, announce) {
    var page = this.currentPage(panel), pages = this.pageCount(panel), self = this;
    panel.querySelectorAll(".mps-dot").forEach(function (dot, index) {
      dot.classList.toggle("is-active", index === page);
      dot.setAttribute("aria-current", index === page ? "true" : "false");
    });
    panel.querySelectorAll(".mps-slide").forEach(function (slide, index) {
      slide.setAttribute("aria-label", (index + 1) + " of " + panel.querySelectorAll(".mps-slide").length);
    });
    panel.querySelectorAll(".mps-arrow, .mps-dots").forEach(function (control) { control.hidden = pages <= 1; });
    var previous = panel.querySelector(".mps-prev"), next = panel.querySelector(".mps-next");
    if (!this.config.loop) {
      if (previous) previous.disabled = page === 0;
      if (next) next.disabled = page === pages - 1;
    }
    if (announce) self.announce("Slide group " + (page + 1) + " of " + pages);
  };

  MPSSlider.prototype.startAutoplay = function (panel) {
    var self = this;
    this.stopAutoplay();
    if (!this.config.autoplay || this.pageCount(panel) <= 1 || window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
    this.timer = window.setInterval(function () { self.move(panel, 1); }, this.config.speed || 4000);
  };
  MPSSlider.prototype.stopAutoplay = function () { window.clearInterval(this.timer); this.timer = null; };

  MPSSlider.prototype.bindTabs = function () {
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
        self.activateTab(next, true); self.tabs[next].focus();
      });
    });
  };

  MPSSlider.prototype.activateTab = function (index, announce) {
    var self = this, selected = this.tabs[index], panel = this.panels[index];
    this.stopAutoplay();
    this.tabs.forEach(function (tab, tabIndex) {
      var active = tabIndex === index;
      tab.classList.toggle("is-active", active); tab.setAttribute("aria-selected", active ? "true" : "false"); tab.tabIndex = active ? 0 : -1;
      self.panels[tabIndex].classList.toggle("is-active", active); self.panels[tabIndex].hidden = !active;
    });
    this.load(panel).then(function () { self.initPanel(panel); self.refreshPanel(panel); if (announce) self.announce(selected.textContent.trim() + " selected"); });
  };

  MPSSlider.prototype.load = function (panel) {
    var self = this;
    if (!panel.dataset.payload || panel.dataset.loaded) return Promise.resolve();
    panel.dataset.loaded = "loading"; this.announce((window.MPS_DATA && MPS_DATA.loading) || "Loading products…");
    return fetch(MPS_DATA.ajaxUrl, { method: "POST", credentials: "same-origin", headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" }, body: new URLSearchParams({ action: "mps_load_products", nonce: MPS_DATA.nonce, payload: panel.dataset.payload, signature: panel.dataset.signature }).toString() })
      .then(function (response) { if (!response.ok) throw new Error("Request failed"); return response.json(); })
      .then(function (response) { if (!response.success) throw new Error("Invalid response"); panel.querySelector(".mps-track").innerHTML = response.data.html; panel.dataset.loaded = "yes"; delete panel.dataset.payload; delete panel.dataset.signature; })
      .catch(function () { panel.dataset.loaded = ""; panel.querySelector(".mps-track").innerHTML = '<div class="mps-error">' + ((window.MPS_DATA && MPS_DATA.error) || "Products could not be loaded.") + "</div>"; self.refreshPanel(panel); });
  };

  MPSSlider.prototype.announce = function (message) { if (this.status) this.status.textContent = message; };

  function init(scope) {
    $(scope || document).find(".mps:not([data-mps-ready])").addBack(".mps:not([data-mps-ready])").each(function () { this.dataset.mpsReady = "1"; this.mpsInstance = new MPSSlider(this); });
  }
  $(function () { init(document); });
  $(window).on("resize.mps", function () { document.querySelectorAll(".mps-panel.is-active").forEach(function (panel) { var root = panel.closest(".mps"); if (root && root.mpsInstance) root.mpsInstance.refreshPanel(panel); }); });
  $(window).on("elementor/frontend/init", function () { if (window.elementorFrontend) elementorFrontend.hooks.addAction("frontend/element_ready/mim-products-swipe.default", function ($scope) { init($scope[0] || $scope); }); });
})(jQuery);
