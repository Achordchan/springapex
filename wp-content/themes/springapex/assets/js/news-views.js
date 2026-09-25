/**
 * 新闻详情页阅读计数：页面加载后向 springapex/v1/news/{id}/view 发一次请求，
 * 用返回的合计更新页面上的「N views」。
 *
 * 同一浏览器同一篇 24 小时内只计一次（localStorage 记下计数时间）；窗口内再
 * 打开仍会请求，但带 count: false，只取最新合计，不让缓存页面上的旧数字留着。
 * 服务器另有同一 IP 10 分钟兜底和爬虫过滤（inc/news-views.php）。只有该计数的
 * 页面才带 data-news-views-url：登录后台的人和预览页没有这个属性，不发请求。
 */
(function () {
  'use strict';

  const WINDOW_MS = 24 * 60 * 60 * 1000;
  // 每篇一个键：几个标签页同时读完不同文章，各写各的，不会互相覆盖。
  // 键的个数以文章篇数为上限，过期的留着无害，不做清理。
  const STORAGE_PREFIX = 'springapex-news-view:';

  const counter = document.querySelector('[data-news-views-url]');
  if (!counter || window.navigator.webdriver) return;

  const id = String(counter.getAttribute('data-news-views-id') || '');
  const url = String(counter.getAttribute('data-news-views-url') || '');
  if (!id || !url) return;

  const now = Date.now();
  const storageKey = STORAGE_PREFIX + id;

  // 隐私模式等场景下 localStorage 不可用：照常计数，交给服务器的 IP 兜底。
  let last = 0;
  try {
    last = Number(window.localStorage.getItem(storageKey)) || 0;
  } catch (error) { /* 读不了就当没记过 */ }
  const shouldCount = !(last > now - WINDOW_MS && last <= now);

  const remember = () => {
    try {
      window.localStorage.setItem(storageKey, String(now));
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
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify({ count: shouldCount }),
  })
    .then((response) => (response.ok ? response.json() : null))
    .then((data) => {
      if (!data) return;
      // 服务器按规则没算（如同一 IP 10 分钟内已算过）也记下：这个浏览器本窗口
      // 内已经请求过。没算成（接口出错）走不到这里，下次打开会重试。只取合计的
      // 请求不记，免得每次打开都把 24 小时窗口往后顺延。
      if (shouldCount) remember();
      const total = data.total;
      const label = counter.querySelector('[data-news-views-label]');
      if (label && typeof total === 'number' && Number.isInteger(total) && total >= 0) label.textContent = format(total);
    })
    .catch(() => { /* 计数失败不影响阅读，下次打开再试 */ });
})();
