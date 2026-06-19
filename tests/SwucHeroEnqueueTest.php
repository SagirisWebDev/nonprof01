<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for Issue #14: hero JS enqueue gating.
 *
 * The swuc-hero.js bundle should only enqueue on the front-end homepage
 * (`is_front_page() && !is_admin()`). Loading it elsewhere is wasteful
 * and on non-home pages the script's own runtime guard would no-op anyway.
 *
 * Runtime behaviour is verified separately in the browser. This file is
 * source-level only — it asserts the JS file exists and that functions.php
 * contains the correct `is_front_page()` gate.
 */
class SwucHeroEnqueueTest extends TestCase {

    private static string $functionsSrc;

    public static function setUpBeforeClass(): void {
        $path = SWUC_PATH . 'functions.php';
        self::assertFileExists($path);
        self::$functionsSrc = (string) file_get_contents($path);
    }

    public function test_swuc_hero_js_file_exists(): void {
        $this->assertFileExists(
            SWUC_PATH . 'assets/js/swuc-hero.js',
            'Expected the hero JS at assets/js/swuc-hero.js (ported from spiritwestuc/assets/src/js/main.js).'
        );
    }

    public function test_functions_enqueues_swuc_hero_handle(): void {
        $this->assertMatchesRegularExpression(
            '/wp_enqueue_script\s*\(\s*[\'"]swuc-hero[\'"]/',
            self::$functionsSrc,
            'Expected functions.php to register a wp_enqueue_script with handle `swuc-hero`.'
        );
    }

    public function test_hero_enqueue_is_gated_to_front_page(): void {
        // The enqueue must be inside an `if (is_front_page())` (or
        // `is_front_page() &&`) block, so the script never loads on inner
        // pages or in wp-admin.
        $this->assertMatchesRegularExpression(
            '/if\s*\([^)]*is_front_page\s*\([^{]+\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?wp_enqueue_script\s*\(\s*[\'"]swuc-hero[\'"]/s',
            self::$functionsSrc,
            'The swuc-hero enqueue must be inside an `if (is_front_page() ... )` block.'
        );
    }

    public function test_hero_enqueue_points_at_assets_js_swuc_hero(): void {
        $this->assertMatchesRegularExpression(
            '/wp_enqueue_script\s*\(\s*[\'"]swuc-hero[\'"]\s*,\s*[^,]*assets\/js\/swuc-hero\.js/',
            self::$functionsSrc,
            'The swuc-hero handle must point at assets/js/swuc-hero.js.'
        );
    }

    public function test_hero_enqueue_loads_in_footer(): void {
        // Hero JS is non-critical decoration — it must defer to the footer
        // so initial render isn't blocked. wp_enqueue_script's 5th arg
        // (`$in_footer`) must be truthy.
        $this->assertMatchesRegularExpression(
            '/wp_enqueue_script\s*\(\s*[\'"]swuc-hero[\'"][^;]*,\s*true\s*\)/s',
            self::$functionsSrc,
            'The swuc-hero enqueue must pass `true` for $in_footer.'
        );
    }
}
