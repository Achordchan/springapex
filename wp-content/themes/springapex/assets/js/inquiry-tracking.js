/**
 * 有效询盘的唯一统计入口。仅接收服务端成功响应中的匿名凭据，
 * 不读取表单字段、不监听点击、不在成功页加载时补报。
 */
(function () {
  'use strict';

  const reported = new Set();
  const contexts = new Set(['full', 'product', 'quick']);
  const timeout = 1200;

  window.NorenSpringInquiryTracking = (inquiry) => {
    if (!inquiry || typeof inquiry.conversion_id !== 'string' || !/^[a-f0-9]{64}$/.test(inquiry.conversion_id)
      || !contexts.has(inquiry.form_context) || reported.has(inquiry.conversion_id)) {
      return Promise.resolve();
    }
    reported.add(inquiry.conversion_id);

    return new Promise((resolve) => {
      let finished = false;
      let timer;
      const finish = () => {
        if (finished) return;
        finished = true;
        window.clearTimeout(timer);
        resolve();
      };
      // GTM 不可用或被拦截时不妨碍业务成功；回调可能被多个容器调用。
      timer = window.setTimeout(finish, timeout);
      try {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          event: 'inquiry_success',
          inquiry_id: inquiry.conversion_id,
          form_context: inquiry.form_context,
          eventCallback: finish,
          eventTimeout: timeout,
        });
      } catch (error) {
        finish();
      }
    });
  };
})();
