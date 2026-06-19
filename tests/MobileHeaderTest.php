<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for Issue #10: Fix mobile header — hamburger off-screen + title
 * truncated with ellipsis at 375px.
 *
 * Root cause (from browser diagnosis):
 *   - `.site-branding` (title + tagline) is 391px wide at 375px viewport
 *     because the tagline "Alive with the Spirit" (110px) refuses to shrink
 *     past its intrinsic width. The 50%-min-width `.dynamo-header-menu-cart`
 *     wrapper gets pushed off-screen, so the hamburger toggle ends up at
 *     x=538.
 *   - Even with the tagline hidden, the title at 30px (h2 token) needs
 *     ~341px and can't fit alongside the hamburger inside a 360px container.
 *
 * Fix (SWUC section, inside a new `@media (max-width: 921px)` block — same
 * breakpoint the base hamburger rule uses):
 *
 *   1. `.site-header .site-description { display: none; }` — tagline only
 *      shows on desktop where there is room.
 *   2. `.site-header .site-title { font-size: 1.5rem; }` — shrink to 24px so
 *      the title fits next to the hamburger in the container's inner 328px.
 *   3. `.site-header .dynamo-header-menu-cart { min-width: 0; }` — relax the
 *      50% wrapper minimum so the wrapper collapses to just the hamburger's
 *      width, returning the surplus to branding.
 *
 * Source-level assertions only (matches PrimaryNavWrapTest / HeroBackgroundTest
 * pattern). Real-browser verification of the hamburger click happens via
 * Playwright at task time; this file is the regression guard.
 */
class MobileHeaderTest extends TestCase {

    private static string $css;

    public static function setUpBeforeClass(): void {
        $path = SWUC_PATH . 'assets/css/swuc-overrides.css';
        self::assertFileExists($path, "Expected dynamo stylesheet at {$path}");
        self::$css = (string) file_get_contents($path);
    }

    // -----------------------------------------------------------------------
    // AC: At 375px the tagline must not crowd the title.
    // -----------------------------------------------------------------------

    public function test_mobile_hides_site_description(): void {
        // A `@media (max-width: 921px) { ... .site-description { display:
        // none } ... }` rule must be present (selector may be scoped under
        // .site-header or unscoped — both acceptable).
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*max-width\s*:\s*921px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?(?:\.site-header\s+)?\.site-description\s*\{[^}]*display\s*:\s*none[^}]*\}/s',
            self::$css,
            'A `@media (max-width: 921px)` block must hide '
            . '`.site-description` so the tagline does not crowd the title '
            . 'on mobile.'
        );
    }

    // -----------------------------------------------------------------------
    // AC: At 375px the title must not be truncated with ellipsis.
    // -----------------------------------------------------------------------

    public function test_mobile_shrinks_site_title_font_size(): void {
        // Default `.site-title` font-size = h2 token (~30px at 375). Shrunk
        // to 1.5rem (24px) here so "Spirit West United Church" fits beside
        // the hamburger inside the container's 328px inner width.
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*max-width\s*:\s*921px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?\.site-header\s+\.site-title\s*\{[^}]*font-size\s*:\s*1\.5rem[^}]*\}/s',
            self::$css,
            'A `@media (max-width: 921px) { .site-header .site-title { '
            . 'font-size: 1.5rem; } }` rule must be present to shrink the '
            . 'title at mobile.'
        );
    }

    // -----------------------------------------------------------------------
    // AC: The hamburger must be visible inside the viewport at 375px.
    // Root cause was the 50%-min-width on .dynamo-header-menu-cart shoving
    // it off-screen; relax to 0 so the wrapper collapses to its hamburger.
    // -----------------------------------------------------------------------

    public function test_mobile_relaxes_menu_cart_wrapper_min_width(): void {
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*max-width\s*:\s*921px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?\.site-header\s+\.dynamo-header-menu-cart\s*\{[^}]*min-width\s*:\s*0[^}]*\}/s',
            self::$css,
            'A `@media (max-width: 921px) { .site-header '
            . '.dynamo-header-menu-cart { min-width: 0; } }` rule must be '
            . 'present so the wrapper does not reserve 50% of the container '
            . 'and push the hamburger off-screen.'
        );
    }

    // -----------------------------------------------------------------------
    // Regression guards.
    // -----------------------------------------------------------------------

    // (Parent dynamo_theme regression guard removed — the base
    // `.dynamo-menu-toggle { display: inline-flex }` mobile rule
    // lives in the parent stylesheet, not this child overrides file.)

    public function test_site_description_is_hidden_at_all_viewports(): void {
        // The SWUC header chrome rewrite hides the dynamo tagline globally
        // (production never displays "Alive with the Spirit" as text — the
        // phrase lives only in the church-photo body background image), so
        // the global `.site-header .site-title, .site-header .site-description
        // { display: none }` rule MUST be present. The mobile override
        // re-shows .site-title alone (display: block); .site-description
        // stays hidden everywhere.
        $this->assertMatchesRegularExpression(
            '/\.site-header\s+\.site-title\s*,\s*\.site-header\s+\.site-description\s*\{[^}]*display\s*:\s*none[^}]*\}/s',
            self::$css,
            'The global `.site-header .site-title, .site-header '
            . '.site-description { display: none }` rule is required so '
            . 'the tagline never renders.'
        );
        // And the mobile override that re-shows .site-title only.
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*max-width\s*:\s*921px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?\.site-header\s+\.site-title\s*\{[^}]*display\s*:\s*block[^}]*\}/s',
            self::$css,
            'The mobile `@media (max-width: 921px) { .site-header '
            . '.site-title { display: block } }` rule must re-show the '
            . 'title on small screens.'
        );
    }
}
