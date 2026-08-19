/**
 * 表单字段构建器交互：添加/删除/拖拽排序/类型联动（选项框显隐、
 * 标题与 ID 跟随名称）。行名 schema[form][fields][i][…] 由保存前的
 * reindex 统一维护。
 */
(function () {
  'use strict';

  const builders = document.querySelectorAll('[data-form-builder]');
  if (!builders.length) return;

  const slugify = (text) => text.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '').slice(0, 40);

  const rowIndex = (row) => {
    const name = row.querySelector('[data-field-id]').name || '';
    const match = name.match(/\[fields\]\[(\d+)\]/);
    return match ? match[1] : '0';
  };

  const setRowNames = (row, index) => {
    const form = row.closest('[data-form-builder]').dataset.formBuilder;
    row.querySelectorAll('[name^="schema["]').forEach((input) => {
      input.name = input.name.replace(/schema\[[^\]]+\]\[fields\]\[\d+\]/, `schema[${form}][fields][${index}]`);
    });
  };

  const reindex = (builder) => {
    [...builder.querySelectorAll('[data-builder-field]')].forEach((row, index) => setRowNames(row, index));
  };

  const syncTypeUi = (row) => {
    const type = row.querySelector('[data-field-type]').value;
    const optionsInput = row.querySelector('[data-field-options]');
    const options = optionsInput ? optionsInput.closest('.builder-field__options') : null;
    if (options) {
      options.hidden = type !== 'select';
    }
  };

  const syncHead = (row) => {
    const labelInput = row.querySelector('[data-field-label]');
    const rawLabel = labelInput.value;
    const label = rawLabel || '（新字段）';
    const idInput = row.querySelector('[data-field-id]');
    row.querySelector('.builder-field__head strong').textContent = label;
    const typeSelect = row.querySelector('[data-field-type]');
    row.querySelector('.builder-field__type').textContent = typeSelect.options[typeSelect.selectedIndex].textContent;
    // 新字段的 ID 在名称输入期间持续同步，而不是首个字符后就锁死。
    // 同时避开当前表单中已有的 ID，防止保存时因重复而静默丢字段。
    if (idInput && row.dataset.autoFieldId === 'true') {
      const slug = slugify(rawLabel);
      const base = slug ? 'custom_' + slug : 'custom_field';
      const usedIds = new Set(
        [...row.closest('[data-form-builder]').querySelectorAll('[data-field-id]')]
          .filter((input) => input !== idInput)
          .map((input) => input.value)
          .filter(Boolean)
      );
      let candidate = base;
      let suffix = 2;
      while (usedIds.has(candidate)) {
        candidate = `${base}_${suffix}`;
        suffix += 1;
      }
      idInput.value = rawLabel ? candidate : '';
    }
  };

  builders.forEach((builder) => {
    const list = builder.querySelector('[data-builder-list]');
    const addButton = builder.querySelector('[data-builder-add]');
    const template = document.getElementById('springapex-builder-row-template');

    const bindRow = (row) => {
      row.querySelector('[data-field-label]').addEventListener('input', () => syncHead(row));
      row.querySelector('[data-field-type]').addEventListener('change', () => { syncTypeUi(row); syncHead(row); });
      const remove = row.querySelector('[data-builder-remove]');
      if (remove) {
        remove.addEventListener('click', () => {
          row.remove();
          reindex(builder);
        });
      }
      syncTypeUi(row);
      syncHead(row);
    };

    // 拖拽排序
    let dragged = null;
    list.addEventListener('dragstart', (event) => {
      const row = event.target.closest('[data-builder-field]');
      if (!row) return;
      dragged = row;
      row.classList.add('is-dragging');
      event.dataTransfer.effectAllowed = 'move';
      try { event.dataTransfer.setData('text/plain', 'field'); } catch (error) { /* ignore */ }
    });
    list.addEventListener('dragend', () => {
      if (dragged) dragged.classList.remove('is-dragging');
      list.querySelectorAll('.is-drop-target').forEach((el) => el.classList.remove('is-drop-target'));
      dragged = null;
    });
    list.addEventListener('dragover', (event) => {
      if (!dragged) return;
      event.preventDefault();
      const over = event.target.closest('[data-builder-field]');
      list.querySelectorAll('.is-drop-target').forEach((el) => el.classList.remove('is-drop-target'));
      if (over && over !== dragged) over.classList.add('is-drop-target');
    });
    list.addEventListener('drop', (event) => {
      if (!dragged) return;
      event.preventDefault();
      const over = event.target.closest('[data-builder-field]');
      if (over && over !== dragged) {
        const rows = [...list.querySelectorAll('[data-builder-field]')];
        if (rows.indexOf(dragged) < rows.indexOf(over)) {
          over.after(dragged);
        } else {
          over.before(dragged);
        }
        reindex(builder);
      }
    });

    addButton.addEventListener('click', () => {
      const clone = template.content.firstElementChild.cloneNode(true);
      // 模板里的表单键是占位符 __FORM__，替换为当前表单。
      clone.querySelectorAll('[name^="schema["]').forEach((input) => {
        input.name = input.name.replace('schema[__FORM__]', `schema[${builder.dataset.formBuilder}]`);
      });
      clone.querySelector('[data-field-id]').value = '';
      clone.dataset.autoFieldId = 'true';
      list.appendChild(clone);
      bindRow(clone);
      reindex(builder);
      clone.querySelector('[data-field-label]').focus();
    });

    list.querySelectorAll('[data-builder-field]').forEach(bindRow);
    reindex(builder);
  });
})();
