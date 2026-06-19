<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for Issue #3: Port site-specific CSS classes from spiritwestuc into
 * dynamo's compiled stylesheet (assets/css/swuc-overrides.css).
 *
 * Dynamo has no CSS build step — the file at SWUC_PATH . 'assets/css/swuc-overrides.css'
 * is both source and output. These tests assert that the rules previously
 * defined in spiritwestuc/style.css have been ported across.
 *
 * Notes:
 *   - `.footer-youtube` is NOT defined in spiritwestuc/style.css. The port
 *     adds it as a new placeholder so it can be styled alongside its sibling
 *     `.footer-facebook`. The test documents this by asserting the selector
 *     is at least present.
 *   - dynamo already defines `.page-title` (as part of an `.archive-title,
 *     .page-title` group) and has no `.post-title` rule. The site-specific
 *     spiritwestuc behaviour is `.page-title { display: none; }` and
 *     `.post-title { padding-inline-start: 0.4em; }` — both must appear in
 *     the ported output without removing dynamo's existing definitions.
 */
class SwucCssPortTest extends TestCase {

    private static string $css;

    public static function setUpBeforeClass(): void {
        $path = SWUC_PATH . 'assets/css/swuc-overrides.css';
        self::assertFileExists($path, "Expected dynamo stylesheet at {$path}");
        self::$css = (string) file_get_contents($path);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Return the body of the first rule whose selector list contains $selector,
     * or null if no matching rule is found.
     *
     * The matcher is intentionally permissive: it accepts the selector when it
     * appears either alone or as part of a comma-separated selector list
     * (e.g. ".covid, .live-stream { ... }").
     */
    private function ruleBody(string $selector): ?string {
        $pattern = '/(^|[\s,}])' . preg_quote($selector, '/') . '\s*(?:,[^{]*)?\{([^}]*)\}/m';
        if (preg_match($pattern, self::$css, $matches)) {
            return $matches[2];
        }

        // Fallback: a selector list where the wanted selector is not first.
        $pattern2 = '/[^{}]*,\s*' . preg_quote($selector, '/') . '\s*(?:,[^{]*)?\{([^}]*)\}/m';
        if (preg_match($pattern2, self::$css, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function assertSelectorPresent(string $selector): void {
        $this->assertMatchesRegularExpression(
            '/(^|[\s,}])' . preg_quote($selector, '/') . '\s*[,{]/m',
            self::$css,
            "Expected selector `{$selector}` to be present in assets/css/swuc-overrides.css."
        );
    }

    // -----------------------------------------------------------------------
    // .zoom / .zoom-left / .zoom-right
    // -----------------------------------------------------------------------

    public function test_zoom_selector_is_present(): void {
        $this->assertSelectorPresent('.zoom');
    }

    public function test_zoom_left_selector_is_present(): void {
        $this->assertSelectorPresent('.zoom-left');
    }

    public function test_zoom_right_selector_is_present(): void {
        $this->assertSelectorPresent('.zoom-right');
    }

    public function test_zoom_hover_has_transform_scale(): void {
        $body = $this->ruleBody('.zoom:hover');
        $this->assertNotNull($body, '.zoom:hover rule must be present.');
        $this->assertMatchesRegularExpression(
            '/transform\s*:\s*scale\([^)]+\)/',
            $body,
            '.zoom:hover must apply a `transform: scale(...)` rule.'
        );
    }

    // -----------------------------------------------------------------------
    // .live-stream
    // -----------------------------------------------------------------------

    public function test_live_stream_selector_is_present(): void {
        $this->assertSelectorPresent('.live-stream');
    }

    public function test_live_stream_is_inline_block(): void {
        $body = $this->ruleBody('.live-stream');
        $this->assertNotNull($body, '.live-stream rule must be present.');
        $this->assertMatchesRegularExpression(
            '/display\s*:\s*inline-block/',
            $body,
            '.live-stream must set `display: inline-block`.'
        );
    }

    public function test_live_stream_has_no_hover_transform(): void {
        // The notices bar removed the scale hover effect — `.covid` and
        // `.live-stream` are now plain badges (no `transform: scale` on
        // hover, no `.notices` background). Guard against the rule being
        // reintroduced by accident.
        $body = $this->ruleBody('.live-stream:hover');
        $this->assertNull(
            $body,
            '.live-stream:hover should not have its own rule — the zoom '
            . 'effect was deliberately removed from the notices bar.'
        );
        $covidBody = $this->ruleBody('.covid:hover');
        $this->assertNull(
            $covidBody,
            '.covid:hover should not have its own rule — the zoom effect '
            . 'was deliberately removed from the notices bar.'
        );
    }

    // -----------------------------------------------------------------------
    // Submenu styles
    // -----------------------------------------------------------------------

    public function test_home_submenu_selector_is_present(): void {
        $this->assertSelectorPresent('.home-submenu');
    }

    public function test_activities_submenu_selector_is_present(): void {
        $this->assertSelectorPresent('.activities-submenu');
    }

    public function test_submenu_item_selector_is_present(): void {
        $this->assertSelectorPresent('.submenu-item');
    }

    // -----------------------------------------------------------------------
    // Text effect utilities
    // -----------------------------------------------------------------------

    public function test_text_shadow_has_text_shadow_property(): void {
        $body = $this->ruleBody('.text-shadow');
        $this->assertNotNull($body, '.text-shadow rule must be present.');
        $this->assertMatchesRegularExpression(
            '/text-shadow\s*:\s*[^;]+/',
            $body,
            '.text-shadow must declare a `text-shadow:` property.'
        );
    }

    public function test_subheading_text_shadow_has_text_shadow_property(): void {
        $body = $this->ruleBody('.subheading-text-shadow');
        $this->assertNotNull($body, '.subheading-text-shadow rule must be present.');
        $this->assertMatchesRegularExpression(
            '/text-shadow\s*:\s*[^;]+/',
            $body,
            '.subheading-text-shadow must declare a `text-shadow:` property.'
        );
    }

    // -----------------------------------------------------------------------
    // Desktop photo / spacer (default state = display:none on mobile)
    // -----------------------------------------------------------------------

    public function test_desktop_photo_is_hidden_by_default(): void {
        $body = $this->ruleBody('.desktop-photo');
        $this->assertNotNull($body, '.desktop-photo rule must be present.');
        $this->assertMatchesRegularExpression(
            '/display\s*:\s*none/',
            $body,
            '.desktop-photo must default to `display: none`.'
        );
    }

    public function test_desktop_photo_spacer_is_hidden_by_default(): void {
        $body = $this->ruleBody('.desktop-photo-spacer');
        $this->assertNotNull($body, '.desktop-photo-spacer rule must be present.');
        $this->assertMatchesRegularExpression(
            '/display\s*:\s*none/',
            $body,
            '.desktop-photo-spacer must default to `display: none`.'
        );
    }

    // -----------------------------------------------------------------------
    // Page-specific headings
    // -----------------------------------------------------------------------

    public function test_sermons_heading_selector_is_present(): void {
        $this->assertSelectorPresent('.sermons-heading');
    }

    public function test_worship_subheading_selector_is_present(): void {
        $this->assertSelectorPresent('.worship-subheading');
    }

    /**
     * The desktop `.worship-subheading` rule originally set
     * `font-size: var(--dynamo-typography-h2-font-size, ...)` because the
     * sub-heading was rendered as three side-by-side flex children
     * ("Sunday Mornings" + "at" + "10:30"). After Issue #16 the markup is a
     * single H3 carrying the full string — at h2 size it wraps to two lines
     * inside the constrained container. Drop the override to the h3 token.
     */
    public function test_desktop_worship_subheading_uses_h3_font_size_token(): void {
        $this->assertMatchesRegularExpression(
            '/@media\s*\(\s*min-width\s*:\s*1024px\s*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*?\.worship-subheading\s*\{[^}]*font-size\s*:\s*var\(\s*--dynamo-typography-h3-font-size[^}]*\}/s',
            (string) file_get_contents(SWUC_PATH . 'assets/css/swuc-overrides.css'),
            'At desktop, `.worship-subheading { font-size: var(--dynamo-typography-h3-font-size, ...) }` so the single-H3 sub-heading stays on one line.'
        );
    }

    // -----------------------------------------------------------------------
    // Prayers form
    // -----------------------------------------------------------------------

    public function test_prayers_input_selector_is_present(): void {
        $this->assertSelectorPresent('.prayers-input');
    }

    public function test_prayers_label_selector_is_present(): void {
        $this->assertSelectorPresent('.prayers-label');
    }

    // -----------------------------------------------------------------------
    // Footer element styles
    // -----------------------------------------------------------------------

    public function test_footer_title_selector_is_present(): void {
        $this->assertSelectorPresent('.footer-title');
    }

    public function test_footer_facebook_selector_is_present(): void {
        $this->assertSelectorPresent('.footer-facebook');
    }

    /**
     * `.footer-youtube` is intentionally NOT in spiritwestuc/style.css. The
     * port should introduce at least a placeholder rule so the footer
     * markup has somewhere to hang styles.
     */
    public function test_footer_youtube_selector_is_present_as_placeholder(): void {
        $this->assertSelectorPresent('.footer-youtube');
    }

    // -----------------------------------------------------------------------
    // Notices and page-id utilities
    // -----------------------------------------------------------------------

    public function test_notices_is_display_flex(): void {
        $body = $this->ruleBody('.notices');
        $this->assertNotNull($body, '.notices rule must be present.');
        $this->assertMatchesRegularExpression(
            '/display\s*:\s*flex/',
            $body,
            '.notices must set `display: flex`.'
        );
    }

    public function test_covid_selector_is_present(): void {
        $this->assertSelectorPresent('.covid');
    }

    public function test_page_id_316_selector_is_present(): void {
        $this->assertSelectorPresent('.page-id-316');
    }

    public function test_page_id_370_selector_is_present(): void {
        $this->assertSelectorPresent('.page-id-370');
    }

    // -----------------------------------------------------------------------
    // Post layout styles
    // -----------------------------------------------------------------------

    public function test_post_feature_image_selector_is_present(): void {
        $this->assertSelectorPresent('.post-feature-image');
    }

    public function test_post_title_selector_is_present(): void {
        $this->assertSelectorPresent('.post-title');
    }

    /**
     * spiritwestuc's `.post-title` site-specific property is
     * `padding-inline-start: 0.4em`. Dynamo has no existing `.post-title`
     * rule, so this property must appear after the port.
     */
    public function test_post_title_has_padding_inline_start_from_spiritwestuc(): void {
        $body = $this->ruleBody('.post-title');
        $this->assertNotNull($body, '.post-title rule must be present.');
        $this->assertMatchesRegularExpression(
            '/padding-inline-start\s*:\s*0\.4em/',
            $body,
            '.post-title must carry spiritwestuc\'s `padding-inline-start: 0.4em`.'
        );
    }

    /**
     * spiritwestuc's site-specific `.page-title` rule hides the title with
     * `display: none`. Dynamo already has an `.archive-title, .page-title`
     * rule with `color` + `margin: 0` — that must be preserved, and the
     * spiritwestuc `display: none` behaviour must also be present (most
     * likely as a separate `.page-title { display: none }` rule).
     *
     * NOTE: This test also serves as the regression guard for Issue #9 —
     * the existing `.page-title { display: none }` behaviour must remain
     * after the additional `body.page .entry-title` rule is added.
     */
    public function test_page_title_has_display_none_from_spiritwestuc(): void {
        // Accept both forms: standalone `.page-title { display: none }` OR a
        // selector-list rule where `.page-title` is followed by `,` and other
        // selectors before `{` (the Issue #9 form joins it with
        // `body.page .entry-title`).
        $this->assertMatchesRegularExpression(
            '/\.page-title\s*[,{][^{}]*\{[^}]*display\s*:\s*none[^}]*\}/',
            self::$css,
            '.page-title must include spiritwestuc\'s `display: none` behaviour.'
        );
    }

    // -----------------------------------------------------------------------
    // Issue #9: Suppress page H1 globally — dynamo's page.php emits the
    // page heading as `<h1 class="entry-title">`, not `.page-title`, so the
    // existing `.page-title { display: none }` rule never matches on pages.
    // Extend the SWUC section so `body.page .entry-title { display: none }`
    // joins the existing rule. The rule must be scoped to `body.page` so
    // that single posts (single.php also emits `.entry-title`) are NOT
    // affected.
    // -----------------------------------------------------------------------

    /**
     * Issue #9: A rule must exist that hides `.entry-title` on pages via the
     * `body.page` scope. Accept any of these forms:
     *   - `body.page .entry-title { display: none; }`
     *   - `.page-title, body.page .entry-title { display: none; }`
     *   - `.page-title,\n body.page .entry-title { ... display: none ... }`
     *
     * The regex requires `body.page .entry-title` to appear as a selector-list
     * member (either alone, first, last, or middle) of a rule whose body
     * contains `display: none`.
     */
    public function test_body_page_entry_title_is_hidden(): void {
        // Match a rule whose selector list contains `body.page .entry-title`
        // (with any whitespace between `body.page` and `.entry-title`) and
        // whose body declares `display: none`.
        //
        // Selector-list membership: the selector is either at the start of the
        // rule's selector list (preceded by start-of-string, `}`, `*/`, or a
        // newline), or it is preceded by a `,` (comma-joined). It is followed
        // by either `{` (terminating the selector list) or `,` (more selectors
        // follow).
        $pattern = '/(?:^|[}\n])\s*(?:[^{}]*,\s*)?body\.page\s+\.entry-title\s*(?:,[^{]*)?\{[^}]*display\s*:\s*none[^}]*\}/m';
        $this->assertMatchesRegularExpression(
            $pattern,
            self::$css,
            'Expected a `body.page .entry-title { display: none; }` rule (alone '
            . 'or as part of a selector list with `.page-title`) so dynamo\'s '
            . '`<h1 class="entry-title">` on pages is hidden. Issue #9.'
        );
    }

    // (Parent dynamo_theme `.archive-title, .page-title` group regression
    // guard removed — that base rule lives in the parent stylesheet.)
}
