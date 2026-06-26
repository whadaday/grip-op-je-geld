<?php
/**
 * Plugin Name: Grip op je geld
 * Description: Interactieve budgettool voor jongeren — gebruik [begrotingstool] als shortcode.
 * Version:     1.0.1
 * Author:      Angelo Vaudo
 * License:     GPL-2.0-or-later
 * Text Domain: grip-op-je-geld
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GOGJ_VERSION', '1.0.1' );
define( 'GOGJ_DIR',     plugin_dir_path( __FILE__ ) );
define( 'GOGJ_URL',     plugin_dir_url( __FILE__ ) );

require_once GOGJ_DIR . 'inc/faq.php';
require_once GOGJ_DIR . 'inc/shortcode.php';

// ── Paginatemplate "Leeg" registreren ──────────────────────────────────────

add_filter( 'theme_page_templates', function( $templates ) {
    $templates['begrotingstool-leeg'] = __( 'Grip op je geld', 'begrotingstool' );
    return $templates;
} );

add_filter( 'template_include', function( $template ) {
    if ( is_page() && get_page_template_slug() === 'begrotingstool-leeg' ) {
        return GOGJ_DIR . 'templates/leeg.php';
    }
    return $template;
} );

add_action( 'wp_enqueue_scripts', function() {
    if ( ! is_singular() ) {
        return;
    }
    $post = get_post();
    if ( ! $post || ! has_shortcode( $post->post_content, 'begrotingstool' ) ) {
        return;
    }

    wp_enqueue_style(
        'begrotingstool',
        GOGJ_URL . 'assets/css/begrotingstool.css',
        array(),
        GOGJ_VERSION
    );
    wp_enqueue_script(
        'bt-jspdf',
        GOGJ_URL . 'assets/js/vendor/jspdf.umd.min.js',
        array(),
        '2.5.1',
        true
    );
    wp_enqueue_script(
        'bt-jspdf-autotable',
        GOGJ_URL . 'assets/js/vendor/jspdf.plugin.autotable.min.js',
        array( 'bt-jspdf' ),
        '3.8.4',
        true
    );
    wp_enqueue_script(
        'begrotingstool',
        GOGJ_URL . 'assets/js/main.js',
        array( 'bt-jspdf-autotable' ),
        GOGJ_VERSION,
        true
    );
} );
