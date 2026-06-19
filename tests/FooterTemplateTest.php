<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Asserts the dynamo theme `footer.php` template and supporting CSS in
 * `assets/css/swuc-overrides.css` meet the rebuild acceptance criteria from issue #5:
 *
 *   1. Footer displays the site title (in bold) on the left.
 *   2. YouTube icon link renders in red (#e12c2c) and points at the church's
 *      YouTube channel.
 *   3. Facebook icon link renders in blue (#006097) and points at the
 *      church's Facebook page.
 *   4. Footer background is #fffffb.
 *   5. Layout is a flex row matching the live site footer appearance.
 *   6. Both social links open in a new tab with
 *      `rel="noreferrer noopener"` (either ordering of the rel tokens is OK).
 *
 * Uses source-level assertions (read the file as a string and regex/substring
 * match), following the pattern in tests/HeaderTemplateTest.php and
 * tests/ExtendCustomizerTemplateTest.php, because footer.php depends on WP
 * runtime functions that are not stubbed in tests/bootstrap.php.
 */
class FooterTemplateTest extends TestCase {

    private function footerPath(): string {
        return SWUC_PATH . 'footer.php';
    }

    private function footerBody(): string {
        return (string) file_get_contents($this->footerPath());
    }

    private function stylePath(): string {
        return SWUC_PATH . 'assets/css/swuc-overrides.css';
    }

    private function styleBody(): string {
        return (string) file_get_contents($this->stylePath());
    }

    /**
     * Extracts the `<a ...>...</a>` blocks whose `href` attribute matches the
     * given URL. Returns the full tag (open tag + inner HTML + close tag) for
     * each match so callers can assert on attributes and children together.
     *
     * @return string[]
     */
    private function anchorsWithHref(string $body, string $href): array {
        $escaped = preg_quote($href, '/');
        $pattern = '/<a\b[^>]*href\s*=\s*"' . $escaped . '"[^>]*>.*?<\/a>/is';
        preg_match_all($pattern, $body, $matches);
        return $matches[0];
    }

    // ── footer.php ────────────────────────────────────────────────────────

    public function test_footer_file_exists(): void {
        $this->assertFileExists($this->footerPath());
    }

    public function test_footer_contains_footer_title_class(): void {
        $body = $this->footerBody();
        $this->assertMatchesRegularExpression(
            '/class\s*=\s*"[^"]*\bfooter-title\b[^"]*"/',
            $body,
            'footer.php must include an element with class `footer-title` to act as the bold left-side site-title label.'
        );
    }

    public function test_footer_links_to_youtube_channel(): void {
        $body = $this->footerBody();
        $this->assertStringContainsString(
            'href="https://youtube.com/channel/UCrJQ5ljHCSPBV3F8TmIhvVA"',
            $body,
            'footer.php must contain an anchor whose href is the church YouTube channel URL.'
        );
    }

    public function test_footer_links_to_facebook_page(): void {
        // Preserve the upstream URL typo ("Spirt") — that is the actual page.
        $body = $this->footerBody();
        $this->assertStringContainsString(
            'href="https://www.facebook.com/SpirtWestUnitedChurch/"',
            $body,
            'footer.php must contain an anchor whose href is the church Facebook page URL (preserving the existing typo).'
        );
    }

    public function test_youtube_link_has_footer_youtube_class(): void {
        $body = $this->footerBody();
        $anchors = $this->anchorsWithHref($body, 'https://youtube.com/channel/UCrJQ5ljHCSPBV3F8TmIhvVA');
        $this->assertNotEmpty(
            $anchors,
            'Expected a YouTube anchor in footer.php; none found.'
        );
        $matchesClass = false;
        foreach ($anchors as $anchor) {
            if (preg_match('/class\s*=\s*"[^"]*\bfooter-youtube\b[^"]*"/', $anchor) === 1) {
                $matchesClass = true;
                break;
            }
        }
        $this->assertTrue(
            $matchesClass,
            'The YouTube anchor in footer.php must carry the `footer-youtube` class.'
        );
    }

    public function test_facebook_link_has_footer_facebook_class(): void {
        $body = $this->footerBody();
        $anchors = $this->anchorsWithHref($body, 'https://www.facebook.com/SpirtWestUnitedChurch/');
        $this->assertNotEmpty(
            $anchors,
            'Expected a Facebook anchor in footer.php; none found.'
        );
        $matchesClass = false;
        foreach ($anchors as $anchor) {
            if (preg_match('/class\s*=\s*"[^"]*\bfooter-facebook\b[^"]*"/', $anchor) === 1) {
                $matchesClass = true;
                break;
            }
        }
        $this->assertTrue(
            $matchesClass,
            'The Facebook anchor in footer.php must carry the `footer-facebook` class.'
        );
    }

    public function test_youtube_link_opens_in_new_tab_with_safe_rel(): void {
        $body = $this->footerBody();
        $anchors = $this->anchorsWithHref($body, 'https://youtube.com/channel/UCrJQ5ljHCSPBV3F8TmIhvVA');
        $this->assertNotEmpty(
            $anchors,
            'Expected a YouTube anchor in footer.php; none found.'
        );
        $satisfies = false;
        foreach ($anchors as $anchor) {
            $hasTargetBlank = (bool) preg_match('/target\s*=\s*"_blank"/', $anchor);
            $hasSafeRel     = (bool) preg_match('/rel\s*=\s*"(?:noreferrer noopener|noopener noreferrer)"/', $anchor);
            if ($hasTargetBlank && $hasSafeRel) {
                $satisfies = true;
                break;
            }
        }
        $this->assertTrue(
            $satisfies,
            'The YouTube anchor must open in a new tab (`target="_blank"`) with `rel="noreferrer noopener"` (either ordering allowed).'
        );
    }

    public function test_facebook_link_opens_in_new_tab_with_safe_rel(): void {
        $body = $this->footerBody();
        $anchors = $this->anchorsWithHref($body, 'https://www.facebook.com/SpirtWestUnitedChurch/');
        $this->assertNotEmpty(
            $anchors,
            'Expected a Facebook anchor in footer.php; none found.'
        );
        $satisfies = false;
        foreach ($anchors as $anchor) {
            $hasTargetBlank = (bool) preg_match('/target\s*=\s*"_blank"/', $anchor);
            $hasSafeRel     = (bool) preg_match('/rel\s*=\s*"(?:noreferrer noopener|noopener noreferrer)"/', $anchor);
            if ($hasTargetBlank && $hasSafeRel) {
                $satisfies = true;
                break;
            }
        }
        $this->assertTrue(
            $satisfies,
            'The Facebook anchor must open in a new tab (`target="_blank"`) with `rel="noreferrer noopener"` (either ordering allowed).'
        );
    }

    public function test_youtube_link_contains_svg_element(): void {
        $body = $this->footerBody();
        $anchors = $this->anchorsWithHref($body, 'https://youtube.com/channel/UCrJQ5ljHCSPBV3F8TmIhvVA');
        $this->assertNotEmpty(
            $anchors,
            'Expected a YouTube anchor in footer.php; none found.'
        );
        $hasSvg = false;
        foreach ($anchors as $anchor) {
            if (preg_match('/<svg\b/i', $anchor) === 1) {
                $hasSvg = true;
                break;
            }
        }
        $this->assertTrue(
            $hasSvg,
            'The YouTube anchor in footer.php must contain an `<svg>` icon element.'
        );
    }

    public function test_facebook_link_contains_svg_element(): void {
        $body = $this->footerBody();
        $anchors = $this->anchorsWithHref($body, 'https://www.facebook.com/SpirtWestUnitedChurch/');
        $this->assertNotEmpty(
            $anchors,
            'Expected a Facebook anchor in footer.php; none found.'
        );
        $hasSvg = false;
        foreach ($anchors as $anchor) {
            if (preg_match('/<svg\b/i', $anchor) === 1) {
                $hasSvg = true;
                break;
            }
        }
        $this->assertTrue(
            $hasSvg,
            'The Facebook anchor in footer.php must contain an `<svg>` icon element.'
        );
    }

    public function test_footer_still_emits_bloginfo_name(): void {
        $body = $this->footerBody();
        $this->assertMatchesRegularExpression(
            "/bloginfo\\(\\s*'name'\\s*\\)/",
            $body,
            "footer.php must still render the site title via bloginfo( 'name' ) for the footer-title text."
        );
    }

    public function test_footer_still_calls_wp_footer(): void {
        $body = $this->footerBody();
        $this->assertMatchesRegularExpression(
            '/wp_footer\s*\(\s*\)/',
            $body,
            'footer.php must still call wp_footer() so WP hooks run.'
        );
    }

    public function test_footer_still_closes_body_and_html(): void {
        $body = $this->footerBody();
        $this->assertStringContainsString(
            '</body>',
            $body,
            'footer.php must still close the </body> tag (regression).'
        );
        $this->assertStringContainsString(
            '</html>',
            $body,
            'footer.php must still close the </html> tag (regression).'
        );
    }

    public function test_footer_php_has_no_syntax_errors(): void {
        $path  = $this->footerPath();
        $cmd   = 'php -l ' . escapeshellarg($path) . ' 2>&1';
        $output = (string) shell_exec($cmd);
        $this->assertStringContainsString(
            'No syntax errors detected',
            $output,
            "php -l reported syntax errors in footer.php:\n" . $output
        );
    }

    // ── assets/css/swuc-overrides.css ──────────────────────────────────────────────

    public function test_style_file_exists(): void {
        $this->assertFileExists($this->stylePath());
    }

    public function test_site_footer_background_is_fffffb(): void {
        $css = $this->styleBody();
        $this->assertMatchesRegularExpression(
            '/\.site-footer\b[^{}]*\{[^}]*background-color\s*:\s*#fffffb/is',
            $css,
            '`.site-footer` must declare `background-color: #fffffb` per issue #5.'
        );
    }

    public function test_footer_youtube_color_is_red(): void {
        $css = $this->styleBody();
        $this->assertMatchesRegularExpression(
            '/\.footer-youtube\b[^{}]*\{[^}]*color\s*:\s*#e12c2c/is',
            $css,
            '`.footer-youtube` must declare `color: #e12c2c` so the YouTube glyph renders red.'
        );
    }

    public function test_footer_facebook_color_is_blue(): void {
        $css = $this->styleBody();
        $this->assertMatchesRegularExpression(
            '/\.footer-facebook\b[^{}]*\{[^}]*color\s*:\s*#006097/is',
            $css,
            '`.footer-facebook` must declare `color: #006097` so the Facebook glyph renders blue.'
        );
    }

    public function test_footer_title_is_bold(): void {
        $css = $this->styleBody();
        $this->assertMatchesRegularExpression(
            '/\.footer-title\b[^{}]*\{[^}]*font-weight\s*:\s*(?:bold|700)\b/is',
            $css,
            '`.footer-title` must declare `font-weight: bold` (or `700`) so the site name renders bold.'
        );
    }
}
