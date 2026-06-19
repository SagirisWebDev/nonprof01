<?php
declare(strict_types=1);
/**
 * Dynamo — Customizer Extension Template
 * --------------------------------------
 *
 * Developer-owned file. Drop one `dynamo_config_customizer(...)` call per
 * Binding you want to add to the WordPress Customizer. Ships empty: every
 * example below is commented out so a fresh theme installs no Bindings.
 *
 * A Binding is a Customizer control plus the CSS selector and property its
 * value should drive. The API absorbs settings/control/section/panel
 * registration, sanitization, CSS emission (Variable layer + Rule layer),
 * and live-preview wiring.
 *
 * Reference docs:
 *   - Full argument shape, validation rules, vocabulary tables, and value
 *     categories: `PRDv1.1_CUSTOMIZER_API.md`
 *   - Token vs Binding distinction (don't conflate them): `CONTEXT.md`
 *
 * Argument shape — `dynamo_config_customizer(array $args)`
 * --------------------------------------------------------
 *   Required:
 *     id        string  Slug. Stored as WP setting `dynamo_<id>`. Must be unique.
 *     type      string  One of: color, text, textarea, number, range, select,
 *                       radio, url, image, media, date, code.
 *     label     string  Human-readable control label.
 *     section   string  Section slug. Auto-created on first sight.
 *     selector  string  CSS selector. Pseudo-classes/elements & comma lists OK.
 *     property  string  CSS property. Must be in the v1 whitelist.
 *
 *   Optional:
 *     unit              string    e.g. 'rem', 'px', 's', 'deg'. Whitelisted.
 *                                 Omit when `choices` is set or property takes
 *                                 no unit (color, display, etc.).
 *     default           mixed     Falls back to a type-based default if omitted
 *                                 (`#000000` for color, `0` for number/range,
 *                                 `''` for text-like, first key for choices).
 *     choices           array     Required for `radio`/`select`. Shape:
 *                                   slug => ['label' => 'UI label',
 *                                            'value' => 'CSS value string']
 *     sanitize_callback callable  Overrides the type's default sanitizer.
 *     input_attrs       array     Pass-through to WP_Customize_Control
 *                                 (min, max, step, placeholder, …).
 *     description       string    Help text rendered under the control.
 *     section_label     string    Override the auto-derived section title.
 *     panel             string    Panel slug. Auto-created on first sight.
 *     panel_label       string    Override the auto-derived panel title.
 *     code_type         string    Required for `type: 'code'` (css, javascript,
 *                                 html, json, …).
 *     mime_type         string    Optional filter for `media` / `image`.
 *     requires          array     Property prerequisites — e.g. ['display' => 'grid']
 *                                 to scope a binding to an already-set value.
 *
 * Vocabulary tables (categories, property whitelist, unit whitelist,
 * per-type sanitizer defaults) live in `PRDv1.1_CUSTOMIZER_API.md` and in
 * `includes/class-dynamo-css-vocabulary.php`. Extend them with the filters
 * `dynamo_binding_properties`, `dynamo_binding_units`, `dynamo_binding_categories`.
 *
 * Examples below — copy, uncomment, edit. Each block is one self-contained
 * Binding registration. Order does not matter; sections and panels are
 * auto-created the first time a slug is referenced.
 */

