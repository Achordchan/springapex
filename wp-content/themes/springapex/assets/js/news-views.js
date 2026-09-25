/**
 * 新闻详情页阅读计数：页面加载后向 springapex/v1/news/{id}/view 发一次请求，
 * 用返回的合计更新页面上的「N views」。
 *
 * 同一浏览器同一篇 24 小时内只发一次（localStorage 记下计数时间）；服务器另有
 * 同一 IP 10 分钟兜底和爬虫过滤（inc/news-views.php）。只有该计数的页面才带
 * data-news-views-url：登录后台的人和预览页没有这个属性，也就不会发请求。
 */
(function () {
  'use strict';

  const WINDOW_MS = 24 * 60 * 60 * 1000;
  const STORAGE_KEY = 'springapex-news-views';

  const counter = document.querySelector('[data-news-views-url]');
  if (!counter || window.navigator.webdriver) return;

  const id = String(counter.getAttribute('data-news-views-id') || '');
  const url = String(counter.getAttribute('data-news-views-url') || '');
  if (!id || !url) return;

  const now = Date.now();

  // 隐私模式等场景下 localStorage 不可用：照常计数，交给服务器的 IP 兜底。
  const readSeen = () => {
    try {
      const stored = JSON.parse(window.localStorage.getItem(STORAGE_KEY) || '{}');
      return stored && typeof stored === 'object' && !Array.isArray(stored) ? stored : {};
    } catch (error) {
      return {};
    }
  };

  const last = Number(readSeen()[id]) || 0;
  if (last > now - WINDOW_MS && last <= now) return;

  const remember = () => {
    const seen = readSeen();
    Object.keys(seen).forEach((key) => {
      const time = Number(seen[key]);
      if (!(time > now - WINDOW_MS && time <= now)) delete seen[key];
    });
    seen[id] = now;
    try {
      window.localStorage.setItem(STORAGE_KEY, JSON.stringify(seen));
    } catch (error) { /* 同上：存不了就不存 */ }
  };

  const format = (total) => {
    const template = total === 1
      ? counter.getAttribute('data-news-views-one')
      : counter.getAttribute('data-news-views-other');
    return String(template || '%s').replace('%s', total.toLocaleString('en-US'));
  };

  window.fetch(url, {
    method: 'POST',
    credentials: 'omit',
    keepalive: true,
    headers: { Accept: 'application/json' },
  })
    .then((response) => (response.ok ? response.json() : null))
    .then((data) => {
      if (!data) return;
      // 服务器按 IP 兜底没算这一次时也记下：这个浏览器本窗口内已经请求过。
      remember();
      const total = data.total;
      const label = counter.querySelector('[data-news-views-label]');
      if (label && typeof total === 'number' && Number.isInteger(total) && total >= 0) label.textContent = format(total);
    })
    .catch(() => { /* 计数失败不影响阅读，下次打开再试 */ });
})();
