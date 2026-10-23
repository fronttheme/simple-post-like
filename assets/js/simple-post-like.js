/**
 * Simple Post Like - Frontend Script
 */

(() => {
  'use strict';

  const {ajax_url, nonce, text} = simplePostLike;

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.simple-post-like-button .like-btn');

    if (!button) return;

    toggleLike(button);
  });

  /**
   * Send the like/unlike request for one button.
   *
   * @param {HTMLButtonElement} button
   */
  function toggleLike(button) {
    const container = button.closest('.simple-post-like-button');

    button.disabled = true;
    button.classList.add('spl-animating');

    fetch(ajax_url, {
      method: 'POST',
      body: new URLSearchParams({
        action: 'simple_post_like',
        post_id: container.dataset.postId,
        nonce: nonce,
      }),
    })
      .then((response) => {
        if (!response.ok) throw new Error('Network error');
        return response.json();
      })
      .then((result) => {
        if (!result.success) {
          alert(result.data?.message || text.error);
          return;
        }

        updateButton(container, result.data);
      })
      .catch(() => {
        alert(text.error);
      })
      .finally(() => {
        button.classList.remove('spl-animating');
        button.disabled = false;
      });
  }

  /**
   * Show the new count and liked state on one button.
   *
   * @param {HTMLElement} container
   * @param {{has_liked: boolean, like_count_formatted: string, like_count_text: string}} data
   */
  function updateButton(container, data) {
    const label       = data.has_liked ? text.unlike : text.like;
    const button      = container.querySelector('.like-btn');
    const countEl     = container.querySelector('.like-count');
    const labelEl     = container.querySelector('.btn-label');
    const countTextEl = container.querySelector('.like-count-text');

    container.classList.toggle('post-liked', data.has_liked);

    if (countEl) {
      countEl.textContent = data.like_count_formatted;
    }

    if (countTextEl) {
      countTextEl.textContent = ' ' + data.like_count_text;
    }

    if (labelEl) {
      labelEl.textContent = ' ' + label;
    }

    button.title = label;
    button.setAttribute('aria-label', label);
  }
})();