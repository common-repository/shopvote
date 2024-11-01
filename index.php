<?php
/**
 * Plugin Name: SHOPVOTE
 * Plugin Slug: shopvote
 * Plugin URI: https://plugins.shopvote.de/shopvote-integrationsanleitung-fuer-woocommerce/
 * Description: Plugin for the legally secure collection of shop reviews and / or product reviews and the integration of the SHOPVOTE badge.
 * Author: SHOPVOTE
 * Author URI: https://www.shopvote.de
 * Text Domain: shopvote
 * Version: 2.1.1
 * Stable tag: 2.1.1
 * Requires at least: 5.2
 * Tested up to: 6.0.2
 * Requires PHP: 5.5
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
class ShopvotePlugin {
    const VERSION = '2.1.1';

    private static function registerSettingsPage() {
        add_action( 'admin_menu', function () {
            add_options_page(
                __('SHOPVOTE Settings', 'shopvote'),
                __('SHOPVOTE Settings', 'shopvote'),
                'manage_options',
                'shopvote',
                function () {
                    if (!current_user_can('manage_options')) {
                        wp_die(__('You do not have sufficient permissions to access this page.'));
                    }
                    if (!empty(\Shopvote\Settings::getUserShop())) {
                        require_once 'template/settings.php';
                    } else {
                        require_once 'template/login.php';
                    }
                }
            );
        });

        add_filter(
            'plugin_action_links_' . plugin_basename(__FILE__),
            function ($links) {
                $links[] = '<a href="' . admin_url('options-general.php?page=shopvote') . '">' . esc_html(__('Settings', 'shopvote')) . '</a>';
                return $links;
            }
        );
    }

    private static function registerAdminResources() {
        $pluginIsOpen = is_admin()
            && isset($_GET['page']) && ($_GET['page'] === 'shopvote');

        if ($pluginIsOpen) {
            add_action('admin_enqueue_scripts', function () {
                wp_enqueue_style(
                    'shopvote_admin_style',
                    plugins_url('/assets/css/plugin.css', __FILE__),
                    [],
                    self::VERSION,
                    'all'
                );
            });
        }
    }

    public static function uninstall() {
        \Shopvote\Settings::clearAll();
    }

    private static function registerInstallationRoutines() {
        register_uninstall_hook(__FILE__, self::class.'::uninstall');
    }

    public static function run() {
        require_once __DIR__ . '/includes/Settings.php';
        require_once __DIR__ . '/includes/AjaxHandler.php';

        add_action('init', function () {
            load_plugin_textdomain('shopvote', false, plugin_basename(__DIR__).'/languages');
        });

        self::registerInstallationRoutines();
        self::registerAdminResources();
        self::registerSettingsPage();

        if (\Shopvote\Settings::isSetup()) {
            require_once __DIR__ . '/includes/FrontendResourcesMananger.php';
            \Shopvote\FrontendResourcesMananger::register();
        }

        \Shopvote\AjaxHandler::register();
    }
}

ShopvotePlugin::run();
