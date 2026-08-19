/**
 * 产品数据面板交互：标签切换 / 产品图多选与拖拽排序 / 参数行增删。
 * 字段名（springapex_gallery[i][image_id] 等）由 reindex 统一维护，
 * 提交结构与旧行编辑器完全一致，保存逻辑无需感知面板存在。
 */
(function () {
  'use strict';

  const panels = document.querySelectorAll('[data-sa-product-panel]');
  if (!panels.length) return;

  const reindex = (root) => {
    root.querySelectorAll('[data-pp-shot]').forEach((shot, index) => {
      shot.querySelectorAll('[data-pp-image-id]').forEach((input) => {
        input.name = `springapex_gallery[${index}][image_id]`;
      });
      shot.querySelectorAll('[data-pp-image-legacy]').forEach((input) => {
        input.name = `springapex_gallery[${index}][image]`;
      });
    });
    root.querySelectorAll('[data-pp-spec]').forEach((row, index) => {
      row.querySelectorAll('[data-pp-spec-label]')[0].name = `springapex_specs[${index}][label]`;
      row.querySelectorAll('[data-pp-spec-value]')[0].name = `springapex_specs[${index}][value]`;
    });
    const counts = {
      gallery: root.querySelectorAll('[data-pp-shot]').length,
      specs: root.querySelectorAll('[data-pp-spec]').length,
    };
    root.querySelectorAll('[data-pp-count="gallery"]').forEach((el) => { el.textContent = String(counts.gallery); });
    root.querySelectorAll('[data-pp-count="specs"]').forEach((el) => { el.textContent = String(counts.specs); });
  };

  panels.forEach((panel) => {
    // ---- 标签切换 ----
    const tabs = panel.querySelectorAll('[data-pp-tab]');
    const activate = (name) => {
      tabs.forEach((tab) => {
        const active = tab.dataset.ppTab === name;
        tab.classList.toggle('is-active', active);
        tab.setAttribute('aria-selected', active ? 'true' : 'false');
      });
      panel.querySelectorAll('[data-pp-panel]').forEach((section) => {
        section.hidden = section.dataset.ppPanel !== name;
      });
      // tinyMCE 在隐藏容器里初始化宽度会失真，切到正文标签时重绘一次。
      if (name === 'content' && window.tinymce) {
        const editor = window.tinymce.get('content');
        if (editor) {
          window.setTimeout(() => editor.execCommand('mceRepaint'), 0);
        }
      }
    };
    tabs.forEach((tab) => {
      tab.addEventListener('click', () => activate(tab.dataset.ppTab));
    });

    // ---- 产品图：多选添加 ----
    const gallery = panel.querySelector('[data-pp-gallery]');
    const addButton = panel.querySelector('[data-pp-add-image]');
    if (gallery && addButton) {
      addButton.addEventListener('click', () => {
        if (!window.wp || !window.wp.media) return;
        const frame = window.wp.media({
          title: '添加产品图',
          button: { text: '添加到产品图' },
          library: { type: 'image' },
          multiple: 'add',
        });
        frame.on('select', () => {
          const selection = frame.state().get('selection');
          selection.each((attachment) => {
            const figure = document.createElement('figure');
            figure.className = 'sa-pp__shot';
            figure.setAttribute('data-pp-shot', '');
            figure.draggable = true;
            figure.innerHTML = [
              '<button type="button" class="sa-pp__shot-remove" data-pp-remove-shot aria-label="移除这张图">&times;</button>',
              '<span class="sa-pp__shot-badge">主图</span>',
              '<div class="sa-pp__shot-frame"><img src="" alt="" draggable="false"></div>',
              `<input type="hidden" name="springapex_gallery[0][image_id]" value="${attachment.get('id')}" data-pp-image-id>`,
              '<input type="hidden" name="springapex_gallery[0][image]" value="" data-pp-image-legacy>',
            ].join('');
            const url = attachment.get('sizes') && attachment.get('sizes').medium
              ? attachment.get('sizes').medium.url
              : attachment.get('url');
            figure.querySelector('img').src = url || '';
            gallery.insertBefore(figure, addButton);
          });
          reindex(panel);
        });
        frame.open();
      });

      // 移除（含动态添加的）
      gallery.addEventListener('click', (event) => {
        const button = event.target.closest('[data-pp-remove-shot]');
        if (!button) return;
        button.closest('[data-pp-shot]').remove();
        reindex(panel);
      });

      // ---- 产品图：拖拽排序 ----
      let dragged = null;
      gallery.addEventListener('dragstart', (event) => {
        const shot = event.target.closest('[data-pp-shot]');
        if (!shot) return;
        dragged = shot;
        shot.classList.add('is-dragging');
        event.dataTransfer.effectAllowed = 'move';
        try {
          event.dataTransfer.setData('text/plain', 'shot');
        } catch (error) { /* IE 防御，可忽略 */ }
      });
      gallery.addEventListener('dragend', () => {
        if (dragged) dragged.classList.remove('is-dragging');
        gallery.querySelectorAll('.is-drop-target').forEach((el) => el.classList.remove('is-drop-target'));
        dragged = null;
      });
      gallery.addEventListener('dragover', (event) => {
        if (!dragged) return;
        event.preventDefault();
        const over = event.target.closest('[data-pp-shot]');
        gallery.querySelectorAll('.is-drop-target').forEach((el) => el.classList.remove('is-drop-target'));
        if (over && over !== dragged) over.classList.add('is-drop-target');
      });
      gallery.addEventListener('drop', (event) => {
        if (!dragged) return;
        event.preventDefault();
        const over = event.target.closest('[data-pp-shot]');
        if (over && over !== dragged) {
          const shots = [...gallery.querySelectorAll('[data-pp-shot]')];
          const from = shots.indexOf(dragged);
          const to = shots.indexOf(over);
          if (from < to) {
            over.after(dragged);
          } else {
            over.before(dragged);
          }
          reindex(panel);
        }
      });
    }

    // ---- 关键参数：增删行 ----
    const specs = panel.querySelector('[data-pp-specs]');
    const specAdd = panel.querySelector('[data-pp-add-spec]');
    if (specs && specAdd) {
      specAdd.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'sa-pp__spec';
        row.setAttribute('data-pp-spec', '');
        row.innerHTML = [
          '<input type="text" name="springapex_specs[0][label]" value="" placeholder="项目，如 Wire Diameter" data-pp-spec-label>',
          '<input type="text" name="springapex_specs[0][value]" value="" placeholder="数值或范围，如 0.1 – 60 mm" data-pp-spec-value>',
          '<button type="button" class="sa-pp__spec-remove" data-pp-remove-spec aria-label="删除这行">&times;</button>',
        ].join('');
        specs.insertBefore(row, specAdd);
        reindex(panel);
        row.querySelector('[data-pp-spec-label]').focus();
      });
      specs.addEventListener('click', (event) => {
        const button = event.target.closest('[data-pp-remove-spec]');
        if (!button) return;
        button.closest('[data-pp-spec]').remove();
        reindex(panel);
      });
    }

    // ---- 单图封面（案例）----
    panel.querySelectorAll('[data-pp-cover]').forEach((cover) => {
      const frameEl = cover.querySelector('[data-pp-cover-frame]');
      const input = cover.querySelector('[data-pp-cover-input]');
      const removeBtn = cover.querySelector('[data-pp-remove-cover]');
      const openPicker = () => {
        if (!window.wp || !window.wp.media) return;
        const frame = window.wp.media({
          title: '选择封面图',
          button: { text: '使用这张图' },
          library: { type: 'image' },
          multiple: false,
        });
        frame.on('select', () => {
          const attachment = frame.state().get('selection').first();
          if (!attachment || !input) return;
          input.value = String(attachment.get('id'));
          const url = attachment.get('sizes') && attachment.get('sizes').medium
            ? attachment.get('sizes').medium.url
            : attachment.get('url');
          frameEl.innerHTML = `<img src="${url || ''}" alt="" draggable="false">`;
          removeBtn.style.display = 'block';
        });
        frame.open();
      };
      frameEl.addEventListener('click', openPicker);
      frameEl.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          openPicker();
        }
      });
      removeBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        input.value = '0';
        frameEl.innerHTML = '<span class="sa-pp__add-plus" aria-hidden="true">+</span><span>选择封面图</span>';
        removeBtn.style.display = 'none';
      });
      removeBtn.style.display = input && Number(input.value) > 0 ? 'block' : 'none';
    });

    reindex(panel);
  });
})();
