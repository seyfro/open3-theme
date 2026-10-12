/**
 * open3 theme — front-end behaviour (no dependencies, enqueued deferred).
 *
 * 1. Mobile menu toggle  (button.o3-menu-toggle[aria-controls] ↔ nav#skiplink-navigation.o3-nav.is-open)
 * 2. Projekt-Explorer     (section.o3-explorer[data-index] cycling through the JSON island #o3-explorer-data)
 *
 * The markup contract lives in the build contract / docs/plan/02-css-fidelity.md §8.
 */
(function () {
	'use strict';

	var mq = window.matchMedia ? window.matchMedia('(max-width: 899.98px)') : null;

	/* ---- 1. Mobile menu ---- */
	var toggle = document.querySelector('.o3-menu-toggle');
	var nav = toggle ? document.getElementById(toggle.getAttribute('aria-controls') || '') : null;

	if (toggle && nav) {
		var labels = {
			closed: toggle.getAttribute('data-label-open') || toggle.textContent.trim() || 'Menü',
			open: toggle.getAttribute('data-label-close') || 'Schließen'
		};

		var isOpen = function () {
			return nav.classList.contains('is-open');
		};

		var setOpen = function (open, moveFocus) {
			nav.classList.toggle('is-open', open);
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			toggle.textContent = open ? labels.open : labels.closed;
			if (moveFocus !== false) {
				var target = open ? nav.querySelector('a, button') : toggle;
				if (target && typeof target.focus === 'function') {
					target.focus();
				}
			}
		};

		toggle.setAttribute('aria-expanded', 'false');

		toggle.addEventListener('click', function () {
			setOpen(!isOpen());
		});

		document.addEventListener('keydown', function (e) {
			if ((e.key === 'Escape' || e.key === 'Esc') && isOpen()) {
				e.preventDefault();
				setOpen(false);
			}
		});

		// Close on in-page navigation (anchor links); full navigations reload anyway.
		nav.addEventListener('click', function (e) {
			var link = e.target && e.target.closest ? e.target.closest('a') : null;
			if (link) {
				setOpen(false, false);
			}
		});

		// Close when focus leaves the header entirely (keyboard users tabbing past the menu).
		document.addEventListener('focusout', function (e) {
			var next = e.relatedTarget;
			if (isOpen() && next && next.closest && !next.closest('.o3-header')) {
				setOpen(false, false);
			}
		});

		// Reset when the viewport grows to the desktop layout.
		if (mq) {
			var onChange = function (e) {
				if (!e.matches && isOpen()) {
					setOpen(false, false);
				}
			};
			if (typeof mq.addEventListener === 'function') {
				mq.addEventListener('change', onChange);
			} else if (typeof mq.addListener === 'function') {
				mq.addListener(onChange);
			}
		}
	}

	/* ---- 2. Projekt-Explorer ---- */
	var box = document.querySelector('.o3-explorer');
	var dataEl = document.getElementById('o3-explorer-data');

	if (box && dataEl) {
		var items = [];
		try {
			items = JSON.parse(dataEl.textContent || '[]');
		} catch (err) {
			items = [];
		}
		if (!Array.isArray(items)) {
			items = [];
		}

		var media = box.querySelector('.o3-explorer__media');
		var type = box.querySelector('.o3-explorer__type');
		var title = box.querySelector('.o3-explorer__title');
		var next = box.querySelector('.o3-explorer__next');
		var index = parseInt(box.getAttribute('data-index') || '0', 10);
		if (isNaN(index) || index < 0) {
			index = 0;
		}

		if (next && items.length < 2) {
			next.disabled = true; // ds .btn:disabled
		}

		var render = function (p) {
			if (!p) {
				return;
			}
			if (media) {
				media.href = p.url || '#';
				while (media.firstChild) {
					media.removeChild(media.firstChild);
				}
				if (p.img && p.img.src) {
					var img = new Image();
					img.src = p.img.src;
					if (p.img.srcset) {
						img.srcset = p.img.srcset;
					}
					if (p.img.sizes) {
						img.sizes = p.img.sizes;
					}
					if (p.img.width) {
						img.width = p.img.width;
					}
					if (p.img.height) {
						img.height = p.img.height;
					}
					img.alt = p.img.alt || '';
					img.decoding = 'async';
					img.loading = 'lazy';
					media.appendChild(img);
				} else {
					var ph = document.createElement('span');
					ph.className = 'o3-placeholder';
					var cap = document.createElement('span');
					cap.className = 'o3-placeholder__caption';
					cap.textContent = p.short || p.title || '';
					ph.appendChild(cap);
					media.appendChild(ph);
				}
			}
			if (type) {
				type.textContent = p.type || '';
			}
			if (title) {
				title.href = p.url || '#';
				title.textContent = p.title || '';
			}
		};

		if (next && items.length > 1) {
			next.addEventListener('click', function () {
				index = (index + 1) % items.length;
				render(items[index]);
				box.setAttribute('data-index', String(index));
				// aria-live="polite" on .o3-explorer__title announces the new title; focus stays on the button.
			});
		}
	}
})();
