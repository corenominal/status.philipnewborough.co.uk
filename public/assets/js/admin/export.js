document.addEventListener('DOMContentLoaded', () => {
  const copyBtn = document.getElementById('copyPromptBtn');

  if (!copyBtn) return;

  const promptText = document.querySelector('.export__prompt-text');

  copyBtn.addEventListener('click', async () => {
    if (!promptText) return;

    try {
      await navigator.clipboard.writeText(promptText.textContent.trim());

      const originalHtml = copyBtn.innerHTML;
      copyBtn.innerHTML = '<i class="bi bi-clipboard-check me-1"></i>Copied!';
      copyBtn.disabled = true;

      setTimeout(() => {
        copyBtn.innerHTML = originalHtml;
        copyBtn.disabled = false;
      }, 2000);
    } catch {
      // Fallback for browsers that don't support clipboard API
      const range = document.createRange();
      range.selectNodeContents(promptText);
      const selection = window.getSelection();
      selection.removeAllRanges();
      selection.addRange(range);
    }
  });
});
