/**
 * 可增删行的编辑器。见 inc/admin/row-editor.php。
 *
 * Rows post as field[<index>][<key>]. The index only has to be unique and ordered
 * — PHP reads the submitted array in document order — so reindexing on every
 * change keeps the markup honest and makes reordering a plain DOM move.
 */
(function () {
	'use strict';

	function reindex(container) {
		var field = container.getAttribute('data-field');
		var rows = container.querySelectorAll('[data-sa-row]');

		Array.prototype.forEach.call(rows, function (row, index) {
			var num = row.querySelector('[data-sa-row-num]');
			if (num) {
				num.textContent = '第 ' + (index + 1) + ' 条';
			}
			Array.prototype.forEach.call(row.querySelectorAll('[name]'), function (input) {
				input.name = input.name.replace(
					new RegExp('^' + field + '\\[[^\\]]*\\]'),
					field + '[' + index + ']'
				);
			});
			var up = row.querySelector('[data-sa-row-up]');
			var down = row.querySelector('[data-sa-row-down]');
			if (up) {
				up.disabled = index === 0;
			}
			if (down) {
				down.disabled = index === rows.length - 1;
			}
		});

		var empty = container.querySelector('[data-sa-rows-empty]');
		if (empty) {
			empty.hidden = rows.length > 0;
		}
	}

	function addRow(container) {
		var template = container.querySelector('[data-sa-rows-template]');
		var list = container.querySelector('[data-sa-rows-list]');
		if (!template || !list) {
			return;
		}
		var row = template.content.firstElementChild.cloneNode(true);
		list.appendChild(row);
		reindex(container);
		var firstInput = row.querySelector('input[type="text"], textarea');
		if (firstInput) {
			firstInput.focus();
		}
	}

	function pickImage(wrapper) {
		if (!window.wp || !window.wp.media) {
			return;
		}
		var frame = window.wp.media({
			title: '选择图片',
			button: { text: '用这张' },
			library: { type: 'image' },
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			var preview = wrapper.querySelector('[data-sa-image-preview]');
			var clear = wrapper.querySelector('[data-sa-image-clear]');
			wrapper.querySelector('[data-sa-image-id]').value = attachment.id;
			// The seed's relative path is no longer what this row shows; leaving it
			// would resurrect the old picture if the attachment is ever deleted.
			wrapper.querySelector('[data-sa-image-legacy]').value = '';
			if (preview) {
				preview.src =
					(attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url) ||
					attachment.url;
				preview.hidden = false;
			}
			if (clear) {
				clear.hidden = false;
			}
		});

		frame.open();
	}

	function clearImage(wrapper) {
		var preview = wrapper.querySelector('[data-sa-image-preview]');
		var clear = wrapper.querySelector('[data-sa-image-clear]');
		wrapper.querySelector('[data-sa-image-id]').value = '0';
		wrapper.querySelector('[data-sa-image-legacy]').value = '';
		if (preview) {
			preview.removeAttribute('src');
			preview.hidden = true;
		}
		if (clear) {
			clear.hidden = true;
		}
	}

	document.addEventListener('click', function (event) {
		var target = event.target;
		if (!(target instanceof Element)) {
			return;
		}

		var container = target.closest('[data-sa-rows]');
		if (!container) {
			return;
		}

		if (target.closest('[data-sa-rows-add]')) {
			event.preventDefault();
			addRow(container);
			return;
		}

		var row = target.closest('[data-sa-row]');
		if (!row) {
			return;
		}

		if (target.closest('[data-sa-row-remove]')) {
			event.preventDefault();
			row.remove();
			reindex(container);
			return;
		}

		if (target.closest('[data-sa-row-up]')) {
			event.preventDefault();
			if (row.previousElementSibling) {
				row.parentNode.insertBefore(row, row.previousElementSibling);
				reindex(container);
			}
			return;
		}

		if (target.closest('[data-sa-row-down]')) {
			event.preventDefault();
			if (row.nextElementSibling) {
				row.parentNode.insertBefore(row.nextElementSibling, row);
				reindex(container);
			}
			return;
		}

		var image = target.closest('[data-sa-image]');
		if (!image) {
			return;
		}
		if (target.closest('[data-sa-image-pick]')) {
			event.preventDefault();
			pickImage(image);
		} else if (target.closest('[data-sa-image-clear]')) {
			event.preventDefault();
			clearImage(image);
		}
	});

	document.addEventListener('DOMContentLoaded', function () {
		Array.prototype.forEach.call(document.querySelectorAll('[data-sa-rows]'), reindex);
	});
})();
