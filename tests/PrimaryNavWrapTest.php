<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for Issue #11: Prevent the primary nav from wrapping at desktop widths.
 *
 * The SWUC menu has 13 top-level items. At dynamo's default body font-size
 * (1.125rem) the row overflows the 1280px header container and wraps. The fix
 * is a desktop-only override inside the SWUC section of assets/css/swuc-overrides.css:
 *
 *   @media (min-width: 922px) {
 *       .site-header nav ul {
 *           flex-wrap: nowrap;
 *           font-size: 0.9rem;
 *       }
 *   }
 *
 * 922px is the breakpoint at which the mobile hamburger disappears
 * (`@media (max-width: 921px)` in the Primary Nav block), so the rule only
 * activates when the horizontal nav is actually visible.
 *
 * Source-level assertions only (same pattern as HeroBackgroundTest /
 * SwucCssPortTest).
 */
class PrimaryNavWrapTest extends TestCase {

    private static string $css;

    public static function setUpBeforeClass(): void {
        $path = SWUC_PATH . 'assets/css/swuc-overrides.css';
        self::assertFileExists($path, "Expected dynamo stylesheet at {$path}");
        self::$css = (string) file_get_contents($path);
    }

    public function test_desktop_media_query_contains_nav_ul_rule(): void {
        // @media (min-width: 922px) { ... .site-header nav ul { ... } ... }
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*922px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?\.site-header\s+nav\s+ul\s*\{[^}]*\}/s',
            self::$css,
            'Expected a `@media (min-width: 922px)` block containing a '
            . '`.site-header nav ul { ... }` rule.'
        );
    }

    public function test_desktop_nav_ul_rule_sets_flex_wrap_nowrap(): void {
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*922px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?\.site-header\s+nav\s+ul\s*\{[^}]*flex-wrap\s*:\s*nowrap[^}]*\}/s',
            self::$css,
            'The desktop `.site-header nav ul` rule must set '
            . '`flex-wrap: nowrap` so the 13 top-level items stay on one row.'
        );
    }

    public function test_desktop_nav_ul_rule_sets_gap(): void {
        // 1.5rem gap leaves enough horizontal breathing room that the
        // scale(1.4) hover zoom on one anchor doesn't visually overlap
        // its neighbours. Combined with `font-size: 1rem`, the 13 items +
        // branding still fit at the 1024px desktop floor because the nav
        // is right-aligned (`justify-content: flex-end`) and packs to
        // content width.
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*922px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?\.site-header\s+nav\s+ul\s*\{[^}]*gap\s*:\s*1\.5rem[^}]*\}/s',
            self::$css,
            'The desktop `.site-header nav ul` rule must set `gap: 1.5rem` '
            . 'so the hover zoom on an anchor never overlaps its neighbours.'
        );
    }

    public function test_desktop_nav_ul_rule_sets_font_size(): void {
        // 1rem on desktop matches the larger nav text requested for
        // readability; the right-aligned + content-width layout keeps it
        // fitting at the 1024px floor.
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*922px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?\.site-header\s+nav\s+ul\s*\{[^}]*font-size\s*:\s*1rem[^}]*\}/s',
            self::$css,
            'The desktop `.site-header nav ul` rule must set '
            . '`font-size: 1rem` so the primary nav reads at a comfortable '
            . 'desktop size.'
        );
    }

    // (Parent dynamo_theme hamburger-breakpoint regression guard
    // removed — that base rule lives in the parent stylesheet.)

    public function test_desktop_submenu_is_hidden_by_default(): void {
        // The submenu <ul class="sub-menu"> renders inline by default, which
        // is what swells .home-submenu and .activities-submenu and forces the
        // wrap at 1280px. The desktop rule must hide submenus and only show
        // them on hover/focus.
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*922px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?\.site-header\s+nav\s+\.sub-menu\s*\{[^}]*display\s*:\s*none[^}]*\}/s',
            self::$css,
            'The desktop block must hide `.site-header nav .sub-menu` by '
            . 'default so dropdown items do not swell their parent <li>.'
        );
    }

    public function test_desktop_submenu_reveals_on_hover_or_focus(): void {
        // Either :hover or :focus-within on .home-submenu / .activities-submenu
        // must reveal the contained .sub-menu (display becomes flex/block).
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*922px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?(?:home-submenu|activities-submenu)[^{}]*:(?:hover|focus-within)[^{}]*\.sub-menu\s*\{[^}]*display\s*:\s*(?:flex|block)[^}]*\}/s',
            self::$css,
            'The desktop block must reveal `.sub-menu` on hover or '
            . 'focus-within of .home-submenu / .activities-submenu.'
        );
    }

    // (Parent dynamo_theme nav-ul base regression guard removed.)
}
