/** Copy the sanitized infrastructure report without adding a dependency. */
(function () {
  'use strict';

  const button = document.querySelector('[data-system-status-copy]');
  const report = document.querySelector('[data-system-status-report]');
  const result = document.querySelector('[data-system-status-copy-result]');
  if (!button || !report) return;

  button.addEventListener('click', async function () {
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(report.value);
      } else {
        report.select();
        document.execCommand('copy');
        window.getSelection()?.removeAllRanges();
      }
      if (result) result.textContent = '诊断信息已复制。';
    } catch (error) {
      if (result) result.textContent = '复制失败，请手动选择文本复制。';
    }
  });
})();
