<?php
/**
 * SecurePay for Restrict Content Pro.
 *
 * @author  SecurePay Sdn Bhd
 * @license GPL-2.0+
 *
 * @see    https://securepay.net
 */

/*
 * @wordpress-plugin
 * Plugin Name:         SecurePay for Restrict Content Pro
 * Plugin URI:          https://www.securepay.my/?utm_source=wp-plugins-restrictcontentpro&utm_campaign=plugin-uri&utm_medium=wp-dash
 * Version:             1.0.4
 * Description:         SecurePay payment platform plugin for Restrict Content Pro
 * Author:              SecurePay Sdn Bhd
 * Author URI:          https://www.securepay.my/?utm_source=wp-plugins-restrictcontentpro&utm_campaign=author-uri&utm_medium=wp-dash
 * Requires at least:   5.4
 * Requires PHP:        7.2
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         securepayrcp
 * Domain Path:         /languages
 */

if (!\defined('ABSPATH') || \defined('SECUREPAY_RCP_FILE')) {
    exit;
}

\define('SECUREPAY_RCP_VERSION', '1.0.4');
\define('SECUREPAY_RCP_SLUG', 'securepay-for-restrictcontentpro');
\define('SECUREPAY_RCP_ENDPOINT_LIVE', 'https://securepay.my/api/v1/');
\define('SECUREPAY_RCP_ENDPOINT_SANDBOX', 'https://sandbox.securepay.my/api/v1/');
\define('SECUREPAY_RCP_ENDPOINT_PUBLIC_LIVE', 'https://securepay.my/api/public/v1/');
\define('SECUREPAY_RCP_ENDPOINT_PUBLIC_SANDBOX', 'https://sandbox.securepay.my/api/public/v1/');
\define('SECUREPAY_RCP_FILE', __FILE__);
\define('SECUREPAY_RCP_HOOK', plugin_basename(SECUREPAY_RCP_FILE));
\define('SECUREPAY_RCP_PATH', realpath(plugin_dir_path(SECUREPAY_RCP_FILE)).'/');
\define('SECUREPAY_RCP_URL', trailingslashit(plugin_dir_url(SECUREPAY_RCP_FILE)));

require __DIR__.'/includes/load.php';
SECUREPAY_RCP::attach();
