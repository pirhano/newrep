/* Rhomija Dentaire — navigation, next free slot, conversion tracking. No dependencies. */
(function () {
	'use strict';

	var header = document.querySelector('[data-header]');
	var nav = document.querySelector('[data-nav]');
	var toggle = document.querySelector('[data-nav-toggle]');

	/* Sticky header shadow */
	if (header) {
		var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/* Mobile menu */
	function setMenu(open) {
		if (!nav || !toggle) return;
		nav.classList.toggle('is-open', open);
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		document.body.classList.toggle('nav-open', open);
	}
	if (toggle) {
		toggle.addEventListener('click', function () {
			setMenu(toggle.getAttribute('aria-expanded') !== 'true');
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && nav.classList.contains('is-open')) {
				setMenu(false);
				toggle.focus();
			}
		});
		if (nav) {
			nav.addEventListener('click', function (e) {
				var a = e.target.closest('a');
				if (a && a.getAttribute('href') && a.getAttribute('href').indexOf('#') !== -1) setMenu(false);
			});
		}
	}

	/* Sub-menus */
	Array.prototype.forEach.call(document.querySelectorAll('.submenu-toggle'), function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			var li = btn.closest('li');
			var open = !li.classList.contains('is-open');
			li.classList.toggle('is-open', open);
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	});
	document.addEventListener('click', function (e) {
		if (e.target.closest('.menu-item-has-children')) return;
		Array.prototype.forEach.call(document.querySelectorAll('.menu-item-has-children.is-open'), function (li) {
			if (window.matchMedia('(min-width: 1021px)').matches) {
				li.classList.remove('is-open');
				var b = li.querySelector('.submenu-toggle');
				if (b) b.setAttribute('aria-expanded', 'false');
			}
		});
	});

	/* Next free slot (home hero) */
	var slot = document.querySelector('[data-next-slot]');
	if (slot && window.RH && window.RH.nextSlotApi && window.fetch) {
		fetch(window.RH.nextSlotApi, { credentials: 'omit', cache: 'no-store' })
			.then(function (r) { return r.ok ? r.json() : null; })
			.then(function (d) {
				if (!d || !d.date) return;
				slot.querySelector('[data-next-slot-label]').textContent = d.label;
				slot.hidden = false;
			})
			.catch(function () {});
	}

	/* Conversion tracking: works with Google Site Kit (gtag), GTM (dataLayer) and Meta Pixel. */
	document.addEventListener('click', function (e) {
		var el = e.target.closest('[data-track]');
		if (!el) return;
		var name = { call: 'click_call', whatsapp: 'click_whatsapp', directions: 'click_directions', cta_booking: 'click_booking', review: 'click_review' }[el.getAttribute('data-track')];
		if (!name) return;
		try {
			if (typeof window.gtag === 'function') window.gtag('event', name, { link_url: el.href });
			if (Array.isArray(window.dataLayer)) window.dataLayer.push({ event: name, link_url: el.href });
			if (typeof window.fbq === 'function' && (name === 'click_call' || name === 'click_whatsapp')) window.fbq('track', 'Contact');
		} catch (err) { /* never block navigation */ }
	});
})();
