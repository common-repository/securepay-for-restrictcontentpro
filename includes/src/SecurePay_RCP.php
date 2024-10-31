<?php
/**
 * SecurePay for Restrict Content Pro.
 *
 * @author  SecurePay Sdn Bhd
 * @license GPL-2.0+
 *
 * @see    https://securepay.net
 */
\defined('ABSPATH') || exit;

final class SecurePay_RCP
{
    private static function register_locale()
    {
        add_action(
            'plugins_loaded',
            function () {
                load_plugin_textdomain(
                    'securepayrcp',
                    false,
                    SECUREPAY_RCP_PATH.'languages/'
                );
            },
            0
        );
    }

    public static function register_admin_hooks()
    {
        add_action(
            'plugins_loaded',
            function () {
                if (current_user_can(apply_filters('capability', 'manage_options'))) {
                    add_action('all_admin_notices', [__CLASS__, 'callback_compatibility'], \PHP_INT_MAX);
                }
            }
        );
    }

    public static function register_addon_hooks()
    {
        add_filter('rcp_payment_gateways', function ($gateways) {
            $gateways['securepay_payment'] = [
                'label' => 'SecurePay',
                'admin_label' => 'SecurePay',
                'class' => 'RCP_SecurePay_Payment',
            ];

            return $gateways;
        });

        add_action('rcp_payments_settings', function ($rcp_options) {
            if (!empty($rcp_options['gateways']) && !empty($rcp_options['gateways']['securepay_payment'])) {
                include SECUREPAY_RCP_PATH.'/includes/admin/settings.php';
            }
        }, \PHP_INT_MAX);

        add_action('plugins_loaded', function () {
            if (self::is_rcp_activated()) {
                require_once SECUREPAY_RCP_PATH.'/includes/src/RCP_SecurePay_Payment.php';
            }
        }, \PHP_INT_MAX);
    }

    private static function is_rcp_activated()
    {
        return class_exists('RCP_Payment_Gateway', false);
    }

    private static function register_autoupdates()
    {
        add_filter(
            'auto_update_plugin',
            function ($update, $item) {
                if (SECUREPAY_RCP_SLUG === $item->slug) {
                    return !\defined('SECUREPAY_RCP_AUTOUPDATE_DISABLED') || !SECUREPAY_RCP_AUTOUPDATE_DISABLED ? true : false;
                }

                return $update;
            },
            \PHP_INT_MAX,
            2
        );
    }

    public static function callback_compatibility()
    {
        if (!self::is_rcp_activated()) {
            $html = '<div id="securepay-notice" class="notice notice-error is-dismissible">';
            $html .= '<p>'.esc_html__('SecurePay require Restrict Content Pro plugin. Please install and activate.', 'securepayrcp').'</p>';
            $html .= '</div>';
            echo wp_kses_post($html);
        }
    }

    public static function activate()
    {
        return true;
    }

    public static function deactivate()
    {
        return true;
    }

    public static function uninstall()
    {
        return true;
    }

    public static function register_plugin_hooks()
    {
        register_activation_hook(SECUREPAY_RCP_HOOK, [__CLASS__, 'activate']);
        register_deactivation_hook(SECUREPAY_RCP_HOOK, [__CLASS__, 'deactivate']);
        register_uninstall_hook(SECUREPAY_RCP_HOOK, [__CLASS__, 'uninstall']);
    }

    public static function attach()
    {
        self::register_locale();
        self::register_admin_hooks();
        self::register_addon_hooks();
        self::register_autoupdates();
    }
}