/* ----------------------------------------------------------------------
 * color — WP_Customize_Color_Control. Produces [color] values.
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'       => 'header_bg',
//     'type'     => 'color',
//     'label'    => 'Header background',
//     'section'  => 'header_styling',
//     'selector' => '.site-header',
//     'property' => 'background-color',
//     'default'  => '#0f172a',
// ]);

/* ----------------------------------------------------------------------
 * text — generic input[type=text]. Produces [string].
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'       => 'site_heading_font',
//     'type'     => 'text',
//     'label'    => 'Heading font family',
//     'section'  => 'typography',
//     'selector' => 'h1, h2, h3',
//     'property' => 'font-family',
//     'default'  => '"Inter", system-ui, sans-serif',
// ]);

/* ----------------------------------------------------------------------
 * textarea — generic textarea. Produces [string].
 * Use for multi-line strings; for CSS prefer `type: 'code'`.
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'          => 'footer_credit',
//     'type'        => 'textarea',
//     'label'       => 'Footer credit',
//     'section'     => 'footer',
//     'selector'    => '.site-footer__credit::after',
//     'property'    => 'content',
//     'default'     => '"© 2026 Studio Sagiris — All rights reserved"',
//     'description' => 'Wrapped in quotes so it renders as a CSS string.',
// ]);

/* ----------------------------------------------------------------------
 * number — input[type=number]. Produces [number]; add `unit` to make [length].
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'          => 'card_columns',
//     'type'        => 'number',
//     'label'       => 'Card grid columns',
//     'section'     => 'layout',
//     'selector'    => '.card-grid',
//     'property'    => 'grid-template-columns',
//     'default'     => 3,
//     'input_attrs' => ['min' => 1, 'max' => 6, 'step' => 1],
// ]);

/* ----------------------------------------------------------------------
 * range — input[type=range]. Produces [number], or [length] if `unit` is set.
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'          => 'header_padding_y',
//     'type'        => 'range',
//     'label'       => 'Header vertical padding',
//     'section'     => 'header_styling',
//     'selector'    => '.site-header',
//     'property'    => 'padding-block',
//     'unit'        => 'rem',
//     'default'     => 1.5,
//     'input_attrs' => ['min' => 0, 'max' => 6, 'step' => 0.25],
// ]);

/* ----------------------------------------------------------------------
 * select — generic <select>. Produces [keyword]; `choices` is required.
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'       => 'footer_columns',
//     'type'     => 'select',
//     'label'    => 'Footer columns',
//     'section'  => 'footer',
//     'selector' => '.site-footer__inner',
//     'property' => 'grid-template-columns',
//     'choices'  => [
//         '1' => ['label' => '1 column',  'value' => '1fr'],
//         '2' => ['label' => '2 columns', 'value' => 'repeat(2, 1fr)'],
//         '3' => ['label' => '3 columns', 'value' => 'repeat(3, 1fr)'],
//         '4' => ['label' => '4 columns', 'value' => 'repeat(4, 1fr)'],
//     ],
//     'default'  => '3',
// ]);

/* ----------------------------------------------------------------------
 * radio — generic radio group. Produces [keyword]; `choices` is required.
 * Use a 2-choice radio for CSS-bound binary toggles (sticky/static, on/off).
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'       => 'sidebar_layout',
//     'type'     => 'radio',
//     'label'    => 'Sidebar layout',
//     'section'  => 'layout',
//     'selector' => '.site-main',
//     'property' => 'grid-template-columns',
//     'requires' => ['display' => 'grid'],
//     'choices'  => [
//         'left'  => ['label' => 'Left',  'value' => '300px 1fr'],
//         'right' => ['label' => 'Right', 'value' => '1fr 300px'],
//         'none'  => ['label' => 'None',  'value' => '1fr'],
//     ],
//     'default'  => 'right',
// ]);

/* ----------------------------------------------------------------------
 * url — input[type=url]. Produces [url]. Pair with `background-image` etc.
 * The renderer wraps the value in `url(...)` for url-accepting properties.
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'       => 'hero_bg_url',
//     'type'     => 'url',
//     'label'    => 'Hero background (paste URL)',
//     'section'  => 'hero',
//     'selector' => '.hero',
//     'property' => 'background-image',
//     'default'  => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9',
// ]);

/* ----------------------------------------------------------------------
 * image — WP_Customize_Image_Control. Produces [url] (resolved from media).
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'        => 'site_logo',
//     'type'      => 'image',
//     'label'     => 'Site logo',
//     'section'   => 'branding',
//     'selector'  => '.site-logo',
//     'property'  => 'background-image',
//     'mime_type' => 'image',
// ]);

/* ----------------------------------------------------------------------
 * media — WP_Customize_Media_Control. Stores an attachment ID; the renderer
 * resolves it to a URL at output time. Produces [url].
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'        => 'hero_bg_media',
//     'type'      => 'media',
//     'label'     => 'Hero background (from media library)',
//     'section'   => 'hero',
//     'selector'  => '.hero-media',
//     'property'  => 'background-image',
//     'mime_type' => 'image',
// ]);

/* ----------------------------------------------------------------------
 * date — WP_Customize_Date_Time_Control. Produces [string]. Useful when a
 * date is rendered into a CSS `content` rule, or paired with handwritten CSS
 * that reads `var(--dynamo-<id>)` for a launch-date countdown.
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'       => 'announcement_launch_date',
//     'type'     => 'date',
//     'label'    => 'Announcement launch date',
//     'section'  => 'banner',
//     'selector' => '.banner::before',
//     'property' => 'content',
//     'default'  => '"Launching 2026-06-01"',
// ]);

/* ----------------------------------------------------------------------
 * code — WP_Customize_Code_Editor_Control. Produces [string]. Required arg:
 * `code_type` (css, javascript, html, json, …). Best for compound CSS values
 * such as `box-shadow`, `transform`, or `grid-template-areas`.
 * ---------------------------------------------------------------------- */
// dynamo_config_customizer([
//     'id'        => 'card_shadow',
//     'type'      => 'code',
//     'code_type' => 'css',
//     'label'     => 'Card shadow',
//     'section'   => 'advanced',
//     'selector'  => '.card',
//     'property'  => 'box-shadow',
//     'default'   => '0 4px 12px rgba(0,0,0,0.15), 0 2px 4px rgba(0,0,0,0.08)',
// ]);


