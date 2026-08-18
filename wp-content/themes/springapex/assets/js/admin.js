/**
 * 网站内容 admin screens: collapsible repeater rows, add/remove/reorder,
 * and the media picker. Vanilla JS, no build step.
 */
(function () {
  'use strict';

  var root = document.querySelector('.sa-admin');
  if (!root) {
    return;
  }

  /* Section panels --------------------------------------------------- */

  var cards = Array.prototype.slice.call(root.querySelectorAll('[data-sa-card]'));
  var toggleAll = root.querySelector('[data-sa-toggle-all]');
  var stateKey = 'springapex-admin-open:' + (location.search.match(/[?&]page=([^&]*)/) || ['', ''])[1];

  /**
   * Saving redirects back to the screen, which would collapse everything the
   * operator had opened. Remember it for the session instead.
   */
  function storeOpen() {
    try {
      sessionStorage.setItem(stateKey, JSON.stringify(cards.filter(function (card) {
        return card.open;
      }).map(function (card) {
        return card.id;
      })));
    } catch (error) {
      /* private mode or a full quota: the panels still work, just forgetfully */
    }
  }

  function syncToggleAll() {
    if (!toggleAll) {
      return;
    }
    var allOpen = cards.length > 0 && cards.every(function (card) {
      return card.open;
    });
    toggleAll.setAttribute('aria-expanded', allOpen ? 'true' : 'false');
    toggleAll.textContent = allOpen ? '全部收起' : '全部展开';
  }

  function openCard(id) {
    var card = id ? document.getElementById(id) : null;
    if (card && card.hasAttribute('data-sa-card') && !card.open) {
      card.open = true;
    }
  }

  cards.forEach(function (card) {
    card.addEventListener('toggle', function () {
      storeOpen();
      syncToggleAll();
    });
  });

  (function restoreOpen() {
    var stored = [];
    try {
      stored = JSON.parse(sessionStorage.getItem(stateKey) || '[]');
    } catch (error) {
      stored = [];
    }
    if (Array.isArray(stored)) {
      stored.forEach(openCard);
    }
    openCard(location.hash.slice(1));
    syncToggleAll();
  })();

  window.addEventListener('hashchange', function () {
    openCard(location.hash.slice(1));
  });

  /* Rows ------------------------------------------------------------- */

  function closestRepeater(el) {
    return el.closest('[data-sa-repeater]');
  }

  /**
   * Renumber a repeater's rows after add/remove/move: the visible position
   * badge, the count, and — critically — the array index inside every field
   * name, so the posted data keeps its order.
   */
  function reindex(repeater) {
    var rows = repeater.querySelector('[data-sa-rows]').children;
    var basePath = repeater.getAttribute('data-path');
    var baseName = 'springapex_content' + basePath.split('.').map(function (p) {
      return '[' + p + ']';
    }).join('');

    Array.prototype.forEach.call(rows, function (row, index) {
      var badge = row.querySelector('[data-sa-index]');
      if (badge && badge.closest('[data-sa-repeater]') === repeater) {
        badge.textContent = String(index + 1);
      }

      row.querySelectorAll('[name]').forEach(function (input) {
        var name = input.getAttribute('name');
        if (name.indexOf(baseName) !== 0) {
          return;
        }
        var rest = name.slice(baseName.length);
        // Replace only this repeater's own index segment; nested repeaters
        // keep theirs because they sit further along in `rest`.
        input.setAttribute('name', baseName + rest.replace(/^\[[^\]]*\]/, '[' + index + ']'));
      });

      var up = row.querySelector('[data-sa-move="up"]');
      var down = row.querySelector('[data-sa-move="down"]');
      if (up && up.closest('[data-sa-repeater]') === repeater) {
        up.disabled = index === 0;
      }
      if (down && down.closest('[data-sa-repeater]') === repeater) {
        down.disabled = index === rows.length - 1;
      }
    });

    var count = repeater.querySelector('.sa-repeater__count');
    if (count && count.closest('[data-sa-repeater]') === repeater) {
      count.textContent = rows.length + ' 项';
    }

    // :scope keeps a nested repeater's empty state out of this one's reach —
    // it sits inside our rows container, i.e. earlier in document order.
    var empty = repeater.querySelector(':scope > [data-sa-empty]');
    if (empty) {
      empty.hidden = rows.length > 0;
    }
  }

  /** Keep the collapsed row title in sync with the row's first text field. */
  function bindTitle(row) {
    var title = row.querySelector('[data-sa-row-title]');
    if (!title || title.closest('[data-sa-row]') !== row) {
      return;
    }
    var source = row.querySelector('.sa-row__body input[type="text"], .sa-row__body textarea');
    if (!source) {
      return;
    }
    var fallback = title.textContent;
    source.addEventListener('input', function () {
      title.textContent = source.value.trim() || fallback;
    });
  }

  root.addEventListener('click', function (event) {
    var expandAll = event.target.closest('[data-sa-toggle-all]');
    if (expandAll) {
      // Read the panels themselves: `toggle` fires asynchronously, so the
      // button's own aria-expanded can still be one click behind.
      var expand = !cards.every(function (card) {
        return card.open;
      });
      cards.forEach(function (card) {
        card.open = expand;
      });
      syncToggleAll();
      return;
    }

    // Jumping to a collapsed panel has to open it, or the anchor lands on a
    // closed summary and the operator sees nothing happen.
    var jump = event.target.closest('.sa-jump__link');
    if (jump) {
      openCard((jump.getAttribute('href') || '').slice(1));
      return;
    }

    var reset = event.target.closest('[data-sa-reset]');
    if (reset && !window.confirm('确定恢复这个页面的默认内容吗？当前保存过的修改会被清除。')) {
      event.preventDefault();
      return;
    }

    var toggle = event.target.closest('[data-sa-toggle]');
    if (toggle) {
      var body = toggle.closest('[data-sa-row]').querySelector('.sa-row__body');
      var open = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
      body.hidden = open;
      return;
    }

    var add = event.target.closest('[data-sa-add]');
    if (add) {
      var repeater = closestRepeater(add);
      if (closestRepeater(add.parentElement) !== repeater) {
        return;
      }
      var template = repeater.querySelector('[data-sa-template]');
      var rowsBox = repeater.querySelector('[data-sa-rows]');
      var html = template.innerHTML.replace(/__i__/g, String(rowsBox.children.length));
      var holder = document.createElement('div');
      holder.innerHTML = html;
      var newRow = holder.firstElementChild;
      rowsBox.appendChild(newRow);
      reindex(repeater);
      bindTitle(newRow);
      var newToggle = newRow.querySelector('[data-sa-toggle]');
      newToggle.setAttribute('aria-expanded', 'true');
      newRow.querySelector('.sa-row__body').hidden = false;
      var firstInput = newRow.querySelector('input:not([type="hidden"]), textarea, select');
      if (firstInput) {
        firstInput.focus();
      }
      return;
    }

    var remove = event.target.closest('[data-sa-remove]');
    if (remove) {
      var rowToRemove = remove.closest('[data-sa-row]');
      var label = rowToRemove.querySelector('[data-sa-row-title]');
      var name = label ? label.textContent.trim() : '这一项';
      if (!window.confirm('确定删除「' + name + '」吗？保存后就找不回来了。')) {
        return;
      }
      var owner = closestRepeater(rowToRemove);
      rowToRemove.remove();
      reindex(owner);
      return;
    }

    var move = event.target.closest('[data-sa-move]');
    if (move) {
      var row = move.closest('[data-sa-row]');
      var sibling = move.getAttribute('data-sa-move') === 'up'
        ? row.previousElementSibling
        : row.nextElementSibling;
      if (!sibling) {
        return;
      }
      if (move.getAttribute('data-sa-move') === 'up') {
        sibling.before(row);
      } else {
        sibling.after(row);
      }
      reindex(closestRepeater(row));
      return;
    }

    /* Media picker -------------------------------------------------- */

    var select = event.target.closest('[data-sa-image-select]');
    if (select) {
      openMedia(select.closest('[data-sa-image]'));
      return;
    }

    var clear = event.target.closest('[data-sa-image-remove]');
    if (clear) {
      var field = clear.closest('[data-sa-image]');
      field.querySelector('[data-sa-image-value]').value = '';
      var preview = field.querySelector('[data-sa-image-preview]');
      preview.classList.add('is-empty');
      preview.innerHTML = '<span class="sa-image__placeholder">未选择图片</span>';
      clear.classList.add('is-hidden');
      var file = field.querySelector('.sa-image__file');
      if (file) {
        file.remove();
      }
    }
  });

  function openMedia(field) {
    if (!window.wp || !window.wp.media) {
      window.alert('媒体库还没加载好，请刷新页面再试。');
      return;
    }
    var frame = wp.media({
      title: '选择图片',
      button: { text: '使用这张图片' },
      library: { type: 'image' },
      multiple: false
    });
    frame.on('select', function () {
      var item = frame.state().get('selection').first().toJSON();
      var url = (item.sizes && item.sizes.medium && item.sizes.medium.url) || item.url;
      field.querySelector('[data-sa-image-value]').value = String(item.id);
      var preview = field.querySelector('[data-sa-image-preview]');
      preview.classList.remove('is-empty');
      preview.innerHTML = '<img alt="" />';
      preview.querySelector('img').src = url;
      var remove = field.querySelector('[data-sa-image-remove]');
      if (remove) {
        remove.classList.remove('is-hidden');
      }
      var file = field.querySelector('.sa-image__file');
      if (file) {
        file.remove();
      }
    });
    frame.open();
  }

  /* Icon preview ---------------------------------------------------- */

  root.addEventListener('change', function (event) {
    var select = event.target.closest('.sa-icon-field select');
    if (!select) {
      return;
    }
    var icons = (window.springapexAdmin && window.springapexAdmin.icons) || {};
    var url = icons[select.value];
    var img = select.closest('.sa-icon-field').querySelector('[data-sa-icon-preview] img');
    if (url && img) {
      img.src = url;
    }
  });

  /* Init ------------------------------------------------------------- */

  root.querySelectorAll('[data-sa-repeater]').forEach(reindex);
  root.querySelectorAll('[data-sa-row]').forEach(function (row) {
    if (!row.closest('[data-sa-template]')) {
      bindTitle(row);
    }
  });

  /* Field search ---------------------------------------------------- */
  // The overview searches every screen (results link out to each field's page);
  // a sub-screen searches only its own page and jumps in place. Each box carries
  // its own index inline and knows its scope (the screen key, empty = global).

  function cssEscape(value) {
    return (window.CSS && CSS.escape) ? CSS.escape(value) : value;
  }

  // Open and highlight the field for a path on the current page, if present.
  function focusField(path) {
    var target = document.querySelector('[data-sa-field-path="' + cssEscape(path) + '"]') ||
      document.querySelector('[data-sa-repeater][data-path="' + cssEscape(path) + '"]');
    if (!target) {
      return false;
    }
    var card = target.closest('[data-sa-card]');
    if (card && !card.open) {
      card.open = true;
    }
    var rowBody = target.closest('.sa-row__body');
    if (rowBody && rowBody.hidden) {
      rowBody.hidden = false;
      var rowToggle = rowBody.parentElement.querySelector('[data-sa-toggle]');
      if (rowToggle) {
        rowToggle.setAttribute('aria-expanded', 'true');
      }
    }
    window.requestAnimationFrame(function () {
      target.scrollIntoView({ behavior: 'smooth', block: 'center' });
      target.classList.add('is-search-focus');
      setTimeout(function () {
        target.classList.remove('is-search-focus');
      }, 2600);
    });
    return true;
  }

  (function () {
    var box = document.querySelector('[data-sa-search]');
    var dataEl = box ? box.querySelector('[data-sa-search-data]') : null;
    if (!box || !dataEl) {
      return;
    }

    var index = [];
    try {
      index = JSON.parse(dataEl.textContent || '[]');
    } catch (error) {
      return;
    }
    var base = (window.SA_ADMIN_SEARCH && window.SA_ADMIN_SEARCH.base) || '';
    var scope = box.getAttribute('data-sa-search-scope') || '';
    var LIMIT = 40;

    var input = box.querySelector('[data-sa-search-input]');
    var clear = box.querySelector('[data-sa-search-clear]');
    var hint = box.querySelector('[data-sa-search-hint]');
    var results = box.querySelector('[data-sa-search-results]');
    if (!input || !results) {
      return;
    }

    // A result on this same page is located in place; one on another page is a
    // real link. On a sub-screen every result is same-page (scope filters it).
    function sameScreen(entry) {
      return scope !== '' && entry.screen === scope;
    }
    function linkFor(entry) {
      return base + encodeURIComponent(entry.screen) +
        '&sa-focus=' + encodeURIComponent(entry.path) + '#' + entry.anchor;
    }

    function render(keyword) {
      results.textContent = '';
      if (keyword === '') {
        results.hidden = true;
        if (clear) { clear.hidden = true; }
        if (hint) {
          hint.textContent = scope === ''
            ? '输入关键词，搜索所有页面的字段名、英文 key 或当前文字，点结果跳到对应页面。'
            : '输入关键词，搜索本页的字段名、英文 key 或当前文字，点结果就地定位。';
        }
        return;
      }
      if (clear) { clear.hidden = false; }

      var matches = index.filter(function (entry) {
        return entry.text.indexOf(keyword) !== -1;
      });
      var shown = matches.slice(0, LIMIT);

      shown.forEach(function (entry) {
        var li = document.createElement('li');
        li.className = 'sa-search__item';
        var a = document.createElement('a');
        a.className = 'sa-search__link';
        a.href = linkFor(entry);
        if (sameScreen(entry)) {
          a.addEventListener('click', function (event) {
            event.preventDefault();
            focusField(entry.path);
          });
        }

        var label = document.createElement('span');
        label.className = 'sa-search__field';
        label.textContent = entry.label;
        a.appendChild(label);

        var where = document.createElement('span');
        where.className = 'sa-search__where';
        // Within one screen the screen name is redundant; show the section only.
        where.textContent = scope === '' ? (entry.screenLabel + ' › ' + entry.section) : entry.section;
        a.appendChild(where);

        if (entry.snippet) {
          var snip = document.createElement('span');
          snip.className = 'sa-search__snippet';
          snip.textContent = entry.snippet;
          a.appendChild(snip);
        }

        li.appendChild(a);
        results.appendChild(li);
      });

      results.hidden = false;
      if (hint) {
        if (matches.length === 0) {
          hint.textContent = '没有找到匹配的字段。';
        } else if (matches.length > shown.length) {
          hint.textContent = '找到 ' + matches.length + ' 个匹配，显示前 ' + shown.length + ' 个，继续输入可缩小范围。';
        } else {
          hint.textContent = '找到 ' + matches.length + ' 个匹配。';
        }
      }
    }

    input.addEventListener('input', function () {
      render(input.value.trim().toLowerCase());
    });
    // Enter goes to the first result: locate in place, or navigate.
    input.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        var first = results.querySelector('.sa-search__link');
        if (first) {
          event.preventDefault();
          first.click();
        }
      }
    });
    if (clear) {
      clear.addEventListener('click', function () {
        input.value = '';
        render('');
        input.focus();
      });
    }
    render('');
  })();

  // Arriving from an overview result: open and highlight the target field.
  (function focusFromUrl() {
    var match = location.search.match(/[?&]sa-focus=([^&#]*)/);
    if (match) {
      focusField(decodeURIComponent(match[1]));
    }
  })();
})();
