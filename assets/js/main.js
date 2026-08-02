(function () {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function onReady(callback) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback, { once: true });
      return;
    }
    callback();
  }

  function initHeader() {
    const header = document.querySelector('[data-header]');
    const toggle = document.querySelector('[data-menu-toggle]');
    const mobileNav = document.querySelector('[data-mobile-nav]');
    if (!header) return;

    let isScrolled = null;
    const updateHeader = () => {
      const nextState = window.scrollY > 8;
      if (nextState === isScrolled) return;
      isScrolled = nextState;
      header.classList.toggle('is-scrolled', nextState);
    };
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    if (!toggle || !mobileNav) return;

    let menuReturnFocus = null;

    const menuFocusables = () => [
      toggle,
      ...mobileNav.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'),
    ].filter((element) => !element.hidden);

    const setMenu = (open, restoreFocus = true) => {
      if (open) menuReturnFocus = document.activeElement;
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      mobileNav.hidden = !open;
      document.body.classList.toggle('menu-open', open);
      if (open) {
        const firstLink = mobileNav.querySelector('a');
        if (firstLink) firstLink.focus({ preventScroll: true });
      } else if (restoreFocus && menuReturnFocus instanceof HTMLElement) {
        menuReturnFocus.focus({ preventScroll: true });
      }
    };

    toggle.addEventListener('click', () => {
      setMenu(toggle.getAttribute('aria-expanded') !== 'true');
    });

    mobileNav.addEventListener('click', (event) => {
      if (event.target.closest('a')) setMenu(false);
    });

    document.addEventListener('keydown', (event) => {
      const menuOpen = toggle.getAttribute('aria-expanded') === 'true';
      if (event.key === 'Escape' && menuOpen) {
        setMenu(false);
        return;
      }

      if (event.key === 'Tab' && menuOpen) {
        const focusables = menuFocusables();
        const first = focusables[0];
        const last = focusables[focusables.length - 1];
        if (!first || !last) return;

        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        } else if (!focusables.includes(document.activeElement)) {
          event.preventDefault();
          first.focus();
        }
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 860 && toggle.getAttribute('aria-expanded') === 'true') {
        const focusWasInMenu = document.activeElement === toggle || mobileNav.contains(document.activeElement);
        setMenu(false, false);
        if (focusWasInMenu) {
          const desktopFocusTarget = header.querySelector('.quote-btn');
          if (desktopFocusTarget) desktopFocusTarget.focus({ preventScroll: true });
        }
      }
    });
  }

  function initReveal() {
    const items = document.querySelectorAll('[data-reveal], [data-reveal-group]');
    if (!items.length) return;

    if (reduceMotion || !('IntersectionObserver' in window)) {
      items.forEach((item) => item.classList.add('is-visible'));
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { rootMargin: '120px 0px', threshold: 0.05 });

    items.forEach((item) => observer.observe(item));
  }

  function initCounters() {
    const counters = Array.from(document.querySelectorAll('[data-count-target]'));
    if (!counters.length) return;

    const showFinalValue = (counter) => {
      counter.textContent = counter.dataset.countDisplay || counter.textContent || '0';
    };

    if (reduceMotion || !('IntersectionObserver' in window)) {
      counters.forEach(showFinalValue);
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        observer.unobserve(entry.target);

        const counter = entry.target;
        const target = Number(counter.dataset.countTarget || 0);
        const display = counter.dataset.countDisplay || String(target);
        const numericToken = display.match(/[0-9,.]+/)?.[0] || String(target);
        const tokenIndex = display.indexOf(numericToken);
        const prefix = tokenIndex > 0 ? display.slice(0, tokenIndex) : '';
        const suffix = display.slice(tokenIndex + numericToken.length);
        const formatter = new Intl.NumberFormat('en-US', { useGrouping: numericToken.includes(',') });
        const duration = 780;
        const startTime = performance.now();

        counter.textContent = prefix + formatter.format(0) + suffix;

        const tick = (now) => {
          if (document.hidden) {
            showFinalValue(counter);
            return;
          }

          const progress = Math.min((now - startTime) / duration, 1);
          const eased = 1 - Math.pow(1 - progress, 3);
          counter.textContent = prefix + formatter.format(Math.round(target * eased)) + suffix;

          if (progress < 1) {
            window.requestAnimationFrame(tick);
          } else {
            showFinalValue(counter);
          }
        };

        window.requestAnimationFrame(tick);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.35 });

    counters.forEach((counter) => {
      counter.textContent = (counter.dataset.countDisplay || '0').replace(/[0-9,.]+/, '0');
      observer.observe(counter);
    });
  }

  function initProductTabs() {
    const nav = document.querySelector('[data-product-tabs]');
    if (!nav) return;

    const scroller = nav.querySelector('.product-tabs') || nav;
    const links = Array.from(nav.querySelectorAll('[data-section]'));
    const sections = links
      .map((link) => document.getElementById(link.dataset.section || ''))
      .filter(Boolean);
    const navFlowTop = () => {
      const previous = nav.previousElementSibling;
      return previous instanceof HTMLElement
        ? previous.offsetTop + previous.offsetHeight
        : nav.getBoundingClientRect().top + window.scrollY;
    };

    const setActive = (id) => {
      links.forEach((link) => {
        const active = link.dataset.section === id;
        link.classList.toggle('is-active', active);
        if (active) {
          link.setAttribute('aria-current', 'true');
          if (scroller.scrollWidth > scroller.clientWidth) {
            const left = link.offsetLeft - ((scroller.clientWidth - link.offsetWidth) / 2);
            scroller.scrollTo({ left: Math.max(0, left), behavior: reduceMotion ? 'auto' : 'smooth' });
          }
        } else {
          link.removeAttribute('aria-current');
        }
      });
    };

    links.forEach((link) => {
      link.addEventListener('click', (event) => {
        const target = document.getElementById(link.dataset.section || '');
        if (!target) return;
        event.preventDefault();
        setActive(link.dataset.section || '');
        target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
        history.replaceState(null, '', '#' + target.id);
      });
    });

    if (!('IntersectionObserver' in window)) return;
    const observer = new IntersectionObserver((entries) => {
      const visible = entries
        .filter((entry) => entry.isIntersecting)
        .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
      if (visible) {
        setActive(visible.target.id);
      } else if (window.scrollY < navFlowTop()) {
        setActive(links[0]?.dataset.section || 'overview');
      }
    }, { rootMargin: '-28% 0px -55% 0px', threshold: [0.05, 0.25, 0.5] });
    sections.forEach((section) => observer.observe(section));
  }

  function initContactForms() {
    const forms = Array.from(document.querySelectorAll('[data-contact-form]'));
    if (!forms.length) return;

    forms.forEach((form) => {

      const status = form.querySelector('[data-form-status]');
      const submit = form.querySelector('[data-submit-button]');
      const startedAt = form.querySelector('[data-form-started-at]');
      const fileInput = form.querySelector('input[type="file"]');
      const inquiryType = form.querySelector('[data-inquiry-type]');
      const drawingField = form.querySelector('[data-drawing-field]');
      const config = window.SpringApex || {};
      if (startedAt) startedAt.value = String(Math.floor(Date.now() / 1000));

      const syncDrawingField = () => {
        if (!inquiryType || !drawingField) return;
        const visible = inquiryType.value === 'Upload a Drawing';
        drawingField.classList.toggle('is-collapsed', !visible);
        if (!visible && fileInput && fileInput.files.length) fileInput.value = '';
      };

      if (inquiryType) inquiryType.addEventListener('change', syncDrawingField);
      syncDrawingField();

      const showStatus = (message, type) => {
        if (!status) return;
        status.hidden = false;
        status.textContent = message;
        status.classList.toggle('is-error', type === 'error');
        status.classList.toggle('is-success', type === 'success');
      };

      form.addEventListener('submit', async (event) => {
      event.preventDefault();

      if (!form.checkValidity()) {
        form.reportValidity();
        showStatus('Please complete the required fields.', 'error');
        return;
      }

      const maxFileSize = Number(config.maxFileSize || 10 * 1024 * 1024);
      if (fileInput && fileInput.files[0] && fileInput.files[0].size > maxFileSize) {
        showStatus('The drawing must be 10 MB or smaller.', 'error');
        fileInput.focus();
        return;
      }

      if (!config.ajaxUrl) {
        showStatus('Preview only — the form was not sent.', 'success');
        return;
      }

      if (submit) {
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
      }
      showStatus('Submitting your request…', '');

      const data = new FormData(form);
      data.append('action', 'springapex_contact');
      data.append('nonce', config.nonce || '');

      try {
        const response = await fetch(config.ajaxUrl, {
          method: 'POST',
          body: data,
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) {
          const message = payload.data && payload.data.message ? payload.data.message : 'Unable to submit right now.';
          throw new Error(message);
        }
        showStatus(payload.data.message || 'Thank you. Your request has been received.', 'success');
        form.reset();
        if (startedAt) startedAt.value = String(Math.floor(Date.now() / 1000));
        syncDrawingField();
      } catch (error) {
        showStatus(error instanceof Error ? error.message : 'Unable to submit right now.', 'error');
      } finally {
        if (submit) {
          submit.disabled = false;
          submit.removeAttribute('aria-busy');
        }
      }
      });
    });
  }

  function initSupportWidget() {
    const widget = document.querySelector('[data-support-widget]');
    if (!widget) return;

    const panel = widget.querySelector('[data-support-panel]');
    const toggle = widget.querySelector('[data-support-toggle]');
    const close = widget.querySelector('[data-support-close]');
    if (!panel || !toggle || !close) return;

    const mobileQuery = window.matchMedia('(max-width: 860px)');
    const isEditingField = (element) => element instanceof HTMLElement
      && Boolean(element.closest('input, textarea, select, [contenteditable="true"]'));

    const setOpen = (open, restoreFocus = true) => {
      panel.hidden = !open;
      document.body.classList.toggle('support-panel-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Close quick inquiry form' : 'Open quick inquiry form');
      if (open) {
        const firstField = panel.querySelector('[data-support-first-field]');
        (firstField || close).focus({ preventScroll: true });
      } else if (restoreFocus) {
        toggle.focus({ preventScroll: true });
      }
    };

    const syncSuppressedState = () => {
      const shouldSuppress = mobileQuery.matches
        && (document.body.classList.contains('menu-open') || isEditingField(document.activeElement));
      widget.classList.toggle('is-suppressed', shouldSuppress);
      if ((mobileQuery.matches || shouldSuppress) && !panel.hidden) setOpen(false, false);
    };

    toggle.addEventListener('click', () => {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    close.addEventListener('click', () => setOpen(false));

    document.addEventListener('click', (event) => {
      if (panel.hidden || widget.contains(event.target)) return;
      setOpen(false, false);
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !panel.hidden) setOpen(false);
    });

    document.addEventListener('focusin', syncSuppressedState);
    document.addEventListener('focusout', () => window.setTimeout(syncSuppressedState, 0));
    mobileQuery.addEventListener('change', syncSuppressedState);

    const bodyClassObserver = new MutationObserver(syncSuppressedState);
    bodyClassObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });
    syncSuppressedState();
  }

  function initSearchOverlay() {
    const toggle = document.querySelector('[data-search-toggle]');
    const overlay = document.querySelector('[data-search-overlay]');
    const backdrop = document.querySelector('[data-search-backdrop]');
    const closeBtn = document.querySelector('[data-search-close]');
    const input = overlay ? overlay.querySelector('input[type="search"]') : null;
    if (!toggle || !overlay) return;

    document.body.appendChild(overlay);

    const setOverlay = (open) => {
      overlay.hidden = !open;
      document.body.classList.toggle('search-overlay-open', open);
      if (open && input) {
        requestAnimationFrame(() => input.focus());
      }
    };

    toggle.addEventListener('click', () => setOverlay(true));
    if (closeBtn) closeBtn.addEventListener('click', () => {
      setOverlay(false);
      toggle.focus();
    });
    if (backdrop) backdrop.addEventListener('click', () => {
      setOverlay(false);
      toggle.focus();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !overlay.hidden) {
        setOverlay(false);
        toggle.focus();
      }
    });
  }

  function initLanguageSwitcher() {
    const switcher = document.querySelector('[data-language-switcher]');
    if (!(switcher instanceof HTMLDetailsElement)) return;

    document.addEventListener('click', (event) => {
      if (switcher.open && !switcher.contains(event.target)) switcher.open = false;
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && switcher.open) {
        switcher.open = false;
        switcher.querySelector('summary')?.focus();
      }
    });
  }

  onReady(() => {
    initHeader();
    initReveal();
    initCounters();
    initProductTabs();
    initContactForms();
    initSupportWidget();
    initSearchOverlay();
    initLanguageSwitcher();
  });
})();
