/**
 * Turnstile loader for wp-login.php.
 *
 * Same semantics as main.js's loadTurnstile(): a single in-flight load, failed
 * residue scripts removed before retrying, 15s timeout — so one failed
 * Cloudflare probe can't wedge the login form.
 */
(function () {
  'use strict';

  const config = window.NorenSpringLogin || {};
  let loadPromise = null;

  function loadTurnstile() {
    if (window.turnstile) return Promise.resolve(window.turnstile);
    if (loadPromise) return loadPromise;

    const url = String(config.turnstileUrl || '');
    if (!url) return Promise.reject(new Error('Turnstile URL is unavailable.'));

    loadPromise = new Promise((resolve, reject) => {
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
      const fail = () => {
        if (settled) return;
        settled = true;
        cleanup();
        script.remove();
        reject(new Error('Turnstile could not be loaded.'));
      };
      const onLoad = () => {
        if (window.turnstile) {
          settled = true;
          script.dataset.springapexTurnstile = 'loaded';
          cleanup();
          resolve(window.turnstile);
          return;
        }
        fail();
      };
      const onError = () => fail();

      script.addEventListener('load', onLoad);
      script.addEventListener('error', onError);
      timeoutId = window.setTimeout(() => fail(), 15000);
      if (!existing) {
        script.src = url;
        script.async = true;
        script.defer = true;
        script.dataset.springapexTurnstile = 'loading';
        document.head.appendChild(script);
      }
    }).catch((error) => {
      loadPromise = null;
      throw error;
    });

    return loadPromise;
  }

  const widget = document.querySelector('.cf-turnstile');
  if (!widget) return;

  // 进入登录页即加载；加载失败后聚焦重试（与联系表单同策略）。无 token 的
  // 提交由服务端拒绝并给出可读错误，不会静默放行。
  loadTurnstile().catch(() => {});
  ['focusin', 'pointerdown'].forEach((type) => {
    widget.addEventListener(type, () => loadTurnstile().catch(() => {}), { once: true });
  });
})();
