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
if (empty($rcp_options)) {
    return;
}

// general sandbox
//$sandbox = !empty($rcp_options['sandbox']) ? (int)$rcp_options['sandbox'] : 0;

$testmode = !empty($rcp_options['securepay_testmode']) ? (int) $rcp_options['securepay_testmode'] : 0;
$live_token = !empty($rcp_options['securepay_live_token']) ? $rcp_options['securepay_live_token'] : '';
$live_checksum = !empty($rcp_options['securepay_live_checksum']) ? $rcp_options['securepay_live_checksum'] : '';
$live_uid = !empty($rcp_options['securepay_live_uid']) ? $rcp_options['securepay_live_uid'] : '';
$live_partner_uid = !empty($rcp_options['securepay_live_partner_uid']) ? $rcp_options['securepay_live_partner_uid'] : '';

$sandboxmode = !empty($rcp_options['securepay_sandboxmode']) ? $rcp_options['securepay_sandboxmode'] : '';
$sandbox_token = !empty($rcp_options['securepay_sandbox_token']) ? $rcp_options['securepay_sandbox_token'] : '';
$sandbox_checksum = !empty($rcp_options['securepay_sandbox_checksum']) ? $rcp_options['securepay_sandbox_checksum'] : '';
$sandbox_uid = !empty($rcp_options['securepay_sandbox_uid']) ? $rcp_options['securepay_sandbox_uid'] : '';
$sandbox_partner_uid = !empty($rcp_options['securepay_sandbox_partner_uid']) ? $rcp_options['securepay_sandbox_partner_uid'] : '';

$banklist = !empty($rcp_options['securepay_banklist']) ? (int) $rcp_options['securepay_banklist'] : 0;
$banklogo = !empty($rcp_options['securepay_banklogo']) ? (int) $rcp_options['securepay_banklogo'] : 0;

