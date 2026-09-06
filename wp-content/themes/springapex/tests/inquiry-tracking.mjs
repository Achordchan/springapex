/** 执行真实统计模块及共享表单处理器；浏览器/网络替身仅用于离线回归。 */
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import vm from 'node:vm';

const tracking = readFileSync(new URL('../assets/js/inquiry-tracking.js', import.meta.url), 'utf8');
const main = readFileSync(new URL('../assets/js/main.js', import.meta.url), 'utf8');
// 只初始化共享询盘流程，避免将无关的导航、视频和动画带入本测试。
const start = main.indexOf('  function initContactForms() {');
const end = main.indexOf('\n  function ', start + 10);
assert.ok(start >= 0 && end > start);
const contact = main.slice(start, end) + '\ninitContactForms();';

function tracker({ broken = false, immediate = true } = {}) {
  const events = [];
  const timers = new Map();
  let nextTimer = 0;
  const window = {
    setTimeout(callback) { timers.set(++nextTimer, callback); return nextTimer; },
    clearTimeout(id) { timers.delete(id); },
    dataLayer: { push(event) {
      if (broken) throw new Error('Tracking blocked');
      events.push(event);
      if (immediate) event.eventCallback();
    } },
  };
  vm.runInNewContext(tracking, { window, Set, Promise });
  return { window, events, expire() { for (const callback of timers.values()) callback(); } };
}

const receipt = (form_context = 'full', conversion_id = 'a'.repeat(64)) => ({ form_context, conversion_id });

test('三类表单仅发送白名单字段，同一成功凭据只上报一次', async () => {
  const t = tracker();
  for (const [i, context] of ['full', 'product', 'quick'].entries()) {
    const inquiry = { ...receipt(context, String(i).repeat(64)), email: 'private@example.com', message: 'private' };
    await t.window.NorenSpringInquiryTracking(inquiry);
    await t.window.NorenSpringInquiryTracking(inquiry);
  }
  assert.equal(t.events.length, 3);
  for (const event of t.events) {
    assert.equal(event.event, 'inquiry_success');
    assert.deepEqual(Object.keys(event).sort(), ['event', 'eventCallback', 'eventTimeout', 'form_context', 'inquiry_id']);
    assert.equal(JSON.stringify(event).includes('private'), false);
  }
});

test('缺失/非法凭据不产生事件，加载模块本身不会补报', async () => {
  const t = tracker();
  for (const inquiry of [undefined, {}, receipt('unknown'), receipt('full', 123), receipt('full', ['a'.repeat(64)])]) {
    await t.window.NorenSpringInquiryTracking(inquiry);
  }
  assert.equal(t.events.length, 0);
});

test('GTM 不回调、回调两次或抛错均不能阻断成功流程', async () => {
  const t = tracker({ immediate: false });
  let completed = 0;
  const pending = t.window.NorenSpringInquiryTracking(receipt()).then(() => completed++);
  t.expire();
  t.events[0].eventCallback();
  t.events[0].eventCallback();
  await pending;
  assert.equal(completed, 1);
  await tracker({ broken: true }).window.NorenSpringInquiryTracking(receipt());
});

class Element {
  constructor() {
    this.dataset = {};
    this.listeners = {};
    this.hidden = false;
    this.classList = { add() {}, remove() {}, toggle() {} };
  }
  addEventListener(name, handler) { this.listeners[name] = handler; }
  setAttribute() {}
  getAttribute() { return null; }
  removeAttribute() {}
  replaceChildren() {}
  focus() {}
  closest() { return null; }
}

function formHarness({ context = 'full', valid = true, response = { success: true, data: { inquiry: receipt(context) } }, statusCode = 200, networkError = false, oversized = false, immediate = true, deferredResponse = false } = {}) {
  const t = tracker({ immediate });
  const status = new Element();
  const button = new Element();
  const form = new Element();
  const redirects = [];
  const requests = [];
  const file = oversized ? Object.assign(new Element(), { files: [{ size: 11 * 1024 * 1024, name: 'large.png' }] }) : null;
  const fields = { '[data-form-status]': status, '[data-submit-button]': button, 'input[type="file"]': file };
  form.dataset.success = context === 'quick' ? 'inline' : 'redirect';
  form.querySelector = (selector) => fields[selector] || null;
  form.querySelectorAll = () => [];
  form.checkValidity = () => valid;
  form.reset = () => { form.resets = (form.resets || 0) + 1; };
  Object.assign(t.window, { location: { assign(url) { redirects.push(url); } } });
  class XHR {
    constructor() { this.handlers = {}; this.upload = new Element(); }
    open() {}
    setRequestHeader() {}
    addEventListener(name, handler) { this.handlers[name] = handler; }
    complete() {
      this.status = statusCode;
      this.responseText = JSON.stringify(response);
      this.handlers[networkError ? 'error' : 'load']();
    }
    send() { requests.push(this); if (!deferredResponse) this.complete(); }
  }
  const document = { querySelectorAll: () => [form], createElement: () => new Element() };
  vm.runInNewContext(contact, {
    document, window: t.window, config: { ajaxUrl: '/ajax', successUrl: '/success/' },
    HTMLElement: Element, HTMLInputElement: Element, HTMLTextAreaElement: Element, HTMLSelectElement: Element,
    XMLHttpRequest: XHR, FormData: class { append() {} },
    Promise, Date, Set, Error,
  });
  return { ...t, form, status, redirects, requests, submit: () => form.listeners.submit({ preventDefault() {} }) };
}

test('必填失败、附件超限、服务器拒绝和网络错误全部零转化', async () => {
  for (const options of [
    { valid: false }, { oversized: true }, { networkError: true },
    { statusCode: 403, response: { success: false, data: { message: 'captcha' } } },
    { statusCode: 422, response: { success: false, data: { message: 'invalid attachment' } } },
    { statusCode: 500, response: { success: false, data: { message: 'storage failed' } } },
  ]) {
    const h = formHarness(options);
    await h.submit();
    assert.equal(h.events.length, 0);
    assert.equal(h.redirects.length, 0);
    assert.equal(h.form.resets || 0, 0);
  }
});

test('三个真实处理器分支均在成功响应后上报，快速询盘保持原页面', async () => {
  for (const context of ['full', 'product', 'quick']) {
    const h = formHarness({ context });
    assert.equal(h.events.length, 0);
    await h.submit();
    assert.equal(h.events.length, 1);
    assert.equal(h.events[0].form_context, context);
    assert.equal(h.redirects.length, context === 'quick' ? 0 : 1);
  }
});

test('连续点击只产生一个在途请求，跳转等待统计窗口结束', async () => {
  const h = formHarness({ deferredResponse: true, immediate: false });
  const first = h.submit();
  await h.submit();
  assert.equal(h.requests.length, 1);
  assert.equal(h.events.length, 0);
  h.requests[0].complete();
  await Promise.resolve();
  await Promise.resolve();
  assert.equal(h.events.length, 1);
  assert.equal(h.redirects.length, 0);
  await h.submit();
  assert.equal(h.requests.length, 1);
  h.expire();
  await first;
  assert.deepEqual(h.redirects, ['/success/']);
});

test('旧响应不误报，已保存但邮件失败的询盘照常统计', async () => {
  const old = formHarness({ response: { success: true, data: { message: 'received' } } });
  await old.submit();
  assert.equal(old.events.length, 0);
  assert.deepEqual(old.redirects, ['/success/']);
  const saved = formHarness({ response: { success: true, data: { inquiry: receipt(), sent: false } } });
  await saved.submit();
  assert.equal(saved.events.length, 1);
});
