/**
 * SWUC Welcome page hero (Issue #14).
 *
 * Ported from spiritwestuc/assets/src/js/main.js lines 31-99. Differences:
 *   - Defensive fallbacks if `.notices` is missing (Issue #15 not yet
 *     done locally — use the first <main> element as the insertion point
 *     and scroll target).
 *   - Targets dynamo's `.site-header` instead of spiritwestuc's
 *     `.header-desktop-landing` for the scroll-triggered background change.
 *   - Wrapped in an IIFE to avoid leaking globals.
 */
(function () {
    if (window.innerWidth < 1024) return;
    if (!document.body.classList.contains('home')) return;

    var insertionAnchor =
        document.querySelector('.notices') ||
        document.querySelector('main#main') ||
        document.querySelector('main') ||
        document.querySelector('.entry-content');
    if (!insertionAnchor) return;

    /* Hero container is inserted OUTSIDE <main> so it can span the full
       viewport width — dynamo's .dynamo-content-wrap caps inner content at
       720px, which is fine for body content but crushes the hero title. */
    var heroOuterTarget =
        document.querySelector('main.site-main') ||
        document.querySelector('main') ||
        insertionAnchor;

    var container = document.createElement('div');
    container.id = 'desktop-title-container';
    container.addEventListener('animationend', function () {
        container.classList.add('animations-visible');
    });

    function pushTitle(html) {
        var h1 = document.createElement('h1');
        h1.innerHTML = html;
        h1.classList.add('title-desktop-landing', 'text-shadow');
        container.appendChild(h1);
    }
    pushTitle('at <span>Spirit West United</span>');
    pushTitle('<span>Church</span>');

    heroOuterTarget.before(container);
    if (insertionAnchor.classList && insertionAnchor.classList.contains('notices')) {
        insertionAnchor.setAttribute('style', 'padding-top: 2rem');
    }

    var arrow = document.createElement('a');
    arrow.innerHTML =
        '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0px" y="0px" ' +
        'viewBox="-18 -17 530 500" xml:space="preserve" aria-hidden="true" focusable="false">' +
        '<g><path fill="#dfdfdf" d="M52.8,311.3c-12.8-12.8-12.8-33.4,0-46.2c6.4-6.4,14.7-9.6,23.1-9.6s16.7,3.2,23.1,9.6l113.4,113.4V32.7   c0-18,14.6-32.7,32.7-32.7c18,0,32.7,14.6,32.7,32.7v345.8L391,265.1c12.8-12.8,33.4-12.8,46.2,0c12.8,12.8,12.8,33.4,0,46.2   L268.1,480.4c-6.1,6.1-14.4,9.6-23.1,9.6c-8.7,0-17-3.4-23.1-9.6L52.8,311.3z"/></g>' +
        '</svg>';
    arrow.classList.add('arrow-desktop-landing');
    arrow.setAttribute('aria-label', 'Scroll to content');
    arrow.setAttribute('role', 'button');
    arrow.setAttribute('tabindex', '0');
    container.after(arrow);

    arrow.addEventListener('animationend', function () {
        arrow.classList.add('animations-visible');
    });
    arrow.addEventListener('click', function () {
        insertionAnchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    arrow.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            insertionAnchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    var header = document.querySelector('.site-header');
    if (header) {
        window.addEventListener('scroll', function () {
            if (window.scrollY >= window.innerHeight) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
})();
