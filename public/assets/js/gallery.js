(() => {
  'use strict';

  const grid = document.getElementById('gallery-grid');
  if (!grid) return;

  const items = Array.from(grid.querySelectorAll('.gallery-item__img'));
  if (items.length === 0) return;

  /**
   * Mark an item's inner container as loaded, fading out the shimmer and
   * fading in the image.
   *
   * @param {HTMLImageElement} img
   */
  const markLoaded = (img) => {
    const inner = img.closest('.gallery-item__inner');
    if (inner) inner.classList.add('is-loaded');
  };

  /**
   * Trigger actual image load by moving data-src → src.
   *
   * @param {HTMLImageElement} img
   */
  const loadImage = (img) => {
    const src = img.dataset.src;
    if (!src) return;

    img.addEventListener('load', () => markLoaded(img), { once: true });
    // On error still dismiss the shimmer so it doesn't spin forever.
    img.addEventListener('error', () => markLoaded(img), { once: true });

    img.src = src;
    delete img.dataset.src;
  };

  // Use IntersectionObserver for lazy loading — start fetching images
  // slightly before they scroll into view for a seamless experience.
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          loadImage(/** @type {HTMLImageElement} */ (entry.target));
          observer.unobserve(entry.target);
        });
      },
      { rootMargin: '400px 0px', threshold: 0 },
    );

    items.forEach((img) => observer.observe(img));
  } else {
    // Fallback: load all images immediately for older browsers.
    items.forEach(loadImage);
  }
})();
