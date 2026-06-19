<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Asserts the developer-owned template file `dynamo-extend-customizer.php`
 * ships per issue #18 acceptance criteria.
 */
class ExtendCustomizerTemplateTest extends TestCase {

    private const V1_TYPES = [
        'color', 'text', 'textarea', 'number', 'range',
        'select', 'radio', 'url', 'image', 'media', 'date', 'code',
    ];

    private function templatePath(): string {
        return SWUC_PATH . 'dynamo-extend-customizer.php';
    }

    private function templateBody(): string {
        return (string) file_get_contents($this->templatePath());
    }

    public function test_file_exists_at_theme_root(): void {
        $this->assertFileExists($this->templatePath());
    }

    public function test_header_comment_links_prd_and_context(): void {
        $body = $this->templateBody();
        $header = substr($body, 0, 4000);
        $this->assertStringContainsString('PRDv1.1_CUSTOMIZER_API.md', $header);
        $this->assertStringContainsString('CONTEXT.md', $header);
    }

    public function test_header_documents_required_argument_keys(): void {
        $header = substr($this->templateBody(), 0, 4000);
        foreach (['id', 'type', 'label', 'section', 'selector', 'property'] as $key) {
            $this->assertMatchesRegularExpression(
                "/\b{$key}\b/",
                $header,
                "Header block should document the `{$key}` argument."
            );
        }
    }

    public function test_one_commented_example_per_v1_type(): void {
        $body = $this->templateBody();
        foreach (self::V1_TYPES as $type) {
            $this->assertMatchesRegularExpression(
                "/'type'\s*=>\s*'{$type}'/",
                $body,
                "Template missing a `'type' => '{$type}'` example."
            );
        }
    }

    private function templateExamplesSection(): string {
        $body = $this->templateBody();
        $sep  = '/* -------------- Write your custom customizer controls below this line';
        $pos  = strpos($body, $sep);
        return $pos !== false ? substr($body, 0, $pos) : $body;
    }

    public function test_examples_are_commented_out(): void {
        $section = $this->templateExamplesSection();
        $lines   = preg_split('/\R/', $section);
        foreach ($lines as $n => $line) {
            if (preg_match('/^\s*dynamo_config_customizer\s*\(/', $line)) {
                $this->fail(
                    "Line " . ($n + 1) . " contains a live `dynamo_config_customizer(` call in the examples section: '"
                    . trim($line) . "'. All examples must be commented out."
                );
            }
        }
        $this->assertMatchesRegularExpression(
            '~(//|#|\*)\s*dynamo_config_customizer\s*\(~',
            $section,
            'Template must contain at least one commented `dynamo_config_customizer(` example.'
        );
    }

    private function locateTypeBlock(string $body, string $type): int {
        $matched = preg_match("/'type'\s*=>\s*'{$type}'/", $body, $_, PREG_OFFSET_CAPTURE);
        $this->assertSame(1, $matched, "Could not find the `{$type}` example.");
        return (int) $_[0][1];
    }

    public function test_code_example_carries_code_type(): void {
        $body = $this->templateBody();
        $offset = $this->locateTypeBlock($body, 'code');
        $window = substr($body, max(0, $offset - 400), 900);
        $this->assertMatchesRegularExpression(
            "/'code_type'\s*=>/",
            $window,
            'The `code` example must demonstrate `code_type`.'
        );
    }

    public function test_choices_example_present_for_radio_and_select(): void {
        $body = $this->templateBody();
        foreach (['radio', 'select'] as $type) {
            $offset = $this->locateTypeBlock($body, $type);
            $window = substr($body, max(0, $offset - 200), 1200);
            $this->assertMatchesRegularExpression(
                "/'choices'\s*=>/",
                $window,
                "The `{$type}` example must demonstrate a `choices` map."
            );
        }
    }

    public function test_range_example_demonstrates_input_attrs(): void {
        $body = $this->templateBody();
        $offset = $this->locateTypeBlock($body, 'range');
        $window = substr($body, max(0, $offset - 200), 800);
        $this->assertMatchesRegularExpression(
            "/'input_attrs'\s*=>/",
            $window,
            'The `range` example must demonstrate `input_attrs`.'
        );
    }

    public function test_examples_section_contains_no_live_binding_calls(): void {
        // The examples section (above the "Write your custom controls" separator) must
        // not contain any uncommented dynamo_config_customizer() calls — these should
        // remain illustrative examples only. Site-specific bindings go below the separator.
        $section = $this->templateExamplesSection();
        $this->assertDoesNotMatchRegularExpression(
            '/^\s*dynamo_config_customizer\s*\(/m',
            $section,
            'The template examples section must not contain any live (non-commented) dynamo_config_customizer() calls.'
        );
    }
}
