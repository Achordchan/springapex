/**
 * 询盘邮件模板编辑器：复用 WordPress CodeMirror，提供沙箱预览、占位符插入
 * 与恢复默认。所有状态都由用户操作或输入事件驱动，不做轮询。
 */
(function () {
  'use strict';

  // wp.codeEditor 要求 DOMContentLoaded 后初始化。配置先捕获，避免等待期间
  // 依赖全局变量仍然存在。
  const config = window.springapexMailTemplateEditor || {};
  const init = () => {
    const root = document.querySelector('[data-mail-editor]');
    const bodyInput = document.getElementById('springapex_inquiry_mail_body');
    const subjectInput = document.getElementById('springapex_inquiry_mail_subject');
    if (!root || !bodyInput || !subjectInput) return;

    const modeButtons = [...root.querySelectorAll('[data-mail-mode]')];
    const panels = [...root.querySelectorAll('[data-mail-panel]')];
    const previewFrame = root.querySelector('[data-mail-preview-frame]');
    const previewSubject = root.querySelector('[data-mail-preview-subject]');
    const status = root.querySelector('[data-mail-status]');
    const form = root.closest('form');
    let editor = null;
    let previewTimer = null;

    const escapeHtml = (value) => String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    const previewRows = [
      ['姓名', '王工'],
      ['邮箱', 'wang@example.com'],
      ['公司', '示例精密制造有限公司'],
      ['电话', '+86 138 0000 0000'],
      ['国家/地区', '中国'],
      ['询盘类型', '压缩弹簧'],
      ['线径', '1.2 mm'],
      ['外径', '12 mm'],
      ['自由长度', '45 mm'],
      ['数量', '5,000 件'],
      ['材料', 'SUS304'],
      // 与 PHP 传来的示例清单保持同一个值：真实邮件里这一行带文件大小，预览
      // 里若还写旧格式，判断会以为模板没显示图纸，于是多补一段出来。
      ['图纸', config.previewDrawings || 'compression-spring-drawing.pdf (3.0 MB)'],
    ];

    const fieldsTable = '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin:4px 0 10px;">'
      + previewRows.map((row, index) => {
        const background = index % 2 === 0 ? '#ffffff' : '#f9fafb';
        return '<tr>'
          + `<td style="padding:9px 12px;background:${background};border:1px solid #eceef0;font-size:13px;color:#646970;width:38%;vertical-align:top;">${escapeHtml(row[0])}</td>`
          + `<td style="padding:9px 12px;background:${background};border:1px solid #eceef0;font-size:13px;color:#1d2327;vertical-align:top;">${escapeHtml(row[1])}</td>`
          + '</tr>';
      }).join('')
      + '</table>';

    const dimensions = ['线径：1.2 mm', '外径：12 mm', '自由长度：45 mm'].join('<br>');
    const previewVars = {
      '{fields_table}': fieldsTable,
      '{name}': '王工',
      '{email}': 'wang@example.com',
      '{company}': '示例精密制造有限公司',
      '{phone}': '+86 138 0000 0000',
      '{country}': '中国',
      '{type}': '压缩弹簧',
      '{product}': '精密压缩弹簧',
      '{industry}': '工业设备',
      '{intent}': '询价与打样',
      '{quantity}': '5,000 件',
      '{material}': 'SUS304',
      '{operating_environment}': '室内常温，轻度潮湿',
      '{dimensions}': dimensions,
      '{custom_fields}': '目标交期：30 天内',
      '{message}': '我们需要 5,000 件压缩弹簧，请协助评估可行性、交期与报价。\n图纸已随询盘上传。',
      '{document}': 'product-datasheet',
      '{drawings}': config.previewDrawings || 'compression-spring-drawing.pdf (3.0 MB)',
      '{inquiry_link}': config.previewInquiryLink || '#',
      '{site_name}': 'NorenSpring',
      '{site_url}': 'https://www.springapex.cn/',
    };

    const fillTemplate = (template) => Object.entries(previewVars).reduce(
      (filled, [token, value]) => filled.split(token).join(value),
      String(template || '')
    );

    // 与 PHP 的 springapex_inquiry_mail_with_drawing_notice() 对应：正文里既有图纸
    // 清单又有后台入口才放过，缺任一就补一段。老模板（图纸当年靠附件送达，模板里
    // 什么都不用写）实际发出的邮件会多这一块，预览必须照样显示出来，否则管理员既
    // 看不到它，也发现不了它带来的排版问题。补的 HTML 由 PHP 生成后传过来。
    const withDrawingNotice = (html) => {
      const notice = config.drawingNotice || '';
      const drawings = config.previewDrawings || '';
      const link = config.previewInquiryLink || '';
      // 收件人读不了后台时，图纸照旧作为附件发出，正文里不会补取件提示 ——
      // 预览跟着同一个条件走，否则展示的是一封根本不会存在的邮件。
      if (!config.recipientReadsBackend) return html;
      if (!notice || !drawings) return html;
      const hasList = html.includes(drawings);
      const hasLink = link !== '' && html.includes(link);
      return hasList && hasLink ? html : html + notice;
    };

    // 与 PHP 的 springapex_inquiry_mail_clarify_missing_attachments() 对应：不发
    // 附件时，正文里若还留着旧的「附件」说法，末尾会补一句澄清。顺序与发送端一致，
    // 先补取件提示再补澄清。
    const withAttachmentClarification = (html) => {
      const block = config.attachmentClarification || '';
      if (!block || !config.recipientReadsBackend || !config.previewDrawings) return html;
      const text = html.replace(/<[^>]*>/g, ' ');
      return /附件|attachment/i.test(text) ? html + block : html;
    };

    const getBody = () => editor ? editor.codemirror.getValue() : bodyInput.value;
    const setBody = (value) => {
      bodyInput.value = value;
      if (editor) editor.codemirror.setValue(value);
    };

    const updatePreview = () => {
      if (!previewFrame || !previewSubject) return;
      previewSubject.textContent = fillTemplate(subjectInput.value);
      previewFrame.srcdoc = '<!doctype html><html lang="zh"><head><meta charset="utf-8">'
        + '<meta name="viewport" content="width=device-width,initial-scale=1"></head>'
        + `<body style="margin:0;padding:0;background:#f4f7fa;">${withAttachmentClarification(withDrawingNotice(fillTemplate(getBody())))}</body></html>`;
    };

    const schedulePreview = () => {
      window.clearTimeout(previewTimer);
      previewTimer = window.setTimeout(updatePreview, 120);
    };

    const activateMode = (name) => {
      modeButtons.forEach((button) => {
        const active = button.dataset.mailMode === name;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-selected', active ? 'true' : 'false');
        button.tabIndex = active ? 0 : -1;
      });
      panels.forEach((panel) => {
        panel.hidden = panel.dataset.mailPanel !== name;
      });
      if (name === 'preview') updatePreview();
      if (name === 'code' && editor) {
        window.setTimeout(() => editor.codemirror.refresh(), 0);
      }
    };

    if (config.codeEditor && window.wp && window.wp.codeEditor) {
      editor = window.wp.codeEditor.initialize(bodyInput, config.codeEditor);
      editor.codemirror.on('change', (instance) => {
        bodyInput.value = instance.getValue();
        schedulePreview();
      });
    }

    modeButtons.forEach((button) => {
      button.addEventListener('click', () => activateMode(button.dataset.mailMode));
      button.addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        event.preventDefault();
        const current = modeButtons.indexOf(button);
        const offset = event.key === 'ArrowRight' ? 1 : -1;
        const next = modeButtons[(current + offset + modeButtons.length) % modeButtons.length];
        activateMode(next.dataset.mailMode);
        next.focus();
      });
    });

    subjectInput.addEventListener('input', schedulePreview);
    bodyInput.addEventListener('input', schedulePreview);

    root.querySelector('[data-mail-reset]')?.addEventListener('click', () => {
      if (!window.confirm('载入默认模板会覆盖当前尚未保存的邮件标题和正文，确定继续吗？')) return;
      subjectInput.value = config.defaultSubject || '';
      setBody(config.defaultBody || '');
      activateMode('preview');
      if (status) status.textContent = '默认模板已载入；点击“保存设置”后才会正式生效。';
    });

    document.querySelectorAll('[data-mail-token]').forEach((button) => {
      button.addEventListener('click', () => {
        const token = button.dataset.mailToken || '';
        activateMode('code');
        if (editor) {
          editor.codemirror.replaceSelection(token);
          editor.codemirror.focus();
        } else {
          bodyInput.setRangeText(token, bodyInput.selectionStart, bodyInput.selectionEnd, 'end');
          bodyInput.focus();
          bodyInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (status) status.textContent = `已插入占位符 ${token}`;
      });
    });

    if (form) {
      form.addEventListener('submit', () => {
        bodyInput.value = getBody();
      });
    }

    updatePreview();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
