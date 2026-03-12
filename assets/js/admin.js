/**
 * Simple Post Like — Admin JS
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

  /**
   * For each radio input inside a group of labelled cards or pills,
   * move the .is-selected class to whichever label wraps the checked input.
   *
   * Works for both .spl-radio-card and .spl-radio-pill —
   * they share the same pattern: <label class="..."><input type="radio">...
   */
  const syncRadioGroup = (changedInput) => {
    // Find all sibling radios with the same name.
    const name = changedInput.name;
    const radios = document.querySelectorAll(`input[type="radio"][name="${name}"]`);

    radios.forEach((radio) => {
      const label = radio.closest('.spl-radio-card, .spl-radio-pill');
      if (!label) return;

      label.classList.toggle('is-selected', radio.checked);
    });
  };

  // Attach a single delegated listener to the form.
  const form = document.querySelector('.spl-tab-content form');
  if (!form) return;

  form.addEventListener('change', (e) => {
    if (e.target.type === 'radio') {
      syncRadioGroup(e.target);
    }
  });

  // Notice auto dismissible
  const notices = document.querySelectorAll('.spl-notice');

  notices.forEach(function (notice) {
    setTimeout(function () {
      notice.style.transition = 'opacity 0.5s ease';
      notice.style.opacity = '0';

      setTimeout(function () {
        notice.remove();
      }, 500);
    }, 3000); // 3 seconds
  });

});