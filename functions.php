<?php
/**
 * Spirit West UC functions and definitions
 *
 * @see https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @since 1.0.0
 * @package SpiritWestUC 
 */

/**
 * Register Twentytwentytwo block patterns
 */
require get_template_directory() . '/inc/block-patterns.php';

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @since 1.0.0
 *
 * @return void
 */
if ( ! function_exists( 'spiritwestuc_support' ) ) :
	function spiritwestuc_support() {

		// Add support for block styles.
		add_theme_support( 'wp-block-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style.css' );

	}

endif;

add_action( 'after_setup_theme', 'spiritwestuc_support' );

/**
 * Enqueue styles.
 *
 * @since 1.0.0
 *
 * @return void
 */
if ( ! function_exists( 'spiritwestuc_enqueue_scripts_styles' ) ) :

	function spiritwestuc_enqueue_scripts_styles() {
		$manifest_path = get_template_directory() . '/assets/dist/manifest.json';
		$theme_version = wp_get_theme()->get( 'Version' );
		$version_string = is_string( $theme_version ) ? $theme_version : false;
    if (file_exists($manifest_path)) {
			$manifest = json_decode(file_get_contents($manifest_path), true);
			
			// Register cache-busting JS
			if (!empty($manifest['main.js'])) {
				wp_enqueue_script(
					'spiritwestuc',
					get_template_directory_uri() . $manifest['main.js'],
					array(),
					null,
					true
				);
			}

			// Register TEC calendar style overrides
			if ( ! empty( $manifest[ 'calendar.js' ] ) )
				wp_enqueue_script(
					'spiritwestuc-tec-calendar',
					get_template_directory_uri() . $manifest['calendar.js'],
					array(),
					null,
					true
				);
			
			// Register cache-busting theme stylesheet.
			if (!empty($manifest['style.css'])) {
				wp_enqueue_style(
					'spiritwestuc',
					get_template_directory_uri() . $manifest['style.css'],
					array(),
					null
				);
			}
    }
	}
endif;

add_action( 'wp_enqueue_scripts', 'spiritwestuc_enqueue_scripts_styles' );


/**
 * Override WP Global Inline Styles
 * Called by  spiritwestuc_style().
 *
 * @since 1.0.0
 *
 * @return string
 */
if ( ! function_exists( 'spiritwestuc_inline_styles_override' ) ) :
	function spiritwestuc_inline_styles_override() {
		return "
			:root :where(body) {
				/* Remove auto white background from WP global inline style */
				background-color: transparent;
			}
			@media (min-width: 1024px) {

				/* Left Justify Post Title on Sermons/Posts Page */
				[class*='wp-container-'] > .post-title {
					max-width: 100%;
				}
			}	
		";
	}

endif;

wp_add_inline_style( 'spiritwestuc-style', spiritwestuc_inline_styles_override());