/**
 * Simple Post Like - Frontend Script
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const likeContainers = document.querySelectorAll('.simple-post-like-button');

  if (!likeContainers.length) return;

  const {ajax_url, nonce, button_display, button_text} = simplePostLike;

  likeContainers.forEach((container) => {
    const postId = container.dataset.postId;
    const likeCountEl = container.querySelector('.like-count');
    const button = container.querySelector('.like-btn');
    const btnContent = button.querySelector('.button-content');
    const btnLabel = button.querySelector('.btn-label');

    // Sync initial button state from server-rendered HTML class.
    syncButtonState(container.classList.contains('post-liked'));

    button.addEventListener('click', () => {
      button.disabled = true;
      button.classList.add('spl-animating');

      fetch(ajax_url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
          action: 'simple_post_like',
          post_id: postId,
          nonce: nonce,
        }),
      })
        .then((res) => {
          if (!res.ok) throw new Error('Network error');
          return res.json();
        })
        .then((data) => {
          if (!data.success) return;

          const {like_count_formatted, has_liked} = data.data;

          // Update count display — using pre-formatted value from server.
          if (likeCountEl) {
            likeCountEl.textContent = like_count_formatted;
          }

          // Toggle liked state.
          container.classList.toggle('post-liked', has_liked);
          syncButtonState(has_liked);
        })
        .catch(() => {
          alert(button_text.catch_alert);
        })
        .finally(() => {
          button.classList.remove('spl-animating');
          button.disabled = false;
        });
    });

    /**
     * Sync icon + label + title to match current liked state.
     *
     * @param {boolean} isLiked
     */
    function syncButtonState(isLiked) {
      const isIconMode = button_display === 'icon_only' || button_display === 'icon_counter';
      const iconClass = isLiked ? 'fa-solid' : 'fa-regular';
      const title = isLiked ? button_text.title_unlike : button_text.title_like;
      const label = isLiked ? button_text.text_unlike : button_text.text_like;

      // Update icon.
      const icon = btnContent.querySelector('i');
      if (icon) {
        icon.className = `${iconClass} fa-heart`;
      }

      // Update label text (button_default mode only).
      if (!isIconMode && btnLabel) {
        btnLabel.textContent = ' ' + label;
      }

      // Update button title + aria-label.
      button.title = title;
      button.setAttribute('aria-label', title);
    }
  });
});