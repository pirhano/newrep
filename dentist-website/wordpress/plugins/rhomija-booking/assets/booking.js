/* Rhomija — prise de rendez-vous. No dependencies. */
(function () {
	'use strict';

	var C = window.RB_CONFIG;
	if (!C) return;

	function $(root, sel) { return root.querySelector(sel); }
	function $$(root, sel) { return Array.prototype.slice.call(root.querySelectorAll(sel)); }
	function pad(n) { return (n < 10 ? '0' : '') + n; }
	function ymd(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
	function parseYmd(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
	function esc(s) {
		return String(s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function api(path, opts) {
		return fetch(C.api.replace(/\/$/, '') + path, Object.assign({ credentials: 'omit', cache: 'no-store' }, opts || {}))
			.then(function (r) {
				return r.json().catch(function () { return {}; }).then(function (body) {
					return { status: r.status, body: body };
				});
			});
	}

	function track(name, params) {
		try {
			if (typeof window.gtag === 'function') window.gtag('event', name, params || {});
			if (Array.isArray(window.dataLayer)) window.dataLayer.push(Object.assign({ event: name }, params || {}));
			if (typeof window.fbq === 'function' && name === 'generate_lead') window.fbq('track', 'Lead');
		} catch (e) { /* analytics must never break booking */ }
	}

	function Booking(root) {
		this.root = root;
		this.form = $(root, '.rb-form');
		this.state = { service: '', serviceLabel: '', date: '', time: '', step: 1 };
		this.startedAt = Date.now();
		var today = parseYmd(C.today);
		this.month = new Date(today.getFullYear(), today.getMonth(), 1);
		this.monthCache = {};
		this.bind();
		this.preselect();
	}

	Booking.prototype.bind = function () {
		var self = this;

		$$(this.form, 'input[name="service"]').forEach(function (input) {
			input.addEventListener('change', function () {
				self.state.service = input.value;
				self.state.serviceLabel = input.getAttribute('data-label');
				self.updateButtons();
			});
		});

		$$(this.form, '[data-next]').forEach(function (b) {
			b.addEventListener('click', function () { self.go(self.state.step + 1); });
		});
		$$(this.form, '[data-back]').forEach(function (b) {
			b.addEventListener('click', function () { self.go(self.state.step - 1); });
		});

		$(this.form, '[data-cal-prev]').addEventListener('click', function () {
			self.month = new Date(self.month.getFullYear(), self.month.getMonth() - 1, 1);
			self.renderMonth();
		});
		$(this.form, '[data-cal-next]').addEventListener('click', function () {
			self.month = new Date(self.month.getFullYear(), self.month.getMonth() + 1, 1);
			self.renderMonth();
		});

		this.form.addEventListener('submit', function (e) {
			e.preventDefault();
			self.submit();
		});
	};

	/* A service can be pre-selected with ?soin=<slug> (links from service pages). */
	Booking.prototype.preselect = function () {
		var m = /[?&]soin=([^&#]+)/.exec(window.location.search);
		if (!m) return;
		var slug = decodeURIComponent(m[1]);
		var input = $$(this.form, 'input[name="service"]').filter(function (i) { return i.value === slug; })[0];
		if (input) {
			input.checked = true;
			input.dispatchEvent(new Event('change'));
		}
	};

	Booking.prototype.updateButtons = function () {
		var s = this.state;
		var b1 = $(this.form, '[data-step="1"] [data-next]');
		var b2 = $(this.form, '[data-step="2"] [data-next]');
		b1.disabled = !s.service;
		b2.disabled = !(s.date && s.time);
	};

	Booking.prototype.go = function (step) {
		if (step < 1) return;
		var self = this;
		this.hideError();
		this.state.step = step;
		$$(this.form, '.rb-step').forEach(function (sec) {
			sec.hidden = sec.getAttribute('data-step') !== String(step);
		});
		$$(this.root, '[data-progress]').forEach(function (li) {
			var n = +li.getAttribute('data-progress');
			li.classList.toggle('is-current', n === step);
			li.classList.toggle('is-done', step === 'done' || n < step);
		});
		if (step === 2 && !this.calendarReady) {
			this.calendarReady = true;
			this.renderMonth();
		}
		if (step === 3) this.renderSummary($(this.form, '[data-summary]'), true);
		var title = $(this.form, '[data-step="' + step + '"] .rb-title');
		if (title) {
			title.focus({ preventScroll: true });
			var top = this.root.getBoundingClientRect().top + window.pageYOffset - 110;
			if (Math.abs(window.pageYOffset - top) > 40) window.scrollTo({ top: top, behavior: 'smooth' });
		}
		if (step === 2) track('booking_step', { step: 'date', service: self.state.serviceLabel });
	};

	Booking.prototype.loadMonth = function (key) {
		var self = this;
		if (this.monthCache[key]) return Promise.resolve(this.monthCache[key]);
		return api('/month?month=' + key).then(function (res) {
			if (res.status !== 200) throw new Error('month');
			self.monthCache[key] = res.body;
			return res.body;
		});
	};

	Booking.prototype.renderMonth = function () {
		var self = this;
		var m = this.month;
		var key = m.getFullYear() + '-' + pad(m.getMonth() + 1);
		var grid = $(this.form, '[data-cal-grid]');
		var label = C.months[m.getMonth()] + ' ' + m.getFullYear();
		$(this.form, '[data-cal-label]').textContent = label.charAt(0).toUpperCase() + label.slice(1);
		grid.setAttribute('aria-busy', 'true');
		grid.innerHTML = '<p class="rb-muted rb-cal__loading">' + esc(C.i18n.loading) + '</p>';

		this.loadMonth(key).then(function (data) {
			var first = parseYmd(data.first);
			var last = parseYmd(data.last);
			$(self.form, '[data-cal-prev]').disabled = new Date(m.getFullYear(), m.getMonth(), 0) < first;
			$(self.form, '[data-cal-next]').disabled = new Date(m.getFullYear(), m.getMonth() + 1, 1) > last;

			var html = '<div class="rb-cal__row rb-cal__row--head" role="row">';
			C.weekdays.forEach(function (w) { html += '<span role="columnheader">' + esc(w) + '</span>'; });
			html += '</div><div class="rb-cal__days">';
			var offset = (new Date(m.getFullYear(), m.getMonth(), 1).getDay() + 6) % 7;
			for (var i = 0; i < offset; i++) html += '<span class="rb-cal__pad"></span>';
			var daysIn = new Date(m.getFullYear(), m.getMonth() + 1, 0).getDate();
			var firstAvailable = null;
			for (var d = 1; d <= daysIn; d++) {
				var date = new Date(m.getFullYear(), m.getMonth(), d);
				var id = ymd(date);
				var free = data.days[id] || 0;
				var inWindow = date >= first && date <= last;
				var cls = 'rb-cal__day' + (free ? ' has-slots' : '') + (id === self.state.date ? ' is-selected' : '') + (id === C.today ? ' is-today' : '');
				var aria = date.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' }) +
					' — ' + (free ? free + ' ' + C.i18n.free : (inWindow ? C.i18n.full : C.i18n.closed));
				html += '<button type="button" class="' + cls + '" data-day="' + id + '"' + (free ? '' : ' disabled') +
					' aria-pressed="' + (id === self.state.date) + '" aria-label="' + esc(aria) + '">' + d + '</button>';
				if (free && !firstAvailable) firstAvailable = id;
			}
			html += '</div>';
			grid.innerHTML = html;
			grid.removeAttribute('aria-busy');

			$$(grid, '[data-day]').forEach(function (btn) {
				btn.addEventListener('click', function () { self.pickDay(btn.getAttribute('data-day')); });
			});

			// Jump straight to the first month with availability, and pre-open its first free day.
			if (!firstAvailable && !self.autoAdvanced && !$(self.form, '[data-cal-next]').disabled) {
				self.autoAdvanced = true;
				self.month = new Date(m.getFullYear(), m.getMonth() + 1, 1);
				return self.renderMonth();
			}
			self.autoAdvanced = true;
			if (!self.state.date && firstAvailable) self.pickDay(firstAvailable);
		}).catch(function () {
			grid.innerHTML = '';
			self.showError(C.i18n.network);
		});
	};

	Booking.prototype.pickDay = function (id) {
		var self = this;
		this.state.date = id;
		this.state.time = '';
		this.updateButtons();
		$$(this.form, '[data-day]').forEach(function (b) {
			var on = b.getAttribute('data-day') === id;
			b.classList.toggle('is-selected', on);
			b.setAttribute('aria-pressed', on);
		});
		var box = $(this.form, '[data-times]');
		box.innerHTML = '<p class="rb-muted">' + esc(C.i18n.loading) + '</p>';

		api('/slots?date=' + id).then(function (res) {
			if (res.status !== 200) throw new Error('slots');
			if (self.state.date !== id) return;
			var slots = res.body.slots || [];
			if (!slots.length) {
				box.innerHTML = '<p class="rb-muted">' + esc(C.i18n.noSlots) + '</p>';
				return;
			}
			var am = slots.filter(function (s) { return s < '12:00'; });
			var pm = slots.filter(function (s) { return s >= '12:00'; });
			var html = '<p class="rb-times__title">' + esc(C.i18n.slotsFor) + ' <strong>' + esc(res.body.label) + '</strong></p>';
			[[C.i18n.morning, am], [C.i18n.afternoon, pm]].forEach(function (g) {
				if (!g[1].length) return;
				html += '<p class="rb-times__group">' + esc(g[0]) + '</p><div class="rb-times__grid">';
				g[1].forEach(function (t) {
					html += '<button type="button" class="rb-slot" data-time="' + t + '" aria-pressed="false">' + t.replace(':', 'h') + '</button>';
				});
				html += '</div>';
			});
			box.innerHTML = html;
			$$(box, '[data-time]').forEach(function (b) {
				b.addEventListener('click', function () {
					self.state.time = b.getAttribute('data-time');
					$$(box, '[data-time]').forEach(function (x) {
						var on = x === b;
						x.classList.toggle('is-selected', on);
						x.setAttribute('aria-pressed', on);
					});
					self.updateButtons();
				});
			});
		}).catch(function () {
			box.innerHTML = '';
			self.showError(C.i18n.network);
		});
	};

	Booking.prototype.renderSummary = function (el, editable) {
		var s = this.state;
		var date = parseYmd(s.date).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
		el.innerHTML =
			'<dl>' +
			'<div><dt>Motif</dt><dd>' + esc(s.serviceLabel) + '</dd></div>' +
			'<div><dt>Date</dt><dd>' + esc(date.charAt(0).toUpperCase() + date.slice(1)) + '</dd></div>' +
			'<div><dt>Heure</dt><dd>' + esc(s.time.replace(':', 'h')) + '</dd></div>' +
			'</dl>' +
			(editable ? '<button type="button" class="rb-link" data-edit>Modifier</button>' : '');
		var self = this;
		var edit = $(el, '[data-edit]');
		if (edit) edit.addEventListener('click', function () { self.go(2); });
	};

	/* form.name is the form's own attribute, so fields are always read via elements. */
	Booking.prototype.field = function (n) { return this.form.elements.namedItem(n); };
	Booking.prototype.val = function (n) { return String(this.field(n).value || '').trim(); };

	Booking.prototype.fieldError = function (name, msg) {
		var input = $(this.form, '[name="' + name + '"]');
		if (!input) return;
		var wrap = input.closest('.rb-field') || input.parentNode;
		var old = $(wrap, '.rb-field__error');
		if (old) old.remove();
		input.removeAttribute('aria-invalid');
		if (!msg) return;
		input.setAttribute('aria-invalid', 'true');
		var p = document.createElement('span');
		p.className = 'rb-field__error';
		p.textContent = msg;
		wrap.appendChild(p);
	};

	Booking.prototype.validate = function () {
		var f = this.form;
		var errors = {};
		var name = this.val('name');
		var phone = this.val('phone').replace(/\D/g, '');
		var email = this.val('email');
		if (name.length < 2) errors.name = 'Indiquez votre nom et prénom.';
		if (phone.length < 9 || phone.length > 15) errors.phone = 'Indiquez un numéro de téléphone valide, ex. 06 12 34 56 78.';
		if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.email = 'Cette adresse e-mail ne semble pas valide.';
		if (!this.field('consent').checked) errors.consent = 'Merci de cocher cette case pour envoyer votre demande.';
		var self = this;
		['name', 'phone', 'email', 'consent'].forEach(function (k) { self.fieldError(k, errors[k]); });
		var firstBad = Object.keys(errors)[0];
		if (firstBad) $(f, '[name="' + firstBad + '"]').focus();
		return !firstBad;
	};

	Booking.prototype.submit = function () {
		var self = this;
		if (this.busy || !this.validate()) return;
		var f = this.form;
		var btn = $(f, '[data-submit]');
		var np = $(f, 'input[name="new_patient"]:checked');
		var payload = {
			service: this.state.service,
			date: this.state.date,
			time: this.state.time,
			name: this.val('name'),
			phone: this.val('phone'),
			email: this.val('email'),
			message: this.val('message'),
			new_patient: np ? np.value : '',
			consent: this.field('consent').checked ? '1' : '',
			website: this.field('website').value,
			elapsed: Date.now() - this.startedAt
		};
		this.busy = true;
		this.hideError();
		var label = btn.textContent;
		btn.disabled = true;
		btn.textContent = C.i18n.sending;

		api('/appointments', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(payload)
		}).then(function (res) {
			var b = res.body || {};
			if (res.status === 201 && b.ok) return self.done(b);
			if (b.fields) Object.keys(b.fields).forEach(function (k) { self.fieldError(k, b.fields[k]); });
			if (b.code === 'slot_taken') {
				self.monthCache = {};
				self.state.time = '';
				self.go(2);
				self.pickDay(self.state.date);
			}
			self.showError(b.message || C.i18n.network);
		}).catch(function () {
			self.showError(C.i18n.network);
		}).then(function () {
			self.busy = false;
			btn.disabled = false;
			btn.textContent = label;
		});
	};

	Booking.prototype.done = function (b) {
		this.go('done');
		var sum = $(this.form, '[data-done-summary]');
		sum.innerHTML = '<dl>' +
			'<div><dt>Motif</dt><dd>' + esc(b.service) + '</dd></div>' +
			'<div><dt>Date</dt><dd>' + esc(b.date_label) + '</dd></div>' +
			'<div><dt>Heure</dt><dd>' + esc(b.time_label) + '</dd></div>' +
			'<div><dt>Référence</dt><dd>' + esc(b.reference) + '</dd></div>' +
			'</dl>';
		var actions = $(this.form, '[data-done-actions]');
		var html = '';
		if (b.ics) {
			var url = URL.createObjectURL(new Blob([b.ics], { type: 'text/calendar' }));
			html += '<a class="rb-btn rb-btn--ghost" href="' + url + '" download="rendez-vous-dentiste.ics">Ajouter à mon agenda</a>';
		}
		if (b.whatsapp) {
			html += '<a class="rb-btn rb-btn--whatsapp" href="' + esc(b.whatsapp) + '" target="_blank" rel="noopener" data-track="whatsapp">Prévenir le cabinet sur WhatsApp</a>';
		}
		actions.innerHTML = html;
		$$(this.root, '.rb-progress li').forEach(function (li) { li.classList.add('is-done'); li.classList.remove('is-current'); });
		track('generate_lead', { event_category: 'booking', service: b.service });
	};

	Booking.prototype.showError = function (msg) {
		var el = $(this.form, '[data-error]');
		el.innerHTML = esc(msg) + ' <a href="' + esc(C.phoneHref) + '">' + esc(C.phone) + '</a>';
		el.hidden = false;
	};

	Booking.prototype.hideError = function () {
		$(this.form, '[data-error]').hidden = true;
	};

	function boot() {
		$$(document, '[data-rb]').forEach(function (el) {
			if (!el.rb) el.rb = new Booking(el);
		});
	}

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
	else boot();
})();
