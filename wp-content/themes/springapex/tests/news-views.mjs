/** 执行真实的新闻阅读计数脚本；页面、存储和网络替身仅用于离线回归。 */
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import vm from 'node:vm';

const script = readFileSync(new URL('../assets/js/news-views.js', import.meta.url), 'utf8');
const DAY = 24 * 60 * 60 * 1000;
const URL_ = 'https://example.test/wp-json/springapex/v1/news/42/view';

function storage({ broken = false, initial = {} } = {}) {
  const data = new Map(Object.entries(initial));
  return {
    data,
    getItem(key) { if (broken) throw new Error('blocked'); return data.has(key) ? data.get(key) : null; },
    setItem(key, value) { if (broken) throw new Error('blocked'); data.set(key, String(value)); },
  };
}

async function load({ now = 1_000_000_000_000, store = storage(), attrs = true, webdriver = false, response = { ok: true, total: 501 }, fails = false } = {}) {
  const label = { textContent: '500 views' };
  const attributes = attrs ? {
    'data-news-views-id': '42',
    'data-news-views-url': URL_,
    'data-news-views-one': '%s view',
    'data-news-views-other': '%s views',
  } : {};
  const counter = {
    getAttribute(name) { return name in attributes ? attributes[name] : null; },
    querySelector(selector) { return selector === '[data-news-views-label]' ? label : null; },
  };
  const requests = [];
  let settle;
  const done = new Promise((resolve) => { settle = resolve; });
  const window = {
    navigator: { webdriver },
    localStorage: store,
    fetch(url, options) {
      requests.push({ url, options });
      const result = fails
        ? Promise.reject(new Error('offline'))
        : Promise.resolve({ ok: response.ok, json: () => Promise.resolve({ total: response.total, counted: true }) });
      result.then(() => {}, () => {}).then(() => setTimeout(settle, 0));
      return result;
    },
  };
  const document = { querySelector: (selector) => (selector === '[data-news-views-url]' && attrs ? counter : null) };
  const FakeDate = { now: () => now };
  vm.runInNewContext(script, { window, document, Date: FakeDate, JSON, Number, String, Object, Array });
  if (requests.length) await done;
  return { requests, label, store };
}

const seen = (store) => JSON.parse(store.data.get('springapex-news-views') || '{}');
const body = (request) => JSON.parse(request.options.body);

test('首次打开发一次 POST，用返回的合计更新文字并记下时间', async () => {
  const { requests, label, store } = await load();
  assert.equal(requests.length, 1);
  assert.equal(requests[0].url, URL_);
  assert.equal(requests[0].options.method, 'POST');
  assert.equal(requests[0].options.credentials, 'omit');
  assert.deepEqual(body(requests[0]), { count: true });
  assert.equal(label.textContent, '501 views');
  assert.equal(seen(store)['42'], 1_000_000_000_000);
});

test('24 小时内再打开只取最新合计：不计数、刷新文字、不顺延窗口', async () => {
  const now = 1_000_000_000_000;
  const first = now - DAY + 1000;
  const store = storage({ initial: { 'springapex-news-views': JSON.stringify({ 42: first }) } });
  const { requests, label } = await load({ now, store, response: { ok: true, total: 777 } });
  assert.equal(requests.length, 1);
  assert.deepEqual(body(requests[0]), { count: false });
  assert.equal(label.textContent, '777 views');
  assert.equal(seen(store)['42'], first);
});

test('满 24 小时后重新计数', async () => {
  const now = 1_000_000_000_000;
  const store = storage({ initial: { 'springapex-news-views': JSON.stringify({ 42: now - DAY }) } });
  const { requests } = await load({ now, store });
  assert.deepEqual(body(requests[0]), { count: true });
  assert.equal(seen(store)['42'], now);
});

test('记录时间在未来（改过系统时间）视为无效，照常计数', async () => {
  const now = 1_000_000_000_000;
  const store = storage({ initial: { 'springapex-news-views': JSON.stringify({ 42: now + DAY }) } });
  assert.deepEqual(body((await load({ now, store })).requests[0]), { count: true });
});

test('写入时清掉过期记录，保留其他文章 24 小时内的记录', async () => {
  const now = 1_000_000_000_000;
  const store = storage({ initial: { 'springapex-news-views': JSON.stringify({ 7: now - DAY - 1, 8: now - 1000 }) } });
  await load({ now, store });
  assert.deepEqual(Object.keys(seen(store)).sort(), ['42', '8']);
});

test('1 次用单数，千位加逗号', async () => {
  assert.equal((await load({ response: { ok: true, total: 1 } })).label.textContent, '1 view');
  assert.equal((await load({ response: { ok: true, total: 12345 } })).label.textContent, '12,345 views');
});

test('localStorage 不可用时照常计数且不报错', async () => {
  const { requests, label } = await load({ store: storage({ broken: true }) });
  assert.equal(requests.length, 1);
  assert.equal(label.textContent, '501 views');
});

test('接口失败或网络异常：文字不变，也不记时间（下次打开再试）', async () => {
  for (const options of [{ response: { ok: false, total: 0 } }, { fails: true }]) {
    const { requests, label, store } = await load(options);
    assert.equal(requests.length, 1);
    assert.equal(label.textContent, '500 views');
    assert.equal(store.data.has('springapex-news-views'), false);
  }
});

test('返回值不是非负整数时不改文字', async () => {
  for (const total of [-1, 1.5, 'abc', null]) {
    assert.equal((await load({ response: { ok: true, total } })).label.textContent, '500 views');
  }
});

test('没有计数属性（登录后台、预览）或自动化浏览器不发请求', async () => {
  assert.equal((await load({ attrs: false })).requests.length, 0);
  assert.equal((await load({ webdriver: true })).requests.length, 0);
});
