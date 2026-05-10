<?php
/**
 * Plugin Name:       External Connections Blocker
 * Plugin URI:        https://adschi.com
 * Description:       Blocks external HTTP requests with customizable settings and editable domain lists for whitelist and blacklist.
 * Version:           1.4.0
 * Author:            Mohammad Babaei
 * Author URI:        https://adschi.com
 * License:           GPL-2.0+
 * Text Domain:       external-blocker
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ECB_Plugin {
    private $options;

    public function __construct() {
        $defaults = [
            'allow_google'       => 1,
            'block_external'     => 1,
            'disable_updates'    => 1,
            'disable_xmlrpc'     => 1,
            'disable_emojis'     => 1,
            'disable_google_fonts' => 1,
            'custom_whitelist'   => "",
            'custom_blacklist'   => "",
        ];
        $this->options = wp_parse_args( get_option( 'ecb_settings', [] ), $defaults );

        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'settings_init' ] );
        add_filter( 'pre_http_request', [ $this, 'filter_http_requests' ], 10, 3 );
        add_action( 'admin_notices', [ $this, 'admin_banner_global' ] );

        if ( $this->options['disable_updates'] ) {
            add_filter( 'automatic_updater_disabled', '__return_true' );
            add_filter( 'wp_auto_update_core', '__return_false' );
            add_filter( 'site_transient_update_plugins', '__return_null' );
            add_filter( 'site_transient_update_themes', '__return_null' );
            add_filter( 'site_transient_update_core', '__return_null' );
        }
        if ( $this->options['disable_xmlrpc'] ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
        }
        if ( $this->options['disable_emojis'] ) {
            remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
            remove_action( 'wp_print_styles', 'print_emoji_styles' );
        }
        if ( ! empty( $this->options['disable_google_fonts'] ) ) {
            add_filter( 'style_loader_src', [ $this, 'remove_google_fonts' ], 10, 2 );
        }
    }

    public function remove_google_fonts( $src, $handle ) {
        if ( strpos( $src, 'fonts.googleapis.com' ) !== false || strpos( $src, 'fonts.gstatic.com' ) !== false ) {
            return false;
        }
        return $src;
    }

    public function add_settings_page() {
        add_options_page(
            'External Connections Blocker',
            'Connections Blocker',
            'manage_options',
            'ecb-settings',
            [ $this, 'settings_page_html' ]
        );
    }

    public function settings_init() {
        register_setting( 'ecb', 'ecb_settings', [ $this, 'sanitize_settings' ] );

        add_settings_section(
            'ecb_section',
            'ECB Configuration',
            '__return_false',
            'ecb-settings'
        );

        $fields = [
            'allow_google'     => 'Allow Google Domains',
            'block_external'   => 'Block All Other HTTP Requests',
            'disable_updates'  => 'Disable Automatic Updates',
            'disable_xmlrpc'   => 'Disable XML-RPC',
            'disable_emojis'   => 'Disable Emojis',
            'disable_google_fonts' => 'Disable Google Fonts',
        ];
        foreach ( $fields as $field => $label ) {
            add_settings_field(
                $field,
                $label,
                [ $this, 'field_callback' ],
                'ecb-settings',
                'ecb_section',
                [ 'label_for' => $field ]
            );
        }
        // Custom whitelist
        add_settings_field(
            'custom_whitelist',
            'Custom Whitelist Domains (one per line)',
            [ $this, 'textarea_callback' ],
            'ecb-settings',
            'ecb_section',
            [ 'label_for' => 'custom_whitelist' ]
        );
        add_settings_field(
            'custom_blacklist',
            'Custom Blacklist Domains (one per line)',
            [ $this, 'textarea_callback' ],
            'ecb-settings',
            'ecb_section',
            [ 'label_for' => 'custom_blacklist' ]
        );
    }

    public function sanitize_settings( $input ) {
        $output = [];
        foreach ( $input as $key => $value ) {
            if ( in_array( $key, ['custom_whitelist','custom_blacklist'], true ) ) {
                $lines = explode( "\n", sanitize_textarea_field( $value ) );
                $lines = array_map( 'trim', $lines );
                $lines = array_filter( $lines );
                $output[ $key ] = implode( "\n", $lines );
            } else {
                $output[ $key ] = isset( $value ) ? 1 : 0;
            }
        }
        return $output;
    }

    public function field_callback( $args ) {
        $name = $args['label_for'];
        $checked = ! empty( $this->options[$name] ) ? 'checked' : '';
        echo "<input type='checkbox' id='{$name}' name='ecb_settings[{$name}]' value='1' {$checked} />";
    }

    public function textarea_callback( $args ) {
        $name = $args['label_for'];
        $value = esc_textarea( $this->options[$name] );
        echo "<textarea id='{$name}' name='ecb_settings[{$name}]' rows='5' cols='50'>{$value}</textarea>";
    }

    public function filter_http_requests( $pre, $args, $url ) {
        if ( empty( $this->options['block_external'] ) ) {
            return null;
        }
        $host = parse_url( $url, PHP_URL_HOST );
        if ( ! $host ) return true;

        // Built-in Google whitelist
        if ( ! empty( $this->options['allow_google'] ) ) {
            $whitelist = [
                'google.com',
                'googleapis.com',
                'gstatic.com',
                'googletagmanager.com',
                'googletagservices.com',
                'doubleclick.net',
                'adservice.google.com',
            ];
        } else {
            $whitelist = [];
        }
        // Merge with custom whitelist
        $custom_whitelist = explode("\n", $this->options['custom_whitelist']);
        $whitelist = array_merge( $whitelist, $custom_whitelist );
        foreach ( $whitelist as $domain ) {
            if ( stripos( $host, $domain ) !== false ) {
                return null;
            }
        }
        // Custom blacklist has highest priority
        $custom_blacklist = explode("\n", $this->options['custom_blacklist']);
        foreach ( $custom_blacklist as $domain ) {
            if ( stripos( $host, $domain ) !== false ) {
                return true;
            }
        }
        // Default block
        return true;
    }

    public function admin_banner_global() {
        echo '<div class="notice notice-info is-dismissible" style="border-left-color:#00a0d2;">';
        echo '<p style="font-size:16px;">خدمات مشاوره حرفه‌ای و تبلیغات در گوگل با <a href="https://adschi.com" target="_blank" style="text-decoration:underline;">ادزچی</a></p>';
        echo '</div>';
    }

    public function settings_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        echo '<div class="wrap"><h1>External Connections Blocker Settings</h1><form method="post" action="options.php">';
        settings_fields( 'ecb' );
        do_settings_sections( 'ecb-settings' );
        submit_button();
        echo '<div style="position:fixed;bottom:0;left:0;width:100%;text-align:center;background:#fff;border-top:1px solid #ddd;padding:10px;">';
        echo 'خدمات مشاوره حرفه‌ای و تبلیغات در گوگل با <a href="https://adschi.com" target="_blank">ادزچی</a>';
        echo '</div>';
        echo '</form></div>';
    }
}

new ECB_Plugin();
