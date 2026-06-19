<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for Issue #14: Welcome page hero (title overlay + down arrow).
 *
 * Production builds the hero by JavaScript injection on `body.home` at
 * `innerWidth >= 1024`. The JS adds:
 *   - `<div id="desktop-title-container">` with two `<h1 class="title-desktop-landing text-shadow">`
 *   - `<a class="arrow-desktop-landing">` with an inline SVG arrow
 *   - Scroll-triggered `.scrolled` class on `.site-header`
 *
 * The supporting CSS (animations, layout, color overrides) is gated to
 * `@media (min-width: 1024px)` so mobile keeps the standard layout.
 *
 * This file is the source-level regression guard for the CSS port — the
 * JS runtime behavior is verified separately in the browser.
 */
class SwucHeroAnimationsTest extends TestCase {

    private static string $css;

    public static function setUpBeforeClass(): void {
        $path = SWUC_PATH . 'assets/css/swuc-overrides.css';
        self::assertFileExists($path, "Expected dynamo stylesheet at {$path}");
        self::$css = (string) file_get_contents($path);
    }

    public function test_title_drop_in_keyframes_present(): void {
        $this->assertMatchesRegularExpression(
            '/@keyframes\s+titleDropIn\s*\{[^}]*from\s*\{[^}]*opacity\s*:\s*0[^}]*\}[^}]*to\s*\{[^}]*opacity\s*:\s*100%[^}]*\}\s*\}/s',
            self::$css,
            'Expected `@keyframes titleDropIn { from { opacity: 0 } to { opacity: 100% } }` to be present.'
        );
    }

    public function test_arrow_drop_in_keyframes_present(): void {
        $this->assertMatchesRegularExpression(
            '/@keyframes\s+arrowDropIn\s*\{[^}]*from\s*\{[^}]*opacity\s*:\s*0%[^}]*\}[^}]*to\s*\{[^}]*opacity\s*:\s*100%[^}]*\}\s*\}/s',
            self::$css,
            'Expected `@keyframes arrowDropIn { from { opacity: 0% } to { opacity: 100% } }` to be present.'
        );
    }

    public function test_desktop_title_container_rule_inside_desktop_media_query(): void {
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*1024px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?#desktop-title-container\s*\{[^}]*animation\s*:\s*titleDropIn[^}]*\}/s',
            self::$css,
            'Expected `#desktop-title-container` rule with `animation: titleDropIn ...` inside `@media (min-width: 1024px)`.'
        );
    }

    public function test_title_desktop_landing_rule_styled(): void {
        // The .title-desktop-landing rule must set a large font (e.g. font-size: 6vw)
        // and a contrasting text color so it reads over the photo.
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*1024px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?\.title-desktop-landing\s*\{[^}]*font-size\s*:\s*6vw[^}]*\}/s',
            self::$css,
            'Expected `.title-desktop-landing { font-size: 6vw }` inside `@media (min-width: 1024px)`.'
        );
    }

    public function test_arrow_desktop_landing_rule_styled(): void {
        // Circular, positioned absolutely at bottom, with arrowDropIn animation.
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*1024px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?\.arrow-desktop-landing\s*\{[^}]*animation\s*:\s*arrowDropIn[^}]*\}/s',
            self::$css,
            'Expected `.arrow-desktop-landing { animation: arrowDropIn ... }` inside `@media (min-width: 1024px)`.'
        );
    }

    public function test_animations_visible_helper_unhides_elements(): void {
        // The JS toggles `.animations-visible` on the container + arrow once
        // the keyframes finish. Without this rule the elements snap back to
        // opacity: 0 after the animation. Both selectors must set opacity: 100%.
        $this->assertMatchesRegularExpression(
            '/(?:#desktop-title-container|\.arrow-desktop-landing)\.animations-visible[^{]*\{[^}]*opacity\s*:\s*100%[^}]*\}/s',
            self::$css,
            'Expected `.animations-visible` rule on `#desktop-title-container` / `.arrow-desktop-landing` to set `opacity: 100%`.'
        );
    }

    public function test_home_site_header_scrolled_swaps_background(): void {
        // On the home page the JS adds `.scrolled` to `.site-header` once
        // the user scrolls past one viewport. The CSS must respond with a
        // solid background so the nav stays readable over the next section.
        $this->assertMatchesRegularExpression(
            '/(?:^|[\s}])body\.home\s+\.site-header\.scrolled\s*\{[^}]*background-color\s*:[^}]*\}/s',
            self::$css,
            'Expected `body.home .site-header.scrolled { background-color: ... }`.'
        );
    }

    public function test_home_site_header_default_is_transparent(): void {
        // While the hero is on-screen the header should be transparent so
        // the church photo shows through.
        $this->assertMatchesRegularExpression(
            '/(?:^|[\s}])body\.home\s+\.site-header(?:[^.\{]|\.(?!scrolled))*\{[^}]*background-color\s*:\s*transparent[^}]*\}/s',
            self::$css,
            'Expected `body.home .site-header { background-color: transparent }` (no .scrolled).'
        );
    }
}
