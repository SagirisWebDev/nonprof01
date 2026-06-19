<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for Issue #2: Port design tokens into dynamo's Customizer.
 *
 * After the token-defaults refactor, SWUC tokens are split into two layers:
 *
 *   1. dynamo_token_defaults filter — overrides colours-text, colours-link,
 *      typography font-families and sizes, spacing-content-padding-x.
 *      These surface in Dynamo's existing Customizer panels.
 *
 *   2. dynamo_config_customizer() bindings — only for values that have no
 *      Dynamo built-in equivalent: heading/post-title/sub-heading/alert colours,
 *      swuc_font_size_small, and swuc_spacing_{small,medium,large}.
 */
class SwucDesignTokensTest extends TestCase {

    private static Dynamo_Binding_Registry $registry;

    public static function setUpBeforeClass(): void {
        $GLOBALS['wp_filter']     = [];
        $GLOBALS['wp_theme_mods'] = [];
        Dynamo_Binding_Registry::reset_instance();
        require_once SWUC_PATH . 'dynamo-extend-customizer.php';
        self::$registry = Dynamo_Binding_Registry::instance();
    }

    protected function setUp(): void {
        $GLOBALS['wp_theme_mods'] = [];
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function binding(string $id): array {
        $all = self::$registry->all();
        $this->assertArrayHasKey(
            $id,
            $all,
            "Expected binding '{$id}' to be registered."
        );
        return $all[$id];
    }

    private function bindingCss(): string {
        return (new Dynamo_Binding_CSS_Renderer(self::$registry))->render();
    }

    private function tokenRegistry(): Dynamo_Token_Registry {
        return new Dynamo_Token_Registry();
    }

    private function tokenCss(): string {
        $manifest = new Dynamo_Font_Manifest(DYNAMO_PATH . 'fonts/fonts.json');
        return (new Dynamo_CSS_Generator($this->tokenRegistry(), $manifest))->generate();
    }

    // -----------------------------------------------------------------------
    // Loading: file must not throw
    // -----------------------------------------------------------------------

    public function test_extending_customizer_file_loads_without_exceptions(): void {
        $this->assertInstanceOf(Dynamo_Binding_Registry::class, self::$registry);
    }

    // -----------------------------------------------------------------------
    // Layer 1 — dynamo_token_defaults overrides
    // -----------------------------------------------------------------------

    // --- Colours ---

    public function test_token_defaults_override_colors_text(): void {
        $this->assertSame('#000000', $this->tokenRegistry()->get('colors-text'));
    }

    public function test_token_defaults_override_colors_link(): void {
        $this->assertSame('#3999ef', $this->tokenRegistry()->get('colors-link'));
    }

    public function test_token_defaults_leave_colors_background_unchanged(): void {
        $this->assertSame('#ffffff', $this->tokenRegistry()->get('colors-background'));
    }

    // --- Font families ---

    /** @dataProvider headingElementProvider */
    public function test_token_defaults_set_heading_font_family_to_source_serif_pro(string $el): void {
        $this->assertSame(
            'source-serif-pro',
            $this->tokenRegistry()->get("typography-{$el}-font-family"),
            "typography-{$el}-font-family must default to 'source-serif-pro'."
        );
    }

    public function test_token_defaults_leave_body_font_family_as_system_sans(): void {
        $this->assertSame('system-sans', $this->tokenRegistry()->get('typography-body-font-family'));
    }

    public static function headingElementProvider(): array {
        return [
            'h1' => ['h1'], 'h2' => ['h2'], 'h3' => ['h3'],
            'h4' => ['h4'], 'h5' => ['h5'], 'h6' => ['h6'],
        ];
    }

    // --- Font sizes ---

    public function test_token_defaults_override_body_font_size(): void {
        $this->assertSame('1.125rem', $this->tokenRegistry()->get('typography-body-font-size'));
    }

    /** @dataProvider headingFontSizeProvider */
    public function test_token_defaults_set_heading_font_size(string $token, string $expected): void {
        $this->assertSame($expected, $this->tokenRegistry()->get($token));
    }

    public static function headingFontSizeProvider(): array {
        return [
            'h1' => ['typography-h1-font-size', 'clamp(3.25rem, 8vw, 6.25rem)'],
            'h2' => ['typography-h2-font-size', 'clamp(2.75rem, 6vw, 3.25rem)'],
            'h3' => ['typography-h3-font-size', 'clamp(1.5rem, 4vw, 2.75rem)'],
            'h4' => ['typography-h4-font-size', 'clamp(1.75rem, 3vw, 2.25rem)'],
            'h5' => ['typography-h5-font-size', '1.75rem'],
            'h6' => ['typography-h6-font-size', '1.75rem'],
        ];
    }

    // --- Spacing ---

    public function test_token_defaults_override_spacing_content_padding_x(): void {
        $this->assertSame('1.25rem', $this->tokenRegistry()->get('spacing-content-padding-x'));
    }

    // -----------------------------------------------------------------------
    // Layer 1 — CSS Generator emits correct token variable values
    // -----------------------------------------------------------------------

    public function test_token_css_emits_colors_text_override(): void {
        $this->assertStringContainsString('--dynamo-colors-text: #000000;', $this->tokenCss());
    }

    public function test_token_css_emits_colors_link_override(): void {
        $this->assertStringContainsString('--dynamo-colors-link: #3999ef;', $this->tokenCss());
    }

    public function test_token_css_resolves_h1_font_family_to_source_serif_pro_stack(): void {
        $this->assertStringContainsString(
            '--dynamo-typography-h1-font-family: "Source Serif Pro", Georgia, serif;',
            $this->tokenCss()
        );
    }

    public function test_token_css_emits_body_font_size_override(): void {
        $this->assertStringContainsString(
            '--dynamo-typography-body-font-size: 1.125rem;',
            $this->tokenCss()
        );
    }

    public function test_token_css_emits_h1_font_size_override(): void {
        $this->assertStringContainsString(
            '--dynamo-typography-h1-font-size: clamp(3.25rem, 8vw, 6.25rem);',
            $this->tokenCss()
        );
    }

    public function test_token_css_emits_spacing_content_padding_x_override(): void {
        $this->assertStringContainsString(
            '--dynamo-spacing-content-padding-x: 1.25rem;',
            $this->tokenCss()
        );
    }

    // -----------------------------------------------------------------------
    // Layer 2 — dynamo_config_customizer() bindings
    // -----------------------------------------------------------------------

    // --- 4 colour bindings (no Dynamo equivalent) ---

    /** @dataProvider colorBindingIdProvider */
    public function test_colour_binding_is_registered(string $id): void {
        $this->binding($id);
        $this->addToAssertionCount(1);
    }

    /** @dataProvider colorBindingIdProvider */
    public function test_colour_binding_has_type_color(string $id): void {
        $this->assertSame('color', $this->binding($id)['type']);
    }

    /** @dataProvider colorBindingIdProvider */
    public function test_colour_binding_is_in_swuc_colors_section(string $id): void {
        $this->assertSame('swuc_colors', $this->binding($id)['section']);
    }

    public static function colorBindingIdProvider(): array {
        return [
            'swuc_color_heading'     => ['swuc_color_heading'],
            'swuc_color_post_title'  => ['swuc_color_post_title'],
            'swuc_color_sub_heading' => ['swuc_color_sub_heading'],
            'swuc_color_alert'       => ['swuc_color_alert'],
        ];
    }

    public function test_swuc_color_heading_targets_heading_elements(): void {
        $b = $this->binding('swuc_color_heading');
        $this->assertSame('h1, h2, h3, h4, h5, h6', $b['selector']);
        $this->assertSame('color', $b['property']);
        $this->assertSame('#ffffff', $b['default']);
    }

    public function test_swuc_color_post_title_targets_entry_and_page_title(): void {
        $b = $this->binding('swuc_color_post_title');
        $this->assertSame('.entry-title, .page-title', $b['selector']);
        $this->assertSame('color', $b['property']);
        $this->assertSame('#55606b', $b['default']);
    }

    public function test_swuc_color_sub_heading_targets_description_and_meta(): void {
        $b = $this->binding('swuc_color_sub_heading');
        $this->assertSame('.site-description, .post-meta', $b['selector']);
        $this->assertSame('color', $b['property']);
        $this->assertSame('#b3b3b3', $b['default']);
    }

    public function test_swuc_color_alert_targets_error_and_alert_classes(): void {
        $b = $this->binding('swuc_color_alert');
        $this->assertSame('.error, .alert', $b['selector']);
        $this->assertSame('color', $b['property']);
        $this->assertSame('#cf2e2e', $b['default']);
    }

    // --- CSS for colour bindings ---

    public function test_binding_css_emits_swuc_color_heading_custom_property(): void {
        $this->assertStringContainsString('--dynamo-swuc_color_heading: #ffffff;', $this->bindingCss());
    }

    public function test_binding_css_emits_rule_for_heading_color(): void {
        $this->assertStringContainsString(
            'h1, h2, h3, h4, h5, h6 { color: var(--dynamo-swuc_color_heading); }',
            $this->bindingCss()
        );
    }

    public function test_binding_css_emits_swuc_color_post_title_custom_property(): void {
        $this->assertStringContainsString('--dynamo-swuc_color_post_title: #55606b;', $this->bindingCss());
    }

    public function test_binding_css_emits_swuc_color_sub_heading_custom_property(): void {
        $this->assertStringContainsString('--dynamo-swuc_color_sub_heading: #b3b3b3;', $this->bindingCss());
    }

    public function test_binding_css_emits_swuc_color_alert_custom_property(): void {
        $this->assertStringContainsString('--dynamo-swuc_color_alert: #cf2e2e;', $this->bindingCss());
    }

    // --- swuc_font_size_small binding ---

    public function test_swuc_font_size_small_is_registered(): void {
        $b = $this->binding('swuc_font_size_small');
        $this->assertSame('text', $b['type']);
        $this->assertSame('swuc_type_scale', $b['section']);
        $this->assertSame('small, .text-small', $b['selector']);
        $this->assertSame('font-size', $b['property']);
        $this->assertSame('1rem', $b['default']);
    }

    public function test_binding_css_emits_swuc_font_size_small_custom_property(): void {
        $this->assertStringContainsString('--dynamo-swuc_font_size_small: 1rem;', $this->bindingCss());
    }

    public function test_binding_css_emits_rule_for_small_font_size(): void {
        $this->assertStringContainsString(
            'small, .text-small { font-size: var(--dynamo-swuc_font_size_small); }',
            $this->bindingCss()
        );
    }

    // --- 3 spacing bindings ---

    /** @dataProvider spacingBindingProvider */
    public function test_spacing_binding_is_registered(
        string $id,
        string $expected_selector,
        string $expected_property,
        string $expected_default
    ): void {
        $b = $this->binding($id);
        $this->assertSame('text', $b['type']);
        $this->assertSame('swuc_spacing', $b['section']);
        $this->assertSame($expected_selector, $b['selector']);
        $this->assertSame($expected_property, $b['property']);
        $this->assertSame($expected_default, $b['default']);
    }

    public static function spacingBindingProvider(): array {
        return [
            'swuc_spacing_small'  => ['swuc_spacing_small',  '.site-header',   'padding-block', '0'],
            'swuc_spacing_medium' => ['swuc_spacing_medium', '.entry-content',  'padding-block', 'clamp(2rem, 8vw, 6rem)'],
            'swuc_spacing_large'  => ['swuc_spacing_large',  '.site-section',   'padding-block', 'clamp(4rem, 10vw, 8rem)'],
        ];
    }

    public function test_swuc_spacing_outer_binding_is_not_registered(): void {
        $all = self::$registry->all();
        $this->assertArrayNotHasKey(
            'swuc_spacing_outer',
            $all,
            'swuc_spacing_outer was replaced by the spacing-content-padding-x token default and must not be a binding.'
        );
    }

    public function test_binding_css_emits_swuc_spacing_small_custom_property(): void {
        // Default lowered to 0 so the .site-header collapses to the
        // 56px logo height (production parity); the customizer still
        // exposes the token for sites that want to add padding back.
        $this->assertStringContainsString(
            '--dynamo-swuc_spacing_small: 0;',
            $this->bindingCss()
        );
    }

    public function test_binding_css_emits_rule_for_site_header_padding(): void {
        $this->assertStringContainsString(
            '.site-header { padding-block: var(--dynamo-swuc_spacing_small); }',
            $this->bindingCss()
        );
    }

    // -----------------------------------------------------------------------
    // Vocabulary filter
    // -----------------------------------------------------------------------

    public function test_vocabulary_filter_adds_any_to_font_size_property_categories(): void {
        $this->assertContains('any', Dynamo_CSS_Vocabulary::property_categories('font-size'));
    }

    public function test_vocabulary_filter_adds_any_to_padding_block_property_categories(): void {
        $this->assertContains('any', Dynamo_CSS_Vocabulary::property_categories('padding-block'));
    }

    // -----------------------------------------------------------------------
    // Saved theme-mod overrides
    // -----------------------------------------------------------------------

    public function test_saved_theme_mod_overrides_colors_text_in_token_registry(): void {
        set_theme_mod('dynamo_colors_text', '#111111');
        $this->assertSame('#111111', $this->tokenRegistry()->get('colors-text'));
    }

    public function test_saved_theme_mod_overrides_heading_color_binding_in_css(): void {
        set_theme_mod('dynamo_swuc_color_heading', '#333333');
        $this->assertStringContainsString('--dynamo-swuc_color_heading: #333333;', $this->bindingCss());
    }

    public function test_saved_theme_mod_overrides_typography_h1_font_size_in_token_registry(): void {
        set_theme_mod('dynamo_typography_h1_font_size', 'clamp(4rem, 10vw, 8rem)');
        $this->assertSame('clamp(4rem, 10vw, 8rem)', $this->tokenRegistry()->get('typography-h1-font-size'));
    }
}
