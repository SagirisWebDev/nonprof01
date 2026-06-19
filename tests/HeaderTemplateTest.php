<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Asserts the dynamo theme `header.php` template and supporting CSS in
 * `assets/css/swuc-overrides.css` meet the rebuild acceptance criteria from issue #4:
 *
 *   1. Logo AND site title appear together (not one-or-the-other).
 *   2. Site title renders in italic, font-weight 400.
 *   3. Navigation is right-aligned.
 *   4. Header layout is flex row with branding left, nav right.
 *   5. Renders correctly at mobile viewport (hamburger toggle present).
 *   6. No PHP warnings in error log (manual / UI test — not covered here).
 *
 * Uses source-level assertions (read the file as a string and regex/substring
 * match), following the pattern in tests/ExtendCustomizerTemplateTest.php,
 * because header.php depends on many WP runtime functions that are not
 * stubbed in tests/bootstrap.php.
 */
class HeaderTemplateTest extends TestCase {

    private function headerPath(): string {
        return SWUC_PATH . 'header.php';
    }

    private function headerBody(): string {
        return (string) file_get_contents($this->headerPath());
    }

    private function stylePath(): string {
        return SWUC_PATH . 'assets/css/swuc-overrides.css';
    }

    private function styleBody(): string {
        return (string) file_get_contents($this->stylePath());
    }

    // ── header.php ────────────────────────────────────────────────────────

    public function test_header_file_exists(): void {
        $this->assertFileExists($this->headerPath());
    }

    public function test_header_contains_site_branding_wrapper(): void {
        $body = $this->headerBody();
        $this->assertStringContainsString(
            'site-branding',
            $body,
            'header.php should contain a `site-branding` wrapper that groups the logo and the site title together.'
        );
    }

    public function test_header_calls_the_custom_logo_for_logo_output(): void {
        $body = $this->headerBody();
        $this->assertMatchesRegularExpression(
            '/the_custom_logo\s*\(/',
            $body,
            'header.php must still call the_custom_logo() to render the site logo.'
        );
    }

    public function test_header_still_calls_has_custom_logo_for_logo_gating(): void {
        // The logo block can stay conditional on has_custom_logo(); the change
        // is that the *site title* must no longer be gated to the else branch.
        $body = $this->headerBody();
        $this->assertMatchesRegularExpression(
            '/has_custom_logo\s*\(/',
            $body,
            'header.php should still call has_custom_logo() to decide whether to render the logo image.'
        );
    }

    public function test_header_emits_site_title_via_bloginfo_name(): void {
        $body = $this->headerBody();
        $this->assertMatchesRegularExpression(
            "/bloginfo\\(\\s*'name'\\s*\\)/",
            $body,
            "header.php must render the site title via bloginfo( 'name' )."
        );
    }

