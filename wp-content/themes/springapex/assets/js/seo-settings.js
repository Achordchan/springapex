/** SEO character counters and search-result previews. */
(function () {
  'use strict';

  document.querySelectorAll('[data-seo-scope]').forEach((scope) => {
    const fields = [...scope.querySelectorAll('[data-seo-field]')];
    const titleField = fields.find((field) => field.name.includes('[title]') || field.name === 'springapex_seo_title');
    const descriptionField = fields.find((field) => field.name.includes('[description]') || field.name === 'springapex_seo_description');
    const previewTitle = scope.querySelector('[data-seo-preview-title]');
    const previewDescription = scope.querySelector('[data-seo-preview-description]');

    const updateField = (field) => {
      const wrapper = field.closest('.sa-seo-field');
      const counter = wrapper?.querySelector('[data-seo-count]');
      const recommended = Number(field.dataset.seoRecommended || 0);
      if (counter) counter.textContent = String(field.value.length);
      if (wrapper) wrapper.classList.toggle('is-over-recommended', recommended > 0 && field.value.length > recommended);
    };

    const updatePreview = () => {
      if (previewTitle && titleField) {
        previewTitle.textContent = titleField.value.trim() || titleField.dataset.seoDefault || '';
      }
      if (previewDescription && descriptionField) {
        previewDescription.textContent = descriptionField.value.trim() || descriptionField.dataset.seoDefault || '';
      }
    };

    fields.forEach((field) => {
      updateField(field);
      field.addEventListener('input', () => {
        updateField(field);
        updatePreview();
      });
    });
    updatePreview();
  });
})();