?>
<table class="form-table">
    <tr valign="top">
        <th colspan=2>
            <h3><?php esc_html_e('SecurePay Settings', 'securepayrcp'); ?></h3>
        </th>
    </tr>

    <tr valign="top">
        <th>
            <?php esc_html_e('Enable Test Mode', 'securepayrcp'); ?>
        </th>
        <td>
            <input type="checkbox" value="1" name="rcp_settings[securepay_testmode]" id="rcp_settings[securepay_testmode]" <?php checked($testmode); ?> />
            <label for="rcp_settings[securepay_testmode]"><?php _e('Check this to allow testing SecurePay without credentials.', 'securepayrcp'); ?></label>
        </td>
    </tr>

    <tr valign="top" style="border-top: 1px solid #ccc;">
        <th>
            <?php esc_html_e('Show Bank List', 'securepayrcp'); ?>
        </th>
        <td>
            <input type="checkbox" value="1" name="rcp_settings[securepay_banklist]" id="rcp_settings[securepay_banklist]" <?php checked($banklist); ?> />
            <label for="rcp_settings[securepay_banklist]"><?php _e('Check this to show bank list.', 'securepayrcp'); ?></label>
        </td>
    </tr>

    <tr valign="top">
        <th>
            <?php esc_html_e('Use Supported Bank Logo', 'securepayrcp'); ?>
        </th>
        <td>
            <input type="checkbox" value="1" name="rcp_settings[securepay_banklogo]" id="rcp_settings[securepay_banklogo]" <?php checked($banklogo); ?> />
            <label for="rcp_settings[securepay_banklogo]"><?php _e('Check this to use supported bank logo.', 'securepayrcp'); ?></label>
        </td>
    </tr>

    <tr style="border-top: 1px solid #ccc;">
        <th>
            <label for="rcp_settings[securepay_live_token]"><?php _e('SecurePay Live Token', 'securepayrcp'); ?></label>
        </th>
        <td>
            <input type="text" class="regular-text" id="rcp_settings[securepay_live_token]" style="width: 300px;" name="rcp_settings[securepay_live_token]" value="<?php echo esc_attr($live_token); ?>" />
            <p class="description"><?php _e('Enter your SecurePay Live Token.', 'securepayrcp'); ?></p>
        </td>
    </tr>

    <tr>
        <th>
            <label for="rcp_settings[securepay_live_checksum]"><?php _e('SecurePay Live Checksum Token', 'securepayrcp'); ?></label>
        </th>
        <td>
            <input type="text" class="regular-text" id="rcp_settings[securepay_live_checksum]" style="width: 300px;" name="rcp_settings[securepay_live_checksum]" value="<?php echo esc_attr($live_checksum); ?>" />
            <p class="description"><?php _e('Enter your SecurePay Live Checksum Token.', 'securepayrcp'); ?></p>
        </td>
    </tr>

    <tr>
        <th>
            <label for="rcp_settings[securepay_live_uid]"><?php _e('SecurePay Live UID', 'securepayrcp'); ?></label>
        </th>
        <td>
            <input type="text" class="regular-text" id="rcp_settings[securepay_live_uid]" style="width: 300px;" name="rcp_settings[securepay_live_uid]" value="<?php echo esc_attr($live_uid); ?>" />
            <p class="description"><?php _e('Enter your SecurePay Live UID.', 'securepayrcp'); ?></p>
        </td>
    </tr>

    <tr>
        <th>
            <label for="rcp_settings[securepay_live_partner_uid]"><?php _e('SecurePay Live Partner UID', 'securepayrcp'); ?></label>
        </th>
        <td>
            <input type="text" class="regular-text" id="rcp_settings[securepay_live_partner_uid]" style="width: 300px;" name="rcp_settings[securepay_live_partner_uid]" value="<?php echo esc_attr($live_partner_uid); ?>" />
            <p class="description"><?php _e('Enter your SecurePay Live Partner UID.', 'securepayrcp'); ?></p>
        </td>
    </tr>

    <tr valign="top" style="border-top: 1px solid #ccc;">
        <th>
            <?php esc_html_e('SecurePay Sandbox Mode', 'securepayrcp'); ?>
        </th>
        <td>
            <input type="checkbox" value="1" name="rcp_settings[securepay_sandboxmode]" id="rcp_settings[securepay_sandboxmode]" <?php checked($sandboxmode); ?> />
            <label for="rcp_settings[securepay_sandboxmode]"><?php _e('Check this to enable SecurePay Sandbox Mode and bypass the general Sandbox Mode above.', 'securepayrcp'); ?></label>
        </td>
    </tr>

    <tr>
        <th>
            <label for="rcp_settings[securepay_sandbox_token]"><?php _e('SecurePay Sandbox Token', 'securepayrcp'); ?></label>
        </th>
        <td>
            <input type="text" class="regular-text" id="rcp_settings[securepay_sandbox_token]" style="width: 300px;" name="rcp_settings[securepay_sandbox_token]" value="<?php echo esc_attr($sandbox_token); ?>" />
            <p class="description"><?php _e('Enter your SecurePay Sandbox Token.', 'securepayrcp'); ?></p>
        </td>
    </tr>

    <tr>
        <th>
            <label for="rcp_settings[securepay_sandbox_checksum]"><?php _e('SecurePay Sandbox Checksum Token', 'securepayrcp'); ?></label>
        </th>
        <td>
            <input type="text" class="regular-text" id="rcp_settings[securepay_sandbox_checksum]" style="width: 300px;" name="rcp_settings[securepay_sandbox_checksum]" value="<?php echo esc_attr($sandbox_checksum); ?>" />
            <p class="description"><?php _e('Enter your SecurePay Sandbox Checksum Token.', 'securepayrcp'); ?></p>
        </td>
    </tr>

    <tr>
        <th>
            <label for="rcp_settings[securepay_sandbox_uid]"><?php _e('SecurePay Sandbox UID', 'securepayrcp'); ?></label>
        </th>
        <td>
            <input type="text" class="regular-text" id="rcp_settings[securepay_sandbox_uid]" style="width: 300px;" name="rcp_settings[securepay_sandbox_uid]" value="<?php echo esc_attr($sandbox_uid); ?>" />
            <p class="description"><?php _e('Enter your SecurePay Sandbox UID.', 'securepayrcp'); ?></p>
        </td>
    </tr>

    <tr>
        <th>
            <label for="rcp_settings[securepay_sandbox_partner_uid]"><?php _e('SecurePay Sandbox Partner UID', 'securepayrcp'); ?></label>
        </th>
        <td>
            <input type="text" class="regular-text" id="rcp_settings[securepay_sandbox_partner_uid]" style="width: 300px;" name="rcp_settings[securepay_sandbox_partner_uid]" value="<?php echo esc_attr($sandbox_partner_uid); ?>" />
            <p class="description"><?php _e('Enter your SecurePay Sandbox Partner UID.', 'securepayrcp'); ?></p>
        </td>
    </tr>

</table>