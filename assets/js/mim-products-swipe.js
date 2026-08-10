(function () {
	'use strict';

	function MPSCarousel(root) {
		this.root = root;
		this.config = JSON.parse(root.dataset.config || '{}');
		this.tabs = Array.from(root.querySelectorAll('.mps-tab'));
		this.panels = Array.from(root.querySelectorAll('.mps-panel'));
		this.status = root.querySelector('.mps-status');
		this.timer = null;
		this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		this.tabs.forEach(function (tab, index) {
			tab.classList.toggle('is-active', index === 0);
			tab.setAttribute('aria-selected', index === 0 ? 'true' : 'false');
			tab.tabIndex = index === 0 ? 0 : -1;
		});
		this.panels.forEach(function (panel, index) {
			panel.classList.toggle('is-active', index === 0);
			panel.hidden = index !== 0;
		});
		this.bindTabs();
		this.panels.forEach(this.setupPanel.bind(this));
		this.resizeObserver = window.ResizeObserver ? new ResizeObserver(this.refresh.bind(this)) : null;
		if (this.resizeObserver) this.resizeObserver.observe(root);
		else window.addEventListener('resize', this.refresh.bind(this), { passive: true });
		this.refresh();
	}

	MPSCarousel.prototype.values = function () {
		var width = window.innerWidth, c = this.config;
		if (width < 768) return [c.mobile || 1, c.gapMobile || 0];
		if (width < 1025) return [c.tablet || 2, c.gapTablet || 0];
		return [c.desktop || 4, c.gapDesktop || 0];
	};

	MPSCarousel.prototype.bindTabs = function () {
		var self = this;
		this.tabs.forEach(function (tab, index) {
			tab.addEventListener('click', function () { self.activateTab(index); });
			tab.addEventListener('keydown', function (event) {
				var key = event.key, next = index;
				if (key === 'ArrowRight') next = self.config.rtl ? index - 1 : index + 1;
				else if (key === 'ArrowLeft') next = self.config.rtl ? index + 1 : index - 1;
				else if (key === 'Home') next = 0;
				else if (key === 'End') next = self.tabs.length - 1;
				else return;
				event.preventDefault();
				next = (next + self.tabs.length) % self.tabs.length;
				self.activateTab(next);
				self.tabs[next].focus();
			});
		});
	};

	MPSCarousel.prototype.activateTab = function (index) {
		var self = this, selected = this.tabs[index], panel = this.panels[index];
		this.tabs.forEach(function (tab, i) {
			var active = i === index;
			tab.classList.toggle('is-active', active);
			tab.setAttribute('aria-selected', active ? 'true' : 'false');
			tab.tabIndex = active ? 0 : -1;
			self.panels[i].classList.toggle('is-active', active);
			self.panels[i].hidden = !active;
		});
		this.load(panel).then(function () {
			self.refresh();
			self.announce(selected.textContent.trim() + ' selected');
		});
	};

	MPSCarousel.prototype.load = function (panel) {
		if (!panel.dataset.payload || panel.dataset.loaded) return Promise.resolve();
		panel.dataset.loaded = 'loading';
		this.announce((window.MPS_DATA && MPS_DATA.loading) || 'Loading products…');
		var data = new URLSearchParams({ action: 'mps_load_products', nonce: MPS_DATA.nonce, payload: panel.dataset.payload, signature: panel.dataset.signature });
		return fetch(MPS_DATA.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: data.toString() })
			.then(function (response) { if (!response.ok) throw new Error('Request failed'); return response.json(); })
			.then(function (response) {
				if (!response.success) throw new Error('Invalid response');
				panel.querySelector('.mps-track').innerHTML = response.data.html;
				panel.dataset.loaded = 'yes';
				delete panel.dataset.payload;
				delete panel.dataset.signature;
			})
			.catch(function () {
				panel.dataset.loaded = '';
				panel.querySelector('.mps-track').innerHTML = '<div class="mps-error">' + ((window.MPS_DATA && MPS_DATA.error) || 'Products could not be loaded.') + '</div>';
			});
	};

	MPSCarousel.prototype.setupPanel = function (panel) {
		var self = this, startX = null, delta = 0;
		panel._index = 0;
		panel._track = panel.querySelector('.mps-track');
		panel._dots = panel.querySelector('.mps-dots');
		var previous = panel.querySelector('.mps-prev'), next = panel.querySelector('.mps-next');
		if (previous) previous.addEventListener('click', function () { self.move(panel, self.config.rtl ? 1 : -1); });
		if (next) next.addEventListener('click', function () { self.move(panel, self.config.rtl ? -1 : 1); });
		panel._track.addEventListener('pointerdown', function (event) { startX = event.clientX; delta = 0; panel._track.setPointerCapture(event.pointerId); });
		panel._track.addEventListener('pointermove', function (event) { if (startX !== null) delta = event.clientX - startX; });
		panel._track.addEventListener('pointerup', function () { if (Math.abs(delta) > 35) self.move(panel, delta > 0 ? -1 : 1); startX = null; });
		panel.addEventListener('mouseenter', this.stop.bind(this));
		panel.addEventListener('mouseleave', function () { self.auto(panel); });
		panel.addEventListener('focusin', this.stop.bind(this));
		panel.addEventListener('focusout', function () { self.auto(panel); });
		document.addEventListener('visibilitychange', function () { if (document.hidden) self.stop(); else self.auto(panel); });
	};

	MPSCarousel.prototype.slides = function (panel) { return Array.from(panel.querySelectorAll('.mps-slide')); };
	MPSCarousel.prototype.max = function (panel) { return Math.max(0, this.slides(panel).length - this.values()[0]); };
	MPSCarousel.prototype.move = function (panel, amount) {
		var max = this.max(panel);
		panel._index += amount;
		if (this.config.loop && max > 0) { if (panel._index > max) panel._index = 0; if (panel._index < 0) panel._index = max; }
		else panel._index = Math.max(0, Math.min(max, panel._index));
		this.draw(panel);
		this.announce('Slide ' + (panel._index + 1) + ' of ' + (max + 1));
	};

	MPSCarousel.prototype.draw = function (panel) {
		var values = this.values(), items = values[0], gap = values[1], slides = this.slides(panel);
		this.root.style.setProperty('--mps-items', items);
		this.root.style.setProperty('--mps-gap', gap + 'px');
		var viewport = panel.querySelector('.mps-viewport'), width = viewport ? viewport.clientWidth : 0;
		var slideWidth = width > 0 ? Math.max(0, (width - (items - 1) * gap) / items) : 0;
		var step = slideWidth + gap;
		slides.forEach(function (slide) {
			slide.style.flexBasis = slideWidth + 'px';
			slide.style.width = slideWidth + 'px';
			slide.style.maxWidth = slideWidth + 'px';
		});
		var direction = this.config.rtl ? 1 : -1;
		panel._track.style.transform = 'translate3d(' + (direction * panel._index * step) + 'px,0,0)';
		slides.forEach(function (slide, i) { slide.setAttribute('aria-label', (i + 1) + ' of ' + slides.length); });
		var previous = panel.querySelector('.mps-prev'), next = panel.querySelector('.mps-next'), max = this.max(panel);
		if (previous) previous.hidden = max === 0;
		if (next) next.hidden = max === 0;
		if (panel._dots) panel._dots.hidden = max === 0;
		if (previous) previous.disabled = !this.config.loop && panel._index === 0;
		if (next) next.disabled = !this.config.loop && panel._index === max;
		this.dots(panel, max);
	};

	MPSCarousel.prototype.dots = function (panel, max) {
		if (!panel._dots) return;
		var self = this;
		panel._dots.innerHTML = '';
		for (var i = 0; i <= max; i++) {
			(function (index) {
				var dot = document.createElement('button');
				dot.className = 'mps-dot' + (index === panel._index ? ' is-active' : '');
				dot.type = 'button'; dot.setAttribute('aria-label', 'Go to slide ' + (index + 1));
				dot.setAttribute('aria-current', index === panel._index ? 'true' : 'false');
				dot.addEventListener('click', function () { panel._index = index; self.draw(panel); });
				panel._dots.appendChild(dot);
			})(i);
		}
	};

	MPSCarousel.prototype.refresh = function () {
		var self = this;
		this.panels.forEach(function (panel) { panel._index = Math.min(panel._index, self.max(panel)); self.draw(panel); });
		var active = this.root.querySelector('.mps-panel.is-active');
		if (active) this.auto(active);
	};
	MPSCarousel.prototype.stop = function () { if (this.timer) { clearInterval(this.timer); this.timer = null; } };
	MPSCarousel.prototype.auto = function (panel) { this.stop(); if (!this.config.autoplay || this.reducedMotion || !panel.classList.contains('is-active')) return; var self = this; this.timer = setInterval(function () { self.move(panel, 1); }, this.config.speed || 4000); };
	MPSCarousel.prototype.announce = function (message) { if (this.status) this.status.textContent = message; };

	function init(scope) { (scope || document).querySelectorAll('.mps:not([data-mps-ready])').forEach(function (element) { element.dataset.mpsReady = '1'; new MPSCarousel(element); }); }
	document.addEventListener('DOMContentLoaded', function () { init(document); });
	window.addEventListener('elementor/frontend/init', function () { if (window.elementorFrontend) elementorFrontend.hooks.addAction('frontend/element_ready/mim-products-swipe.default', function (scope) { init(scope[0] || scope); }); });
})();