/* -------------- Write your custom customizer controls below this line ---------------- */

// ---
// Spirit West United Church — Design Token Bindings
// ---

// Extend the vocabulary so font-size and padding-block can accept text values
// (clamp/max CSS functions exceed the built-in [length,keyword] categories).
add_filter('dynamo_binding_properties', static function (array $map): array {
    foreach (['font-size', 'padding-block'] as $prop) {
        if (!in_array('any', $map[$prop] ?? [], true)) {
            $map[$prop][] = 'any';
        }
    }
    return $map;
});

// Override Dynamo's built-in token defaults with SWUC-specific values.
// These tokens surface in Dynamo's existing Customizer panels (Colours,
// Typography, Spacing) — no duplicate panels are created.
add_filter('dynamo_token_defaults', static function (array $defaults): array {
    // Colours panel
    $defaults['colors-text'] = '#000000';
    $defaults['colors-link'] = '#3999ef';

    // Typography panel — font families (source-serif-pro registered in fonts/fonts.json)
    foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $el) {
        $defaults["typography-{$el}-font-family"] = 'source-serif-pro';
    }

    // Typography panel — font sizes
    $defaults['typography-body-font-size'] = '1.125rem';
    $defaults['typography-h1-font-size']   = 'clamp(3.25rem, 8vw, 6.25rem)';
    $defaults['typography-h2-font-size']   = 'clamp(2.75rem, 6vw, 3.25rem)';
    $defaults['typography-h3-font-size']   = 'clamp(1.5rem, 4vw, 2.75rem)';
    $defaults['typography-h4-font-size']   = 'clamp(1.75rem, 3vw, 2.25rem)';
    $defaults['typography-h5-font-size']   = '1.75rem';
    $defaults['typography-h6-font-size']   = '1.75rem';

    // Spacing panel
    $defaults['spacing-content-padding-x'] = '1.25rem';

    return $defaults;
});

// --- Colours: SWUC-specific (no Dynamo equivalent) ---------------------------

dynamo_config_customizer([
    'id'       => 'swuc_color_heading',
    'type'     => 'color',
    'label'    => 'Heading',
    'section'  => 'swuc_colors',
    'selector' => 'h1, h2, h3, h4, h5, h6',
    'property' => 'color',
    'default'  => '#ffffff',
]);

dynamo_config_customizer([
    'id'       => 'swuc_color_post_title',
    'type'     => 'color',
    'label'    => 'Post title',
    'section'  => 'swuc_colors',
    'selector' => '.entry-title, .page-title',
    'property' => 'color',
    'default'  => '#55606b',
]);

dynamo_config_customizer([
    'id'       => 'swuc_color_sub_heading',
    'type'     => 'color',
    'label'    => 'Sub-heading / meta',
    'section'  => 'swuc_colors',
    'selector' => '.site-description, .post-meta',
    'property' => 'color',
    'default'  => '#b3b3b3',
]);

dynamo_config_customizer([
    'id'       => 'swuc_color_alert',
    'type'     => 'color',
    'label'    => 'Alert',
    'section'  => 'swuc_colors',
    'selector' => '.error, .alert',
    'property' => 'color',
    'default'  => '#cf2e2e',
]);

// --- Type scale: custom selectors (no Dynamo equivalent) ---------------------

dynamo_config_customizer([
    'id'       => 'swuc_font_size_small',
    'type'     => 'text',
    'label'    => 'Font size: small',
    'section'  => 'swuc_type_scale',
    'selector' => 'small, .text-small',
    'property' => 'font-size',
    'default'  => '1rem',
]);

// --- Spacing: custom sections (no Dynamo equivalent) -------------------------
// Outer container padding-inline uses the Dynamo spacing-content-padding-x
// token (set above); the three section-level values have no Dynamo equivalent.

dynamo_config_customizer([
    'id'       => 'swuc_spacing_small',
    'type'     => 'text',
    'label'    => 'Spacing: small',
    'section'  => 'swuc_spacing',
    'selector' => '.site-header',
    'property' => 'padding-block',
    'default'  => '0',
]);

dynamo_config_customizer([
    'id'       => 'swuc_spacing_medium',
    'type'     => 'text',
    'label'    => 'Spacing: medium',
    'section'  => 'swuc_spacing',
    'selector' => '.entry-content',
    'property' => 'padding-block',
    'default'  => 'clamp(2rem, 8vw, 6rem)',
]);

dynamo_config_customizer([
    'id'       => 'swuc_spacing_large',
    'type'     => 'text',
    'label'    => 'Spacing: large',
    'section'  => 'swuc_spacing',
    'selector' => '.site-section',
    'property' => 'padding-block',
    'default'  => 'clamp(4rem, 10vw, 8rem)',
]);