(function () {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const config = window.NorenSpring || {};
  let turnstileLoadPromise = null;

  function loadTurnstile() {
    if (window.turnstile) return Promise.resolve(window.turnstile);
    if (turnstileLoadPromise) return turnstileLoadPromise;

    const url = String(config.turnstileUrl || '');
    if (!url) return Promise.reject(new Error('Turnstile URL is unavailable.'));

    turnstileLoadPromise = new Promise((resolve, reject) => {
      const found = document.querySelector('script[data-springapex-turnstile]');
      // 只复用仍在加载的脚本；已结束却没有 API 的元素属于失败残留，先移除。
      const existing = found instanceof HTMLScriptElement && found.dataset.springapexTurnstile === 'loading'
        ? found
        : null;
      if (found instanceof HTMLScriptElement && !existing) found.remove();
      const script = existing || document.createElement('script');
      let settled = false;
      let timeoutId = 0;
      const cleanup = () => {
        window.clearTimeout(timeoutId);
        script.removeEventListener('load', onLoad);
        script.removeEventListener('error', onError);
      };
      const fail = (message) => {
        if (settled) return;
        settled = true;
        cleanup();
        script.remove();
        reject(new Error(message));
      };
      const onLoad = () => {
        if (settled) return;
        if (window.turnstile) {
          settled = true;
          script.dataset.springapexTurnstile = 'loaded';
          cleanup();
          resolve(window.turnstile);
          return;
        }
        fail('Turnstile did not initialize.');
      };
      const onError = () => fail('Turnstile could not be loaded.');

      script.addEventListener('load', onLoad, { once: true });
      script.addEventListener('error', onError, { once: true });
      timeoutId = window.setTimeout(() => fail('Turnstile loading timed out.'), 15000);
      if (!existing) {
        script.src = url;
        script.async = true;
        script.defer = true;
        script.dataset.springapexTurnstile = 'loading';
        document.head.appendChild(script);
      }
    }).catch((error) => {
      turnstileLoadPromise = null;
      throw error;
    });

    return turnstileLoadPromise;
  }

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

  // Apple 风格滚动驱动横滑：竖向滚到此处时文案+卡片条+进度点整体钉住
  // （垂直居中），竖滚进度写入视口的原生 scrollLeft，滑到头恢复竖滚。
  // 用原生滚动而非 transform：手动左右滑动始终可用，且行程与卡片实际
  // 宽度同源（末卡必完整到位）。仅在 ≤760 且未减少动态时启用。
  function initHorizontalScrollSections() {
    const sections = Array.from(document.querySelectorAll('[data-horizontal-scroll]'));
    if (!sections.length) return;

    const mq = window.matchMedia('(max-width: 760px)');
    const disposers = [];

    const teardown = () => {
      disposers.splice(0).forEach((dispose) => dispose());
      for (const section of sections) {
        section.classList.remove('is-scroll-linked');
        const pin = section.querySelector('[data-horizontal-pin]');
        if (pin instanceof HTMLElement) pin.style.top = '';
        const viewport = section.querySelector('[data-horizontal-viewport]');
        if (!(viewport instanceof HTMLElement)) continue;
        viewport.scrollLeft = 0;
        const dots = Array.from(viewport.querySelectorAll('[data-horizontal-dots] span'));
        dots.forEach((dot, index) => dot.classList.toggle('is-active', index === 0));
        const holder = pin instanceof HTMLElement ? pin.parentElement : viewport.parentElement;
        if (holder instanceof HTMLElement) holder.style.height = '';
      }
    };

    const setup = () => {
      teardown();
      if (!mq.matches || reduceMotion) return;

      for (const section of sections) {
        const pin = section.querySelector('[data-horizontal-pin]');
        const viewport = section.querySelector('[data-horizontal-viewport]');
        if (!(pin instanceof HTMLElement) || !(viewport instanceof HTMLElement)) continue;

        // 先挂载滚动驱动布局，再按该布局量行程（sticky 的活动范围是
        // pin 的父级容器，钉住期间需要与行程等长的竖向滚动距离）。
        section.classList.add('is-scroll-linked');
        const extra = viewport.scrollWidth - viewport.clientWidth;
        if (extra <= 8) {
          section.classList.remove('is-scroll-linked');
          continue;
        }
        const holder = pin.parentElement;
        if (holder instanceof HTMLElement) {
          const naturalHeight = holder.offsetHeight;
          holder.style.height = `${naturalHeight + extra}px`;
        }

        // 文案 + 卡片轨 + 进度点整体在视口内垂直居中：
        // 钉住期间上下留白对称，不让下方出现一片突兀的空白。
        const stickyTop = Math.max(12, Math.round((window.innerHeight - pin.offsetHeight) / 2));
        pin.style.top = `${stickyTop}px`;

        const dots = Array.from(viewport.querySelectorAll('[data-horizontal-dots] span'));
        let activeDot = 0;
        let ticking = false;
        // 进度模型：rail 位置 = clamp(页面进度 + progressOffset)。
        // 手势横滑不改页面滚动（iOS 手势期间会冻结程序化滚页，双向
        // scrollBy 在真机上推不动、落定后还会回跳），只记 progressOffset；
        // 手势落定时把当前 rail 位置折算成新基准，竖滚从此无缝续走。
        // 手势与惯性期间（userDriving）公式侧不回写，避免逐帧互掰抖动。
        let writing = false;
        let userDriving = false;
        let settleTimer = 0;
        let progressOffset = 0;
        let lastSyncedLeft = viewport.scrollLeft;
        const rawProgress = () => {
          const top = holder instanceof HTMLElement
            ? holder.getBoundingClientRect().top
            : pin.getBoundingClientRect().top;
          return (stickyTop - top) / extra; // 以 sticky 起点为 0，结束点为 1
        };
        const markUserScroll = () => {
          userDriving = true;
          window.clearTimeout(settleTimer);
          settleTimer = window.setTimeout(() => {
            userDriving = false;
            // 手势落定：把手动位置折算为映射基准，竖滚从这里续走。
            progressOffset = (viewport.scrollLeft / extra) - rawProgress();
            lastSyncedLeft = viewport.scrollLeft;
            update();
          }, 140);
        };
        const onViewportScroll = () => {
          if (writing) {
            window.requestAnimationFrame(() => { writing = false; });
            return;
          }
          lastSyncedLeft = viewport.scrollLeft;
          markUserScroll();
        };
        viewport.addEventListener('scroll', onViewportScroll, { passive: true });
        disposers.push(() => viewport.removeEventListener('scroll', onViewportScroll));
        disposers.push(() => window.clearTimeout(settleTimer));

        const update = () => {
          ticking = false;
          const raw = rawProgress();
          // 滚回旅程上方（或刚进入）时清零手势偏移，重新进入从头开始。
          if (raw <= 0) {
            progressOffset = 0;
          }
          const progress = Math.min(1, Math.max(0, raw + progressOffset));
          if (!userDriving) {
            const target = Math.round(progress * extra);
            if (target !== lastSyncedLeft) {
              writing = true;
              lastSyncedLeft = target;
              viewport.scrollLeft = target;
            }
          }
          if (dots.length > 1) {
            const next = Math.round(progress * (dots.length - 1));
            if (next !== activeDot) {
              dots[activeDot]?.classList.remove('is-active');
              dots[next]?.classList.add('is-active');
              activeDot = next;
            }
          }
        };
        const onScroll = () => {
          if (!ticking) {
            ticking = true;
            window.requestAnimationFrame(update);
          }
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        disposers.push(() => window.removeEventListener('scroll', onScroll));
        update();
      }
    };

    let resizeTimer = 0;
    const onResize = () => {
      window.clearTimeout(resizeTimer);
      resizeTimer = window.setTimeout(setup, 150);
    };
    window.addEventListener('resize', onResize);
    if (typeof mq.addEventListener === 'function') {
      mq.addEventListener('change', setup);
    } else {
      mq.addListener(setup);
    }
    setup();
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
      const captcha = form.querySelector('.cf-turnstile');
      if (startedAt) startedAt.value = String(Math.floor(Date.now() / 1000));

      if (captcha) {
        const warmTurnstile = () => {
          loadTurnstile().catch(() => {
            // 提交时会给出可见错误；预加载失败不打断当前输入操作。
          });
        };
        form.addEventListener('focusin', warmTurnstile, { once: true });
        form.addEventListener('pointerdown', warmTurnstile, { once: true });
      }

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

      const clearStatus = () => {
        if (!status) return;
        status.hidden = true;
        status.textContent = '';
        status.classList.remove('is-error', 'is-success');
      };

      // Front-end debounce guard: blocks a second submit (double-click, Enter,
      // or a programmatic submit) while a request is already in flight.
      let submitting = false;

      // Show the "submitting" state ON the button itself (spinner + label),
      // not only in the status line below it, so the state is obvious. The
      // busy label can be overridden per button via data-busy-label.
      const setSubmitBusy = (busy) => {
        if (!submit) return;
        if (busy) {
          if (submit.dataset.originalHtml === undefined) {
            submit.dataset.originalHtml = submit.innerHTML;
          }
          submit.disabled = true;
          submit.setAttribute('aria-busy', 'true');
          submit.classList.add('is-submitting');
          const spinner = document.createElement('span');
          spinner.className = 'btn-spinner';
          spinner.setAttribute('aria-hidden', 'true');
          const label = document.createElement('span');
          label.className = 'btn-label';
          label.textContent = submit.getAttribute('data-busy-label') || 'Submitting…';
          submit.replaceChildren(spinner, label);
        } else {
          submit.disabled = false;
          submit.removeAttribute('aria-busy');
          submit.classList.remove('is-submitting');
          if (submit.dataset.originalHtml !== undefined) {
            submit.innerHTML = submit.dataset.originalHtml;
            delete submit.dataset.originalHtml;
          }
        }
      };

      form.addEventListener('submit', async (event) => {
      event.preventDefault();

      if (submitting) return;

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

      if (captcha && !window.turnstile) {
        showStatus('Loading the anti-spam check. Please wait a moment and submit again.', 'error');
        try {
          await loadTurnstile();
        } catch (error) {
          showStatus('The anti-spam check is temporarily unavailable. Please try again.', 'error');
        }
        return;
      }

      submitting = true;
      setSubmitBusy(true);
      // The "submitting" feedback now lives on the button, so clear any prior
      // error/success line rather than repeating it below the button.
      clearStatus();

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
        submitting = false;
        setSubmitBusy(false);
        // Turnstile tokens are single-use; refresh the widget so a follow-up
        // submission (after an error, or a second inquiry) gets a fresh token.
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
        if (panel.querySelector('.cf-turnstile')) {
          loadTurnstile().catch(() => {
            // 表单提交时展示具体错误，不在打开动画期间插入额外提示。
          });
        }
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
          const fallbackSrc = trigger.dataset.certificateFallback || '';

          activeTrigger = trigger;
          const certificateTitle = trigger.dataset.certificateTitle || '';
          const certificateScope = trigger.dataset.certificateScope || '';
          const certificateValidity = trigger.dataset.certificateValidity || '';

          image.onerror = () => {
            if (!fallbackSrc || image.src === fallbackSrc) return;
            image.onerror = null;
            image.src = fallbackSrc;
          };
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
        image.onerror = null;
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

      let loopWidth = 0;
      let previousTime = 0;
      let resizeFrame = 0;
      // scrollLeft 读回会被浏览器量化成整像素，而每帧位移不到 1px（高刷新率
      // 屏幕上只有 0.3px 左右），`scrollLeft +=` 的增量会被整个吞掉，轮播就停在
      // 原地不动。位置改用这个浮点累加器保存，每帧整体写回；用户自己滑动时
      // 再把累加器同步回真实位置。
      let position = 0;
      let lastWritten = -1;
      // 悬停、聚焦、按住是三个各自独立的暂停理由，共用一个布尔会互相解除：
      // 鼠标停在证书条上时在别处点一下，抬起就会把悬停暂停一起清掉。
      const pauseReasons = new Set();
      const activePointers = new Set();

      const pause = (reason) => {
        pauseReasons.add(reason);
      };

      const resume = (reason) => {
        pauseReasons.delete(reason);
      };

      const animate = (time) => {
        const elapsed = previousTime ? Math.min(time - previousTime, 50) : 0;
        previousTime = time;

        if (!pauseReasons.size && !document.hidden && loopWidth > 0) {
          const actual = carousel.scrollLeft;
          // iOS 的橡皮筋回弹会让 scrollLeft 短暂为负，直接抄进累加器的话要等它
          // 以 40px/s 爬回 0 才重新动起来，所以负值一律按起点算。
          if (lastWritten < 0 || Math.abs(actual - lastWritten) > 2) position = Math.max(actual, 0);
          position += elapsed * 0.04;
          if (position >= loopWidth) position %= loopWidth;
          carousel.scrollLeft = position;
          lastWritten = carousel.scrollLeft;
        } else {
          lastWritten = -1;
        }

        window.requestAnimationFrame(animate);
      };

      // 上面刚把克隆卡片写入 DOM；同步读取 scrollWidth 会迫使浏览器立刻
      // 完成样式与布局。跨两个动画帧后再测量，让首次布局自然结算，再启动
      // 连续滚动。resize 同样延后测量，避免在 resize 事件中同步求布局。
      const measureLoop = () => {
        loopWidth = carousel.scrollWidth / 2;
        previousTime = 0;
        lastWritten = -1;
      };
      const scheduleMeasure = () => {
        window.cancelAnimationFrame(resizeFrame);
        resizeFrame = window.requestAnimationFrame(() => {
          resizeFrame = window.requestAnimationFrame(() => {
            resizeFrame = 0;
            measureLoop();
          });
        });
      };
      const startAnimation = () => {
        window.requestAnimationFrame(() => {
          window.requestAnimationFrame(() => {
            measureLoop();
            window.requestAnimationFrame(animate);
          });
        });
      };

      // 悬停暂停只认真实鼠标：触摸的合成 mouseenter 之后往往等不到 mouseleave，
      // 会把证书条永久停住。pointerenter/leave 带 pointerType，可以直接筛掉。
      carousel.addEventListener('pointerenter', (event) => {
        if (event.pointerType === 'mouse') pause('hover');
      });
      carousel.addEventListener('pointerleave', (event) => {
        if (event.pointerType === 'mouse') resume('hover');
      });
      // 只有键盘焦点才暂停：触摸点开证书弹窗、关掉之后焦点会落回卡片，
      // 手机上再也等不到 focusout，无条件暂停会把证书条永久停住。
      carousel.addEventListener('focusin', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) return;
        let keyboardFocus = true;
        try {
          keyboardFocus = target.matches(':focus-visible');
        } catch (error) {
          keyboardFocus = true;
        }
        // 焦点在证书条内部从键盘转成指针（点另一张卡片）时不会有 focusout，
        // 这里不主动清掉的话键盘那次暂停就再也解不开了。
        if (keyboardFocus) pause('focus');
        else resume('focus');
      });
      carousel.addEventListener('focusout', () => {
        window.setTimeout(() => {
          if (!carousel.contains(document.activeElement)) resume('focus');
        }, 0);
      });
      carousel.addEventListener('pointerdown', (event) => {
        activePointers.add(event.pointerId);
        pause('pointer');
      });
      // 手指可能滑出轮播之后才抬起，抬起事件挂在 window 上才不会漏掉恢复；
      // 但只认在这条证书条上按下的那些指针，别处松开的指针不该解除暂停。
      // 多指按住时要等最后一根抬起才恢复，否则第二根先松开就会在第一根还
      // 拖着的时候重新开始写 scrollLeft。
      const releasePointer = (event) => {
        if (!activePointers.delete(event.pointerId)) return;
        if (!activePointers.size) resume('pointer');
      };
      window.addEventListener('pointerup', releasePointer);
      window.addEventListener('pointercancel', releasePointer);
      window.addEventListener('resize', () => scheduleMeasure());

      startAnimation();
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

  // 行业方案瀑布流：首屏之外的服务器直出卡片标记 is-deferred（仅 html.js
  // 下隐藏），滚动接近哨兵时按批显现；卡片图片原生 loading=lazy，字节
  // 开销始终只发生在可见范围附近。无 JS 时全部直出（SEO 不受损）。
  function initSolutionsLazyGrid() {
    document.querySelectorAll('[data-lazy-batch]').forEach((grid) => {
      if (!(grid instanceof HTMLElement)) return;
      const sentinel = grid.parentElement?.querySelector('[data-lazy-sentinel]');
      if (!(sentinel instanceof HTMLElement)) return;
      // 无 IntersectionObserver 的环境（旧内嵌 webview）：CSS 已在 html.js 下
      // 隐藏延迟卡片，直接全部显现，避免永久隐藏。
      if (!('IntersectionObserver' in window)) {
        grid.querySelectorAll('.is-deferred').forEach((card) => card.classList.remove('is-deferred'));
        sentinel.remove();
        return;
      }
      const batch = Math.max(1, Number(grid.dataset.lazyBatch) || 6);
      let observer = null;

      const revealBatch = () => {
        let revealed = 0;
        grid.querySelectorAll('.is-deferred').forEach((card) => {
          if (revealed >= batch) return;
          card.classList.remove('is-deferred');
          card.classList.add('is-lazy-revealed');
          revealed += 1;
        });
        if (!grid.querySelector('.is-deferred')) {
          if (observer) observer.disconnect();
          window.removeEventListener('scroll', onScroll);
          sentinel.remove();
        }
      };

      // display:none 的卡片不占高度：快速滚动可能一步跨过哨兵（它在视口
      // 上方、不再产生 intersect 变化），IO 会永远沉默——滚动兜底补显。
      const onScroll = () => {
        if (grid.querySelector('.is-deferred') && sentinel.getBoundingClientRect().bottom < 0) {
          revealBatch();
        }
      };

      // 读屏器/键盘的显式展开入口（虚拟光标不触发 focus，display:none 的
      // 卡片在其浏览模式下不可达）：点击按钮显现下一批。
      sentinel.addEventListener('click', revealBatch);

      observer = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) revealBatch();
      }, { rootMargin: '500px 0px' });
      observer.observe(sentinel);
      window.addEventListener('scroll', onScroll, { passive: true });

      // 深链（/solutions/#<slug>）指向隐藏卡片时，先把目标及其之前的批次
      // 显现出来，锚点定位才能算对位置（本初始化器在 stabilizeInitialAnchor
      // 之前运行）。
      const hash = window.location.hash;
      if (hash && hash.length > 1) {
        // 坏百分号转义（如 #%E0%A4%A）会让 decodeURIComponent 抛错并炸掉
        // 整条 onReady 初始化链——与 stabilizeInitialAnchor 同样兜底。
        let hashId = hash.slice(1);
        try {
          hashId = decodeURIComponent(hashId);
        } catch (error) {
          hashId = hash.slice(1);
        }
        const hashTarget = document.getElementById(hashId);
        if (hashTarget instanceof HTMLElement && hashTarget.classList.contains('is-deferred')) {
          while (hashTarget.classList.contains('is-deferred') && grid.querySelector('.is-deferred')) {
            revealBatch();
          }
        }
      }

      // 键盘可达性：display:none 的卡片不在 Tab 序里，正向前进会直接从
      // 最后一张可见卡跳到 CTA。焦点落在最后一张可见卡上时提前显现下一
      // 批，Tab 自然续进新卡片。
      grid.addEventListener('focusin', () => {
        const visible = grid.querySelectorAll('.solution-card:not(.is-deferred)');
        const last = visible[visible.length - 1];
        if (last instanceof HTMLElement && last.contains(document.activeElement)) {
          revealBatch();
        }
      });
    });
  }

  onReady(() => {
    initSolutionsLazyGrid();
    stabilizeInitialAnchor();
    initHeader();
    initProductMenus();
    initNavDropdowns();
    initMobileSubmenus();
    initReveal();
    initCounters();
    initHorizontalScrollSections();
    initProductTabs();
    initContactForms();
    initSupportWidget();
    initSearchOverlay();
    initHeroVideo();
    initContactRegions();
    initCertificateCarousels();
    initCertificateViewers();
    initBrandWindow();
  });
})();