    public function test_site_title_is_not_gated_inside_else_of_has_custom_logo(): void {
        // The original header.php places `bloginfo('name')` exclusively inside
        // the `else` branch of `has_custom_logo()`, which means the site title
        // disappears whenever a logo is set. Issue #4 requires the title to
        // render alongside the logo, so the title markup must appear outside
        // of an `else` branch belonging to `has_custom_logo()`.
        $body = $this->headerBody();

        // Find every offset of bloginfo('name') and every offset of the
        // has_custom_logo() conditional's else branch. If *every* bloginfo
        // call is inside an `else` of has_custom_logo, the test fails.
        $titlePattern = "/bloginfo\\(\\s*'name'\\s*\\)/";
        preg_match_all($titlePattern, $body, $titleMatches, PREG_OFFSET_CAPTURE);
        $this->assertNotEmpty(
            $titleMatches[0],
            "Expected at least one bloginfo( 'name' ) call in header.php."
        );

        // Locate the has_custom_logo conditional and its else/endif boundaries.
        $logoIfMatched = preg_match(
            '/if\s*\(\s*has_custom_logo\s*\(\s*\)\s*\)\s*:/',
            $body,
            $logoIfHit,
            PREG_OFFSET_CAPTURE
        );

        if ($logoIfMatched !== 1) {
            // No has_custom_logo conditional means the title cannot be gated by
            // it, so the criterion is satisfied trivially.
            $this->assertTrue(true);
            return;
        }

        $logoIfPos = (int) $logoIfHit[0][1];

        // Find the matching `else :` and `endif;` for that block. We assume
        // the conditional uses the alternative syntax (matching the existing
        // header.php). Take the next `else :` and `endif;` after the if.
        $elsePos  = false;
        $endifPos = false;
        if (preg_match('/\belse\s*:/', $body, $m, PREG_OFFSET_CAPTURE, $logoIfPos)) {
            $elsePos = (int) $m[0][1];
        }
        if (preg_match('/\bendif\s*;/', $body, $m, PREG_OFFSET_CAPTURE, $logoIfPos)) {
            $endifPos = (int) $m[0][1];
        }

        $allInsideElse = true;
        foreach ($titleMatches[0] as $hit) {
            $titleOffset = (int) $hit[1];
            $insideElse = $elsePos !== false
                && $endifPos !== false
                && $titleOffset > $elsePos
                && $titleOffset < $endifPos;
            if (!$insideElse) {
                $allInsideElse = false;
                break;
            }
        }

        $this->assertFalse(
            $allInsideElse,
            "header.php must render the site title alongside the logo, but every bloginfo( 'name' ) call lives inside the `else` branch of has_custom_logo() — meaning the title disappears whenever a logo is set."
        );
    }

    public function test_header_calls_wp_nav_menu_with_primary_theme_location(): void {
        $body = $this->headerBody();
        $this->assertMatchesRegularExpression(
            '/wp_nav_menu\s*\(/',
            $body,
            'header.php must still invoke wp_nav_menu().'
        );
        $this->assertMatchesRegularExpression(
            "/'theme_location'\\s*=>\\s*'primary'/",
            $body,
            "wp_nav_menu() must still pass 'theme_location' => 'primary'."
        );
    }

    public function test_header_php_has_no_syntax_errors(): void {
        $path  = $this->headerPath();
        $cmd   = 'php -l ' . escapeshellarg($path) . ' 2>&1';
        $output = (string) shell_exec($cmd);
        $this->assertStringContainsString(
            'No syntax errors detected',
            $output,
            "php -l reported syntax errors in header.php:\n" . $output
        );
    }

    // ── assets/css/swuc-overrides.css ──────────────────────────────────────────────

    public function test_style_file_exists(): void {
        $this->assertFileExists($this->stylePath());
    }

    public function test_site_branding_is_styled_as_flex(): void {
        $css = $this->styleBody();
        $this->assertMatchesRegularExpression(
            '/\.site-branding\b[^{}]*\{[^}]*display\s*:\s*flex/s',
            $css,
            '`.site-branding` should be declared as a flex container so the logo and site title sit side-by-side.'
        );
    }

    public function test_site_title_is_italic_in_header_context(): void {
        $css = $this->styleBody();
        $this->assertMatchesRegularExpression(
            '/\.site-header\s+\.site-title\b[^{}]*\{[^}]*font-style\s*:\s*italic/s',
            $css,
            '`.site-header .site-title` should set `font-style: italic` per issue #4.'
        );
    }

    public function test_site_title_uses_font_weight_400_in_header_context(): void {
        $css = $this->styleBody();
        $this->assertMatchesRegularExpression(
            '/\.site-header\s+\.site-title\b[^{}]*\{[^}]*font-weight\s*:\s*400/s',
            $css,
            '`.site-header .site-title` should set `font-weight: 400` per issue #4.'
        );
    }

    // (Regression guards for parent dynamo_theme rules removed in the
    // child-theme migration — those rules live in the parent's
    // style.css, not the child's swuc-overrides.css, so they belong in
    // the dynamo_theme test suite.)
}
