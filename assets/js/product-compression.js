(function () {
  'use strict';

  const root = document.querySelector('.sa-compression-detail');
  if (!root) return;

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function initHeroGallery() {
    const gallery = root.querySelector('[data-compression-hero-gallery]');
    if (!gallery) return;

    const picture = gallery.querySelector('.sa-compression-hero__primary picture');
    const mainImage = gallery.querySelector('.sa-compression-hero__primary img');
    const thumbs = Array.from(gallery.querySelectorAll('[data-compression-hero-thumb]'));
    if (!(mainImage instanceof HTMLImageElement) || !thumbs.length) return;
    let imageRequest = 0;

    const updateMainImage = (source, alt) => {
      const request = ++imageRequest;
      const preload = new Image();

      preload.addEventListener('load', () => {
        if (request !== imageRequest) return;
        picture?.querySelectorAll('source').forEach((item) => item.remove());
        mainImage.removeAttribute('srcset');
        mainImage.removeAttribute('sizes');
        mainImage.src = source;
        mainImage.alt = alt;
        mainImage.classList.remove('is-changing');
      }, { once: true });

      preload.addEventListener('error', () => {
        if (request !== imageRequest) return;
        mainImage.classList.remove('is-changing');
      }, { once: true });

      preload.src = source;
    };

    thumbs.forEach((thumb) => {
      thumb.addEventListener('click', () => {
        const source = thumb.dataset.image || '';
        const selected = thumb.getAttribute('aria-pressed') === 'true';
        if (!source || selected) return;

        mainImage.classList.add('is-changing');
        window.setTimeout(() => {
          updateMainImage(source, thumb.dataset.alt || '');
        }, reduceMotion ? 0 : 110);

        thumbs.forEach((item) => {
          const active = item === thumb;
          item.classList.toggle('is-active', active);
          item.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
      });
    });
  }

  function initInquiryModes() {
    const form = root.querySelector('[data-compression-inquiry]');
    if (!(form instanceof HTMLFormElement)) return;

    const buttons = Array.from(form.querySelectorAll('[data-compression-inquiry-mode]'));
    const drawingPanel = form.querySelector('[data-compression-drawing-panel]');
    const dimensionsPanel = form.querySelector('[data-compression-dimensions-panel]');
    const reviewGuides = Array.from(root.querySelectorAll('[data-compression-review-guide]'));
    const inquiryType = form.querySelector('[data-inquiry-type]');
    const fileInput = form.querySelector('[data-compression-file-input]');
    const dropzone = form.querySelector('.sa-compression-dropzone');
    if (!buttons.length || !(drawingPanel instanceof HTMLElement) || !(dimensionsPanel instanceof HTMLElement)) return;

    const activate = (mode, moveFocus) => {
      const dimensions = mode === 'dimensions';
      drawingPanel.hidden = dimensions;
      dimensionsPanel.hidden = !dimensions;
      reviewGuides.forEach((guide) => {
        if (!(guide instanceof HTMLElement)) return;
        guide.hidden = guide.dataset.compressionReviewGuide !== mode;
      });
      if (inquiryType instanceof HTMLInputElement) {
        inquiryType.value = dimensions ? 'Request a Quote' : 'Upload a Drawing';
      }
      buttons.forEach((button) => {
        const active = button.dataset.compressionInquiryMode === mode;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-selected', active ? 'true' : 'false');
        button.tabIndex = active ? 0 : -1;
        if (active && moveFocus) button.focus({ preventScroll: true });
      });
    };

    buttons.forEach((button, index) => {
      button.addEventListener('click', () => activate(button.dataset.compressionInquiryMode || 'drawing', false));
      button.addEventListener('keydown', (event) => {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
        event.preventDefault();
        const step = event.key === 'ArrowRight' ? 1 : -1;
        const target = buttons[(index + step + buttons.length) % buttons.length];
        activate(target.dataset.compressionInquiryMode || 'drawing', true);
      });
    });

    root.querySelectorAll('[data-compression-mode-link="dimensions"]').forEach((link) => {
      link.addEventListener('click', () => activate('dimensions', false));
    });

    form.addEventListener('reset', () => {
      window.setTimeout(() => activate('drawing', false), 0);
    });

    if (dropzone instanceof HTMLElement && fileInput instanceof HTMLInputElement) {
      const MAX_FILES = 10;
      const fileList = dropzone.querySelector('[data-compression-file-list]');
      const content = dropzone.querySelector('.sa-compression-dropzone__content');
      let currentFiles = new DataTransfer();

      const updateFileList = () => {
        const files = Array.from(currentFiles.items).map(item => item.getAsFile()).filter(Boolean).slice(0, MAX_FILES);
        const hasFile = files.length > 0;

        dropzone.classList.toggle('has-file', hasFile);
        if (fileList instanceof HTMLElement) {
          fileList.hidden = !hasFile;
          fileList.innerHTML = files.map((file, index) => `
            <li class="sa-compression-file-item">
              <span class="sa-compression-file-name">${file.name}</span>
              <button type="button" class="sa-compression-file-remove" data-file-index="${index}" aria-label="Remove ${file.name}">×</button>
            </li>
          `).join('');

          if (hasFile) {
            const addMoreBtn = document.createElement('button');
            addMoreBtn.type = 'button';
            addMoreBtn.className = 'sa-compression-file-add-more';
            addMoreBtn.textContent = 'Add more files';
            addMoreBtn.addEventListener('click', (e) => {
              e.preventDefault();
              fileInput.click();
            });
            fileList.appendChild(addMoreBtn);
          }

          fileList.querySelectorAll('[data-file-index]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
              e.preventDefault();
              const index = parseInt(btn.dataset.fileIndex, 10);
              const dt = new DataTransfer();
              files.forEach((f, i) => { if (i !== index) dt.items.add(f); });
              currentFiles = dt;
              fileInput.files = dt.files;
              updateFileList();
            });
          });
        }
        if (content instanceof HTMLElement) {
          content.hidden = hasFile;
        }
      };

      fileInput.addEventListener('change', () => {
        const newFiles = Array.from(fileInput.files || []);
        newFiles.forEach(f => currentFiles.items.add(f));
        fileInput.files = currentFiles.files;
        updateFileList();
      });
      ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          dropzone.classList.add('is-dragging');
        });
      });
      ['dragleave', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, () => dropzone.classList.remove('is-dragging'));
      });
      dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        if (!event.dataTransfer?.files?.length) return;
        try {
          const dt = new DataTransfer();
          const existingFiles = Array.from(fileInput.files || []);
          const newFiles = Array.from(event.dataTransfer.files);
          [...existingFiles, ...newFiles].slice(0, MAX_FILES).forEach(f => dt.items.add(f));
          fileInput.files = dt.files;
          updateFileList();
        } catch (error) {
          fileInput.click();
        }
      });
    }

    activate('drawing', false);
  }

  initHeroGallery();
  initInquiryModes();
})();
