document.addEventListener('DOMContentLoaded', function () {
    // Highlight the active sidebar link
    document.querySelectorAll('#sidebar .nav-link').forEach((link) => {
        if (link.getAttribute('href') === '/admin') {
            link.classList.remove('text-white-50');
            link.classList.add('active');
        }
    });
});
