<?php
declare(strict_types=1);

define('SWUC_VERSION', '2.0.0');
define('SWUC_PATH', trailingslashit(get_stylesheet_directory()));
define('SWUC_URL', trailingslashit(get_stylesheet_directory_uri()));

// Deferred to `after_setup_theme` priority 5 so the parent dynamo_theme's
// functions.php (loaded AFTER the child's) has already required the
// binding API by the time these calls execute.
add_action('after_setup_theme', function(): void {
    if (file_exists(SWUC_PATH . 'dynamo-extend-customizer.php')) {
        require_once SWUC_PATH . 'dynamo-extend-customizer.php';
    }
}, 5);

add_action('wp_enqueue_scripts', function(): void {
    wp_enqueue_style(
        'swuc-overrides',
        SWUC_URL . 'assets/css/swuc-overrides.css',
        ['dynamo-style'],
        SWUC_VERSION
    );

    if (is_front_page()) {
        wp_enqueue_script(
            'swuc-hero',
            SWUC_URL . 'assets/js/swuc-hero.js',
            [],
            SWUC_VERSION,
            true
        );
    }

    wp_enqueue_script(
        'swuc-mobile-close',
        SWUC_URL . 'assets/js/swuc-mobile-close.js',
        ['dynamo-primary-nav'],
        SWUC_VERSION,
        true
    );
}, 20);
