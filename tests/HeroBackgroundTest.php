<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for Issue #8: Port hero background images and fixed-background CSS.
 *
 * Source rule (from spiritwestuc/style.css) to be ported into dynamo's
 * compiled stylesheet (assets/css/swuc-overrides.css), inside the SWUC site-specific
 * section. URLs are rewritten relative to the stylesheet location, so they
 * resolve to `../images/<filename>.webp`.
 *
 *   html {
 *       background: url("../images/spirit-west-background-mobile.webp")
 *                   no-repeat center center / cover fixed padding-box;
 *   }
 *   @media (min-width: 1024px) {
 *       html {
 *           background: url("../images/spirit-west-background5.webp")
 *                       no-repeat center center / cover fixed padding-box;
 *       }
 *   }
 *
 * Source-level assertions only (same pattern as SwucCssPortTest /
 * HeaderTemplateTest / FooterTemplateTest) — we read the file contents and
 * pattern-match.
 */
class HeroBackgroundTest extends TestCase {

    private static string $css;

    public static function setUpBeforeClass(): void {
        $path = SWUC_PATH . 'assets/css/swuc-overrides.css';
        self::assertFileExists($path, "Expected dynamo stylesheet at {$path}");
        self::$css = (string) file_get_contents($path);
    }

    // -----------------------------------------------------------------------
    // AC1: Both .webp source images committed to assets/images/
    // -----------------------------------------------------------------------

    public function test_mobile_background_image_exists(): void {
        $path = SWUC_PATH . 'assets/images/spirit-west-background-mobile.webp';
        $this->assertFileExists(
            $path,
            "Expected mobile hero background image at {$path}."
        );
    }

    public function test_desktop_background_image_exists(): void {
        $path = SWUC_PATH . 'assets/images/spirit-west-background5.webp';
        $this->assertFileExists(
            $path,
            "Expected desktop hero background image at {$path}."
        );
    }

    // -----------------------------------------------------------------------
    // AC2: html { background: url(...) fixed } rule present, mobile image
    // -----------------------------------------------------------------------

    public function test_html_rule_references_mobile_background_image(): void {
        // Match an `html { ... }` rule (top-level, not inside a media query
        // block) whose body contains the mobile image filename.
        $this->assertMatchesRegularExpression(
            '/(^|[\s}])html\s*\{[^}]*spirit-west-background-mobile\.webp[^}]*\}/',
            self::$css,
            'Expected an `html { ... }` rule referencing '
            . '`spirit-west-background-mobile.webp` in assets/css/swuc-overrides.css.'
        );
    }

    public function test_html_rule_uses_fixed_background_attachment(): void {
        // The same `html { ... }` rule that references the mobile image must
        // also carry the `fixed` background-attachment keyword.
        $this->assertMatchesRegularExpression(
            '/(^|[\s}])html\s*\{[^}]*spirit-west-background-mobile\.webp[^}]*\bfixed\b[^}]*\}/',
            self::$css,
            'Expected the mobile `html { ... }` background rule to include '
            . 'the `fixed` keyword (background-attachment: fixed).'
        );
    }

    // -----------------------------------------------------------------------
    // AC3: @media (min-width: 1024px) swaps in the desktop image, fixed
    // -----------------------------------------------------------------------

    public function test_desktop_media_query_with_html_rule_exists(): void {
        // Match `@media (min-width: 1024px) { ... html { ... } ... }` where
        // the inner html rule references the desktop image.
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*1024px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?html\s*\{[^}]*spirit-west-background5\.webp[^}]*\}/s',
            self::$css,
            'Expected a `@media (min-width: 1024px)` block containing an '
            . '`html { ... }` rule that references `spirit-west-background5.webp`.'
        );
    }

    public function test_desktop_media_query_html_rule_uses_fixed_keyword(): void {
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*1024px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?html\s*\{[^}]*spirit-west-background5\.webp[^}]*\bfixed\b[^}]*\}/s',
            self::$css,
            'Expected the desktop `html { ... }` rule (inside the 1024px '
            . 'media query) to include the `fixed` keyword.'
        );
    }

    // -----------------------------------------------------------------------
    // AC5: Regression guard — the SWUC section marker comment is still there,
    // confirming the additions land in the right section.
    // -----------------------------------------------------------------------

    public function test_swuc_section_marker_comment_is_preserved(): void {
        $this->assertStringContainsString(
            'Spirit West United Church — Site-specific class rules',
            self::$css,
            'The SWUC section marker comment must remain in style.css so the '
            . 'hero background rules can be located alongside other ported '
            . 'site-specific rules.'
        );
    }
}
