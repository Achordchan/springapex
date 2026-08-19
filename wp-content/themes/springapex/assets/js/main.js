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

  function stabilizeInitialAnchor() {
    const hash = window.location.hash.slice(1);
    if (!hash) return;

    let targetId = hash;
    try {
      targetId = decodeURIComponent(hash);
    } catch (error) {
      return;
    }

    const target = document.getElementById(targetId);
    if (!target) return;

    const root = document.documentElement;
    const scrollPaddingTop = Number.parseFloat(window.getComputedStyle(root).scrollPaddingTop) || 0;
    // 产品详情页的吸顶补偿记在目标自身的 scroll-margin-top 上（root padding 已归零），
    // 这里必须一并读取，否则深链落点会被吸顶栏盖住。
    const scrollMarginTop = Number.parseFloat(window.getComputedStyle(target).scrollMarginTop) || 0;
    const targetTop = target.getBoundingClientRect().top + window.scrollY - scrollPaddingTop - scrollMarginTop;
    const previousScrollBehavior = root.style.scrollBehavior;

    root.style.scrollBehavior = 'auto';
    window.scrollTo(0, Math.max(0, targetTop));
    window.requestAnimationFrame(() => {
      root.style.scrollBehavior = previousScrollBehavior;
    });
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

    // 面板顶部要对齐 header 的实际渲染底边：WP 管理栏（移动端 46px）等
    // 顶部元素会把 sticky header 推到 --sa-header-height 常量之外，写死的
    // top 会让面板钻进 header 底下、遮住第一项（Home）。
    const alignMobileNav = () => {
      const bottom = Math.round(header.getBoundingClientRect().bottom);
      mobileNav.style.top = `${bottom}px`;
      mobileNav.style.maxHeight = `calc(100dvh - ${bottom}px)`;
    };

    const setMenu = (open, restoreFocus = true) => {
      if (open) {
        menuReturnFocus = document.activeElement;
        alignMobileNav();
      } else {
        mobileNav.style.top = '';
        mobileNav.style.maxHeight = '';
      }
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      mobileNav.hidden = !open;
      document.body.classList.toggle('menu-open', open);
      if (!open && restoreFocus && menuReturnFocus instanceof HTMLElement) {
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
      } else if (toggle.getAttribute('aria-expanded') === 'true') {
        alignMobileNav();
      }
    });
  }

  function initProductMenus() {
    const desktopTrigger = document.querySelector('[data-product-menu-trigger]');
    const desktopPanel = document.querySelector('[data-product-menu-panel]');
    const mobileToggle = document.querySelector('[data-mobile-products-toggle]');
    const mobilePanel = document.querySelector('[data-mobile-products-panel]');
    const mobileNav = document.querySelector('[data-mobile-nav]');
    const desktopQuery = window.matchMedia('(min-width: 861px)');
    let desktopCloseTimer = null;

    const cancelDesktopClose = () => {
      if (desktopCloseTimer === null) return;
      window.clearTimeout(desktopCloseTimer);
      desktopCloseTimer = null;
    };

    const setDesktopOpen = (open, restoreFocus = false) => {
      if (!desktopTrigger || !desktopPanel) return;
      cancelDesktopClose();
      const nextOpen = open && desktopQuery.matches;
      desktopPanel.hidden = !nextOpen;
      desktopTrigger.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
      document.body.classList.toggle('product-menu-open', nextOpen);
      if (!nextOpen && restoreFocus) desktopTrigger.focus({ preventScroll: true });
    };

    const scheduleDesktopClose = () => {
      cancelDesktopClose();
      desktopCloseTimer = window.setTimeout(() => setDesktopOpen(false), 180);
    };

    if (desktopTrigger && desktopPanel) {
      desktopTrigger.addEventListener('pointerenter', () => setDesktopOpen(true));
      desktopTrigger.addEventListener('pointerleave', scheduleDesktopClose);
      desktopPanel.addEventListener('pointerenter', cancelDesktopClose);
      desktopPanel.addEventListener('pointerleave', scheduleDesktopClose);
      desktopTrigger.addEventListener('focus', () => setDesktopOpen(true));

      desktopTrigger.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
          event.preventDefault();
          setDesktopOpen(true);
          desktopPanel.querySelector('a[href]')?.focus({ preventScroll: true });
        } else if (event.key === 'Escape') {
          setDesktopOpen(false, true);
        }
      });

      desktopPanel.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          event.preventDefault();
          setDesktopOpen(false, true);
        }
      });

      document.querySelectorAll('.nav-desktop__item:not(.nav-desktop__item--products)').forEach((item) => {
        item.addEventListener('pointerenter', () => setDesktopOpen(false));
        item.addEventListener('focusin', () => setDesktopOpen(false));
      });

      document.addEventListener('pointerdown', (event) => {
        if (desktopPanel.hidden || desktopTrigger.contains(event.target) || desktopPanel.contains(event.target)) return;
        setDesktopOpen(false);
      });

      document.addEventListener('focusin', (event) => {
        if (desktopPanel.hidden || desktopTrigger.contains(event.target) || desktopPanel.contains(event.target)) return;
        setDesktopOpen(false);
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !desktopPanel.hidden) setDesktopOpen(false, true);
      });

      document.querySelector('[data-search-toggle]')?.addEventListener('click', () => setDesktopOpen(false));
      document.querySelector('[data-language-switcher] summary')?.addEventListener('click', () => setDesktopOpen(false));
    }

    const setMobileOpen = (open) => {
      if (!mobileToggle || !mobilePanel) return;
      mobileToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      mobilePanel.hidden = !open;
    };

    if (mobileToggle && mobilePanel) {
      mobileToggle.addEventListener('click', () => {
        setMobileOpen(mobileToggle.getAttribute('aria-expanded') !== 'true');
      });
    }

    if (mobileNav) {
      const mobileNavObserver = new MutationObserver(() => {
        if (mobileNav.hidden) setMobileOpen(false);
      });
      mobileNavObserver.observe(mobileNav, { attributes: true, attributeFilter: ['hidden'] });
    }

    desktopQuery.addEventListener('change', (event) => {
      setDesktopOpen(false);
      if (event.matches) setMobileOpen(false);
    });
  }

  function initNavDropdowns() {
    const triggers = Array.from(document.querySelectorAll('[data-nav-dropdown-trigger]'));
    if (!triggers.length) return;

    const desktopQuery = window.matchMedia('(min-width: 861px)');
    const pairs = triggers
      .map((trigger) => {
        const panel = document.getElementById(trigger.getAttribute('aria-controls') || '');
        return panel ? { trigger, panel, closeTimer: null } : null;
      })
      .filter(Boolean);

    const setOpen = (pair, open, restoreFocus = false) => {
      const nextOpen = open && desktopQuery.matches;
      pair.panel.hidden = !nextOpen;
      pair.trigger.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
      if (!nextOpen && restoreFocus) pair.trigger.focus({ preventScroll: true });
    };

    const cancelClose = (pair) => {
      if (pair.closeTimer === null) return;
      window.clearTimeout(pair.closeTimer);
      pair.closeTimer = null;
    };

    const scheduleClose = (pair) => {
      cancelClose(pair);
      pair.closeTimer = window.setTimeout(() => setOpen(pair, false), 180);
    };

    const closeAll = (exceptPair) => {
      pairs.forEach((pair) => {
        if (pair === exceptPair) return;
        cancelClose(pair);
        setOpen(pair, false);
      });
    };

    pairs.forEach((pair) => {
      pair.trigger.addEventListener('pointerenter', () => {
        closeAll(pair);
        cancelClose(pair);
        setOpen(pair, true);
      });
      pair.trigger.addEventListener('pointerleave', () => scheduleClose(pair));
      pair.panel.addEventListener('pointerenter', () => cancelClose(pair));
      pair.panel.addEventListener('pointerleave', () => scheduleClose(pair));
      pair.trigger.addEventListener('focus', () => {
        closeAll(pair);
        setOpen(pair, true);
      });

      pair.trigger.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
          event.preventDefault();
          setOpen(pair, true);
          pair.panel.querySelector('a[href]')?.focus({ preventScroll: true });
        } else if (event.key === 'Escape') {
          setOpen(pair, false, true);
        }
      });

      pair.panel.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          event.preventDefault();
          setOpen(pair, false, true);
        }
      });
    });

    document.querySelectorAll('.nav-desktop__item:not(.nav-desktop__item--has-children)').forEach((item) => {
      item.addEventListener('pointerenter', () => closeAll());
      item.addEventListener('focusin', () => closeAll());
    });

    document.querySelector('[data-product-menu-trigger]')?.addEventListener('pointerenter', () => closeAll());

    document.addEventListener('pointerdown', (event) => {
      pairs.forEach((pair) => {
        if (pair.panel.hidden || pair.trigger.contains(event.target) || pair.panel.contains(event.target)) return;
        setOpen(pair, false);
      });
    });

    document.addEventListener('focusin', (event) => {
      pairs.forEach((pair) => {
        if (pair.panel.hidden || pair.trigger.contains(event.target) || pair.panel.contains(event.target)) return;
        setOpen(pair, false);
      });
    });

    desktopQuery.addEventListener('change', () => closeAll());
  }

  function initMobileSubmenus() {
    const toggles = Array.from(document.querySelectorAll('[data-mobile-submenu-toggle]'));
    if (!toggles.length) return;

    toggles.forEach((toggle) => {
      const panel = document.getElementById(toggle.getAttribute('aria-controls') || '');
      if (!panel) return;
      toggle.addEventListener('click', () => {
        const open = toggle.getAttribute('aria-expanded') !== 'true';
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.hidden = !open;
      });
    });

    const mobileNav = document.querySelector('[data-mobile-nav]');
    if (mobileNav) {
      const observer = new MutationObserver(() => {
        if (mobileNav.hidden) {
          toggles.forEach((toggle) => {
            const panel = document.getElementById(toggle.getAttribute('aria-controls') || '');
            toggle.setAttribute('aria-expanded', 'false');
            if (panel) panel.hidden = true;
          });
        }
      });
      observer.observe(mobileNav, { attributes: true, attributeFilter: ['hidden'] });
    }
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
      const drawingUpload = form.querySelector('[data-drawing-upload]');
      const drawingGuideOpen = form.querySelector('[data-drawing-guide-open]');
      const drawingGuideDialog = form.querySelector('[data-drawing-guide-dialog]');
      const drawingGuideClose = form.querySelector('[data-drawing-guide-close]');
      const config = window.ApexSpring || {};
      if (startedAt) startedAt.value = String(Math.floor(Date.now() / 1000));

      const closeDrawingGuide = () => {
        if (!drawingGuideDialog) return;
        if (typeof drawingGuideDialog.close === 'function' && drawingGuideDialog.open) {
          drawingGuideDialog.close();
        } else {
          drawingGuideDialog.removeAttribute('open');
        }
      };

      if (drawingGuideOpen && drawingGuideDialog && drawingGuideClose) {
        drawingGuideOpen.addEventListener('click', () => {
          drawingGuideDialog.querySelectorAll('details').forEach((details) => {
            details.open = false;
          });
          drawingGuideOpen.setAttribute('aria-expanded', 'true');
          document.body.classList.add('drawing-guide-open');
          if (typeof drawingGuideDialog.showModal === 'function') {
            drawingGuideDialog.showModal();
          } else {
            drawingGuideDialog.setAttribute('open', '');
          }
          drawingGuideClose.focus({ preventScroll: true });
        });

        drawingGuideClose.addEventListener('click', closeDrawingGuide);
        drawingGuideDialog.addEventListener('click', (event) => {
          if (event.target === drawingGuideDialog) closeDrawingGuide();
        });
        drawingGuideDialog.addEventListener('cancel', (event) => {
          event.preventDefault();
          closeDrawingGuide();
        });
        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape' && drawingGuideDialog.open) {
            event.preventDefault();
            closeDrawingGuide();
          }
        });
        drawingGuideDialog.addEventListener('close', () => {
          document.body.classList.remove('drawing-guide-open');
          drawingGuideOpen.setAttribute('aria-expanded', 'false');
          drawingGuideOpen.focus({ preventScroll: true });
        });
      }

      const MAX_FILES = 10;
      const fileList = form.querySelector('[data-contact-file-list]');

      const updateFileList = () => {
        const files = Array.from(fileInput?.files || []).slice(0, MAX_FILES);
        const hasFile = files.length > 0;

        if (fileList instanceof HTMLElement) {
          fileList.hidden = !hasFile;
          fileList.innerHTML = files.map((file, index) => `
            <li class="contact-file-item">
              <span class="contact-file-name">${file.name}</span>
              <button type="button" class="contact-file-remove" data-file-index="${index}" aria-label="Remove ${file.name}">×</button>
            </li>
          `).join('');

          fileList.querySelectorAll('[data-file-index]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
              e.preventDefault();
              const index = parseInt(btn.dataset.fileIndex, 10);
              const dt = new DataTransfer();
              files.forEach((f, i) => { if (i !== index) dt.items.add(f); });
              if (fileInput) fileInput.files = dt.files;
              updateFileList();
            });
          });
        }
      };

      if (fileInput) fileInput.addEventListener('change', () => {
        const selected = Array.from(fileInput.files || []).slice(0, MAX_FILES);
        if (selected.length !== fileInput.files.length) {
          const dt = new DataTransfer();
          selected.forEach(file => dt.items.add(file));
          fileInput.files = dt.files;
        }
        updateFileList();
      });

      const syncDrawingUpload = () => {
        if (!inquiryType || !drawingUpload) return;
        const visible = inquiryType.value === 'Upload a Drawing';
        drawingUpload.hidden = !visible;
        if (!visible) {
          closeDrawingGuide();
          if (fileInput && fileInput.files.length) fileInput.value = '';
          updateFileList();
        }
      };

      if (inquiryType) inquiryType.addEventListener('change', syncDrawingUpload);
      syncDrawingUpload();

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
        showStatus('Please complete the required fields.', 'error');
        const firstInvalid = form.querySelector(':invalid');
        if (firstInvalid instanceof HTMLElement) {
          firstInvalid.setAttribute('aria-invalid', 'true');
          firstInvalid.focus();
        }
        return;
      }

      const maxFileSize = Number(config.maxFileSize || 10 * 1024 * 1024);
      if (fileInput && fileInput.files.length) {
        const files = Array.from(fileInput.files);
        const oversizedFiles = files.filter(f => f.size > maxFileSize);
        if (oversizedFiles.length > 0) {
          showStatus(`File${oversizedFiles.length > 1 ? 's' : ''} must be 10 MB or smaller: ${oversizedFiles.map(f => f.name).join(', ')}`, 'error');
          fileInput.focus();
          return;
        }
        if (files.reduce((total, file) => total + file.size, 0) > maxFileSize) {
          showStatus('The combined files must be 10 MB or smaller.', 'error');
          fileInput.focus();
          return;
        }
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
        const successMode = form.dataset.success || 'redirect';
        if (successMode === 'inline') {
          // Quick inquiry widget: stay in the popup and swap the form for an
          // in-panel thank-you screen (see parts/support-widget.php).
          const panelBody = form.closest('.support-panel-body');
          const thankyou = panelBody ? panelBody.querySelector('[data-support-thankyou]') : null;
          if (thankyou instanceof HTMLElement && panelBody) {
            panelBody.classList.add('is-submitted');
            thankyou.hidden = false;
            form.reset();
            if (startedAt) startedAt.value = String(Math.floor(Date.now() / 1000));
            syncDrawingUpload();
            const resetButton = panelBody.querySelector('[data-support-reset]');
            if (resetButton instanceof HTMLElement) resetButton.focus({ preventScroll: true });
          } else {
            showStatus(payload.data.message || 'Thank you. Your request has been received.', 'success');
            form.reset();
            if (startedAt) startedAt.value = String(Math.floor(Date.now() / 1000));
            syncDrawingUpload();
          }
        } else {
          // Every other form redirects to the /success landing page so the
          // conversion is trackable by URL. Do it before the form resets.
          const successUrl = config.successUrl || (config.homeUrl ? `${config.homeUrl}success/` : '/success/');
          window.location.assign(successUrl);
          return;
        }
      } catch (error) {
        showStatus(error instanceof Error ? error.message : 'Unable to submit right now.', 'error');
      } finally {
        if (submit) {
          submit.disabled = false;
          submit.removeAttribute('aria-busy');
        }
        // Turnstile tokens are single-use; refresh the widget so a follow-up
        // submission (after an error, or a second inquiry) gets a fresh token.
        const captcha = form.querySelector('.cf-turnstile');
        if (captcha && window.turnstile && typeof window.turnstile.reset === 'function') {
          try {
            window.turnstile.reset(captcha);
          } catch (resetError) {
            /* widget not ready yet — nothing to reset */
          }
        }
      }
      });

      // Quick inquiry widget: "Send another inquiry" returns from the
      // thank-you screen to a fresh form.
      const supportReset = form.closest('.support-panel-body')?.querySelector('[data-support-reset]');
      if (supportReset instanceof HTMLElement) {
        supportReset.addEventListener('click', () => {
          const panelBody = form.closest('.support-panel-body');
          const thankyou = panelBody ? panelBody.querySelector('[data-support-thankyou]') : null;
          if (thankyou instanceof HTMLElement) thankyou.hidden = true;
          if (panelBody) panelBody.classList.remove('is-submitted');
          if (status) status.hidden = true;
          const firstField = form.querySelector('[data-support-first-field]') || form.querySelector('input:not([type="hidden"]), textarea');
          if (firstField instanceof HTMLElement) firstField.focus({ preventScroll: true });
        });
      }

      form.addEventListener('input', (event) => {
        const field = event.target;
        if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement) {
          if (field.checkValidity()) field.removeAttribute('aria-invalid');
        }
      });

      form.addEventListener('change', (event) => {
        const field = event.target;
        if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement) {
          if (field.checkValidity()) field.removeAttribute('aria-invalid');
        }
      });
    });
  }

  function initSupportWidget() {
    const widget = document.querySelector('[data-support-widget]');
    if (!widget) return;

    const panel = widget.querySelector('[data-support-panel]');
    const toggles = Array.from(widget.querySelectorAll('[data-support-toggle]'));
    const close = widget.querySelector('[data-support-close]');
    if (!panel || toggles.length === 0 || !close) return;

    const mobileQuery = window.matchMedia('(max-width: 860px)');
    let activeToggle = toggles[0];
    const isEditingField = (element) => element instanceof HTMLElement
      && Boolean(element.closest('input, textarea, select, [contenteditable="true"]'));

    const setOpen = (open, restoreFocus = true) => {
      panel.hidden = !open;
      document.body.classList.toggle('support-panel-open', open);
      toggles.forEach((toggle) => {
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close quick inquiry form' : 'Open quick inquiry form');
      });
      if (open) {
        const firstField = panel.querySelector('[data-support-first-field]');
        (firstField || close).focus({ preventScroll: true });
      } else if (restoreFocus) {
        activeToggle.focus({ preventScroll: true });
      }
    };

    const syncSuppressedState = () => {
      const focusInsidePanel = panel.contains(document.activeElement);
      const shouldSuppress = mobileQuery.matches
        && (document.body.classList.contains('menu-open')
          || (isEditingField(document.activeElement) && !focusInsidePanel));
      widget.classList.toggle('is-suppressed', shouldSuppress);
      if (shouldSuppress && !panel.hidden) setOpen(false, false);
    };

    toggles.forEach((toggle) => toggle.addEventListener('click', () => {
      activeToggle = toggle;
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    }));

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

  function initHeroVideo() {
    const trigger = document.querySelector('[data-hero-video-open]');
    const dialog = document.querySelector('[data-hero-video-dialog]');
    if (!(trigger instanceof HTMLButtonElement) || !(dialog instanceof HTMLDialogElement)) return;

    const closeButton = dialog.querySelector('[data-hero-video-close]');
    const frame = dialog.querySelector('[data-hero-video-frame]');
    const videoSrc = dialog.dataset.videoSrc || '';
    if (!(closeButton instanceof HTMLButtonElement) || !(frame instanceof HTMLIFrameElement) || !videoSrc) return;

    const closeVideo = () => {
      if (dialog.open) dialog.close();
    };

    trigger.addEventListener('click', () => {
      frame.src = videoSrc;
      dialog.showModal();
      document.body.classList.add('hero-video-open');
      closeButton.focus({ preventScroll: true });
    });

    closeButton.addEventListener('click', closeVideo);
    dialog.addEventListener('click', (event) => {
      if (event.target === dialog) closeVideo();
    });
    dialog.addEventListener('cancel', (event) => {
      event.preventDefault();
      closeVideo();
    });
    dialog.addEventListener('close', () => {
      frame.removeAttribute('src');
      document.body.classList.remove('hero-video-open');
      trigger.focus({ preventScroll: true });
    });
  }

  function initContactRegions() {
    document.querySelectorAll('[data-contact-region-tabs]').forEach((regionTabs) => {
      const tabs = Array.from(regionTabs.querySelectorAll('[data-contact-region-tab]'));
      const panels = Array.from(regionTabs.querySelectorAll('[data-contact-region-panel]'));
      if (!tabs.length || !panels.length) return;

      const activate = (slug, moveFocus = false) => {
        tabs.forEach((tab) => {
          const active = tab.dataset.contactRegionTab === slug;
          tab.setAttribute('aria-selected', active ? 'true' : 'false');
          tab.tabIndex = active ? 0 : -1;
          if (active && moveFocus) tab.focus({ preventScroll: true });
        });
        panels.forEach((panel) => {
          panel.hidden = panel.dataset.contactRegionPanel !== slug;
        });
      };

      tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activate(tab.dataset.contactRegionTab || ''));
        tab.addEventListener('keydown', (event) => {
          let nextIndex = null;
          if (event.key === 'ArrowRight') nextIndex = (index + 1) % tabs.length;
          if (event.key === 'ArrowLeft') nextIndex = (index - 1 + tabs.length) % tabs.length;
          if (event.key === 'Home') nextIndex = 0;
          if (event.key === 'End') nextIndex = tabs.length - 1;
          if (nextIndex === null) return;
          event.preventDefault();
          activate(tabs[nextIndex].dataset.contactRegionTab || '', true);
        });
      });
    });
  }

  function initCertificateViewers() {
    document.querySelectorAll('.sa-certificate-gallery').forEach((gallery) => {
      const dialog = gallery.querySelector('[data-certificate-dialog]');
      const closeButton = gallery.querySelector('[data-certificate-close]');
      const image = gallery.querySelector('[data-certificate-dialog-image]');
      const title = gallery.querySelector('[data-certificate-dialog-title]');
      const meta = gallery.querySelector('[data-certificate-dialog-meta]');
      if (!(dialog instanceof HTMLDialogElement) || !(closeButton instanceof HTMLButtonElement) || !(image instanceof HTMLImageElement)) return;

      let activeTrigger = null;

      const closeViewer = () => {
        if (dialog.open) dialog.close();
      };

      gallery.querySelectorAll('[data-certificate-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
          const src = trigger.dataset.certificateSrc || '';
          if (!src) return;

          activeTrigger = trigger;
          const certificateTitle = trigger.dataset.certificateTitle || '';
          const certificateScope = trigger.dataset.certificateScope || '';
          const certificateValidity = trigger.dataset.certificateValidity || '';

          image.src = src;
          image.alt = certificateTitle;
          if (title) title.textContent = certificateTitle;
          if (meta) meta.textContent = [certificateScope, certificateValidity].filter(Boolean).join(' · ');
          dialog.showModal();
          document.body.classList.add('certificate-viewer-open');
          closeButton.focus({ preventScroll: true });
        });
      });

      closeButton.addEventListener('click', closeViewer);
      dialog.addEventListener('click', (event) => {
        if (event.target === dialog) closeViewer();
      });
      dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeViewer();
      });
      dialog.addEventListener('close', () => {
        image.removeAttribute('src');
        image.alt = '';
        document.body.classList.remove('certificate-viewer-open');
        if (activeTrigger instanceof HTMLElement) activeTrigger.focus({ preventScroll: true });
        activeTrigger = null;
      });
    });
  }

  function initCertificateCarousels() {
    if (reduceMotion) return;

    document.querySelectorAll('[data-certificate-carousel]').forEach((carousel) => {
      const sourceCards = Array.from(carousel.children).filter((item) => item.matches('.sa-certificate-card'));
      if (sourceCards.length < 2 || carousel.dataset.continuousScroll === 'true') return;

      carousel.dataset.continuousScroll = 'true';
      sourceCards.forEach((card) => {
        const clone = card.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        clone.setAttribute('tabindex', '-1');
        clone.dataset.certificateClone = 'true';
        carousel.appendChild(clone);
      });

      let paused = false;
      let loopWidth = carousel.scrollWidth / 2;
      let previousTime = 0;

      const pause = () => {
        paused = true;
      };

      const resume = () => {
        paused = false;
      };

      const animate = (time) => {
        const elapsed = previousTime ? Math.min(time - previousTime, 50) : 0;
        previousTime = time;

        if (!paused && !document.hidden && loopWidth > 0) {
          carousel.scrollLeft += elapsed * 0.04;
          if (carousel.scrollLeft >= loopWidth) carousel.scrollLeft -= loopWidth;
        }

        window.requestAnimationFrame(animate);
      };

      carousel.addEventListener('mouseenter', pause);
      carousel.addEventListener('mouseleave', resume);
      carousel.addEventListener('focusin', pause);
      carousel.addEventListener('focusout', () => {
        window.setTimeout(() => {
          if (!carousel.contains(document.activeElement)) resume();
        }, 0);
      });
      carousel.addEventListener('pointerdown', pause);
      carousel.addEventListener('pointerup', resume);
      carousel.addEventListener('pointercancel', resume);
      window.addEventListener('resize', () => {
        loopWidth = carousel.scrollWidth / 2;
      });

      window.requestAnimationFrame(animate);
    });
  }

  function initBrandWindow() {
    const section = document.querySelector('[data-brand-window]');
    const image = section?.querySelector('[data-brand-window-image]');
    if (!(section instanceof HTMLElement) || !(image instanceof SVGImageElement)) return;

    if (reduceMotion) {
      image.style.setProperty('--sa-brand-image-y', '0px');
      return;
    }

    let frameRequested = false;
    const update = () => {
      frameRequested = false;
      const rect = section.getBoundingClientRect();
      const scrollRange = window.innerHeight + rect.height;
      const progress = Math.min(1, Math.max(0, (window.innerHeight - rect.top) / scrollRange));
      const offset = (progress - .5) * 140;
      image.style.setProperty('--sa-brand-image-y', offset.toFixed(2) + 'px');
    };

    const requestUpdate = () => {
      if (frameRequested) return;
      frameRequested = true;
      window.requestAnimationFrame(update);
    };

    update();
    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate);
  }

  onReady(() => {
    stabilizeInitialAnchor();
    initHeader();
    initProductMenus();
    initNavDropdowns();
    initMobileSubmenus();
    initReveal();
    initCounters();
    initProductTabs();
    initContactForms();
    initSupportWidget();
    initSearchOverlay();
    initLanguageSwitcher();
    initHeroVideo();
    initContactRegions();
    initCertificateCarousels();
    initCertificateViewers();
    initBrandWindow();
  });
})();
