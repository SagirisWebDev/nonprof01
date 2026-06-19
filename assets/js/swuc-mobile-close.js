/**
 * SWUC mobile-menu close button.
 *
 * Injects a `<button class="dynamo-menu-close">` as a sibling of
 * `.site-title` inside `.site-branding`. Clicking it closes the open
 * mobile menu by removing `is-open` from `.menu-primary-container`
 * (the same flag dynamo's primary-nav.js toggles via the hamburger).
 *
 * CSS reveals the button only on mobile when the menu is open:
 *   .site-header:has(nav.is-open) .dynamo-menu-close { display: inline-flex }
 */
(function () {
    var container = document.querySelector('.menu-primary-container');
    var branding  = document.querySelector('.site-header .site-branding');
    if (!container || !branding) return;

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'dynamo-menu-close';
    closeBtn.setAttribute('aria-label', 'Close menu');
    closeBtn.innerHTML =
        '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="24" height="24">' +
        '<path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' +
        '</svg>';
    closeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        container.classList.remove('is-open');
        var toggle = container.querySelector('.dynamo-menu-toggle');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    });
    branding.appendChild(closeBtn);
})();
