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

class RCP_SecurePay_Payment extends RCP_Payment_Gateway
{
    private $sp_testmode = false;
    private $sp_payment_url;
    private $sp_token;
    private $sp_checksum;
    private $sp_uid;
    private $sp_partner_uid;
    private $sp_dosandbox = false;
    private $sp_banklist = false;
    private $sp_banklogo = false;

    public function init()
    {
        global $rcp_options;

        $this->supports[] = 'one-time';
        $this->currency = 'MYR';

        $this->sp_testmode = !empty($rcp_options['securepay_testmode']) && 1 === (int) $rcp_options['securepay_testmode'];
        $this->sp_sandboxmode = !empty($rcp_options['securepay_sandboxmode']) && 1 === (int) $rcp_options['securepay_sandboxmode'];
        $this->sp_dosandbox = $this->test_mode || $this->sp_testmode || $this->sp_sandboxmode ? true : false;

        $this->sp_banklist = !empty($rcp_options['securepay_banklist']) && 1 === (int) $rcp_options['securepay_banklist'];
        $this->sp_banklogo = !empty($rcp_options['securepay_banklogo']) && 1 === (int) $rcp_options['securepay_banklogo'];

        if ($this->sp_testmode) {
            $this->sp_payment_url = SECUREPAY_RCP_ENDPOINT_SANDBOX;
            $this->sp_token = 'GFVnVXHzGEyfzzPk4kY3';
            $this->sp_checksum = '3faa7b27f17c3fb01d961c08da2b6816b667e568efb827544a52c62916d4771d';
            $this->sp_uid = '4a73a364-6548-4e17-9130-c6e9bffa3081';
            $this->sp_partner_uid = '';
        } else {
            if ($this->sp_dosandbox) {
                $this->sp_payment_url = SECUREPAY_RCP_ENDPOINT_SANDBOX;
                $this->sp_token = $rcp_options['securepay_sandbox_token'];
                $this->sp_checksum = $rcp_options['securepay_sandbox_checksum'];
                $this->sp_uid = $rcp_options['securepay_sandbox_uid'];
                $this->sp_partner_uid = $rcp_options['securepay_sandbox_partner_uid'];
            } else {
                $this->sp_payment_url = SECUREPAY_RCP_ENDPOINT_LIVE;
                $this->sp_token = $rcp_options['securepay_live_token'];
                $this->sp_checksum = $rcp_options['securepay_live_checksum'];
                $this->sp_uid = $rcp_options['securepay_live_uid'];
                $this->sp_partner_uid = $rcp_options['securepay_live_partner_uid'];
            }
        }
    }

    public function fields()
    {
        return $this->banklist_output();
    }

    public function process_signup()
    {
        global $rcp_options;

        $payment_id = $this->payment->id;

        $query_url = get_bloginfo('url');
        $query_args = 'timeout=0&cancel=0&pid='.$payment_id.'&ru='.$this->return_url;
        $query_hash = base64_encode($query_args);
        $redirect_url = add_query_arg(
            [
                'listener' => 'securepay',
                'securepay_return' => $query_hash,
            ],
            $query_url
        );
        $callback_url = $redirect_url;

        $query_args = 'timeout=0&cancel=1&pid='.$payment_id.'&ru='.$this->return_url;
        $query_hash = base64_encode($query_args);
        $cancel_url = add_query_arg(
            [
                'listener' => 'securepay',
                'securepay_return' => $query_hash,
            ],
            $query_url
        );

        $query_args = 'timeout=1&cancel=0&pid='.$payment_id.'&ru='.$this->return_url;
        $query_hash = base64_encode($query_args);
        $timeout_url = add_query_arg(
            [
                'listener' => 'securepay',
                'securepay_return' => $query_hash,
            ],
            $query_url
        );

        $amount = $this->initial_amount;

        $customer_name = $this->user_name;

        $user_data = get_userdata($this->user_id);
        if (!empty($user_data)) {
            $user_name = '';
            if ($user_data->first_name) {
                $user_name = $user_data->first_name;
            }

            if ($user_data->last_name) {
                $user_name .= ' '.$user_data->last_name;
            }
            $user_name = trim($user_name);

            if (!empty($user_name)) {
                $customer_name = $user_name;
            }
        }

        $customer_email = $this->email;
        $customer_phone = '';

        if (empty($this->sp_checksum) || empty($this->sp_uid) || empty($this->sp_token)) {
            $error = esc_html__('Invalid SecurePay credentials, please verify SecurePay settings.', 'securepayrcp');
            exit($error);
        }

        $securepay_token = $this->sp_token;
        $securepay_checksum = $this->sp_checksum;
        $securepay_uid = $this->sp_uid;
        $securepay_partner_uid = $this->sp_partner_uid;
        $securepay_payment_url = $this->sp_payment_url;

        $description = 'Subscription '.$this->subscription_name;

        $securepay_sign = $this->calculate_sign($securepay_checksum, $customer_email, $customer_name, $customer_phone, $redirect_url, $payment_id, $description, $redirect_url, $amount, $securepay_uid);

        $buyer_bank_code = !empty($_POST['buyer_bank_code']) ? sanitize_text_field($_POST['buyer_bank_code']) : false;

        $securepay_args['order_number'] = esc_attr($payment_id);
        $securepay_args['buyer_name'] = esc_attr($customer_name);
        $securepay_args['buyer_email'] = esc_attr($customer_email);
        $securepay_args['buyer_phone'] = esc_attr($customer_phone);
        $securepay_args['product_description'] = esc_attr($description);
        $securepay_args['transaction_amount'] = esc_attr($amount);
        $securepay_args['redirect_url'] = esc_url_raw($redirect_url);
        $securepay_args['callback_url'] = esc_url_raw($callback_url);
        $securepay_args['cancel_url'] = esc_url_raw($cancel_url);
        $securepay_args['timeout_url'] = esc_url_raw($timeout_url);
        $securepay_args['token'] = esc_attr($securepay_token);
        $securepay_args['partner_uid'] = esc_attr($securepay_partner_uid);
        $securepay_args['checksum'] = esc_attr($securepay_sign);
        $securepay_args['payment_source'] = 'restrictcontentpro';

        if (!empty($rcp_options['securepay_banklist']) && 1 === (int) $rcp_options['securepay_banklist'] && !empty($buyer_bank_code)) {
            $securepay_args['buyer_bank_code'] = esc_attr($buyer_bank_code);
        }

        $output = '<!doctype html><html><head><title>SecurePay</title>';
        $output .= '<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">';
        $output .= '<meta http-equiv="Pragma" content="no-cache"><meta http-equiv="Expires" content="0">';
        $output .= '</head><body>';
        $output .= '<form name="order" id="securepay_payment" method="post" action="'.esc_url_raw($securepay_payment_url).'payments">';
        foreach ($securepay_args as $f => $v) {
            $output .= '<input type="hidden" name="'.$f.'" value="'.$v.'">';
        }

        $output .= '</form>';
        $output .= wp_get_inline_script_tag('document.getElementById( "securepay_payment" ).submit();');
        $output .= '</body></html>';
        exit($output);
    }

    public function process_webhooks()
    {
        $this->process_callback();
    }

    private function get_bank_list($force = false)
    {
        if (is_user_logged_in()) {
            $force = true;
        }

        $bank_list = $force ? false : get_transient(SECUREPAY_RCP_SLUG.'_banklist');
        $endpoint_pub = $this->sp_dosandbox ? SECUREPAY_RCP_ENDPOINT_PUBLIC_SANDBOX : SECUREPAY_RCP_ENDPOINT_PUBLIC_LIVE;

        if (empty($bank_list)) {
            $remote = wp_remote_get(
                $endpoint_pub.'/banks/b2c?status',
                [
                    'timeout' => 10,
                    'user-agent' => SECUREPAY_RCP_SLUG.'/'.SECUREPAY_RCP_VERSION,
                    'headers' => [
                        'Accept' => 'application/json',
                        'Referer' => home_url(),
                    ],
                ]
            );

            if (!is_wp_error($remote) && isset($remote['response']['code']) && 200 === $remote['response']['code'] && !empty($remote['body'])) {
                $data = json_decode($remote['body'], true);
                if (!empty($data) && \is_array($data) && !empty($data['fpx_bankList'])) {
                    $list = $data['fpx_bankList'];
                    foreach ($list as $arr) {
                        $status = 1;
                        if (empty($arr['status_format2']) || 'offline' === $arr['status_format1']) {
                            $status = 0;
                        }

                        $bank_list[$arr['code']] = [
                            'name' => $arr['name'],
                            'status' => $status,
                        ];
                    }

                    if (!empty($bank_list) && \is_array($bank_list)) {
                        set_transient(SECUREPAY_RCP_SLUG.'_banklist', $bank_list, 60);
                    }
                }
            }
        }

        return !empty($bank_list) && \is_array($bank_list) ? $bank_list : false;
    }

    private function is_bank_list(&$bank_list = '')
    {
        if ($this->sp_banklist) {
            $bank_list = $this->get_bank_list(false);

            return !empty($bank_list) && \is_array($bank_list) ? true : false;
        }

        $bank_list = '';

        return false;
    }

    public function scripts()
    {
        $this->securepay_scripts();
    }

    private function securepay_scripts()
    {
        if (!is_admin()) {
            $version = SECUREPAY_RCP_VERSION.'x'.(\defined('WP_DEBUG') && WP_DEBUG ? time() : date('Ymdh'));
            $slug = SECUREPAY_RCP_SLUG;
            $url = SECUREPAY_RCP_URL;
            $selectid = 'securepayselect2';
            $selectdeps = [];
            if (wp_script_is('select2', 'enqueued')) {
                $selectdeps = ['jquery', 'select2'];
            } elseif (wp_script_is('selectWoo', 'enqueued')) {
                $selectdeps = ['jquery', 'selectWoo'];
            } elseif (wp_script_is($selectid, 'enqueued')) {
                $selectdeps = ['jquery', $selectid];
            }

            if (empty($selectdeps)) {
                wp_enqueue_style($selectid, $url.'includes/admin/min/select2.min.css', null, $version);
                wp_enqueue_script($selectid, $url.'includes/admin/min/select2.min.js', ['jquery'], $version);
                $selectdeps = ['jquery', $selectid];
            }

            wp_enqueue_script($slug, $url.'includes/admin/securepayrcp.js', $selectdeps, $version);

            // remove jquery
            unset($selectdeps[0]);

            wp_enqueue_style($selectid.'-helper', $url.'includes/admin/securepayrcp.css', $selectdeps, $version);
            wp_add_inline_script($slug, 'function securepaybankrcp() { if ( "function" === typeof(securepayrcp_bank_select) ) { securepayrcp_bank_select(jQuery, "'.$url.'includes/admin/bnk/", '.time().', "'.$version.'"); }}');
        }
    }

    private function banklist_output()
    {
        $html = '';
        $bank_list = '';

        if ($this->is_bank_list($bank_list)) {
            $bank_id = !empty($_POST['buyer_bank_code']) ? sanitize_text_field($_POST['buyer_bank_code']) : false;
            $image = false;
            if ($this->sp_banklogo) {
                $image = SECUREPAY_RCP_URL.'includes/admin/securepay-bank-alt.png';
            }

            $html = '<div id="spwfmbody-fpxbank" class="spwfmbody">';
            $html .= '<p class="rcp_subscription_message">Pay with SecurePay</p>';

            if (!empty($image)) {
                $html .= '<img src="'.$image.'" class="spwfmlogo">';
            }

            $html .= '<select name="buyer_bank_code" id="buyer_bank_code">';
            $html .= "<option value=''>Please Select Bank</option>";
            foreach ($bank_list as $id => $arr) {
                $name = $arr['name'];
                $status = $arr['status'];

                $disabled = empty($status) ? ' disabled' : '';
                $offline = empty($status) ? ' (Offline)' : '';
                $selected = $id === $bank_id ? ' selected' : '';
                $html .= '<option value="'.$id.'"'.$selected.$disabled.'>'.$name.$offline.'</option>';
            }
            $html .= '</select>';

            $html .= '</div>';

            $html .= wp_get_inline_script_tag('if ( "function" === typeof(securepaybankrcp()) ) {securepaybankrcp();}', ['id' => SECUREPAY_RCP_SLUG.'-bankselect']);
        }

        return $html;
    }

    private function calculate_sign($checksum, $a, $b, $c, $d, $e, $f, $g, $h, $i)
    {
        $str = $a.'|'.$b.'|'.$c.'|'.$d.'|'.$e.'|'.$f.'|'.$g.'|'.$h.'|'.$i;

        return hash_hmac('sha256', $str, $checksum);
    }

    private function sanitize_response()
    {
        // fix response from api
        $req = $_SERVER['REQUEST_URI'];
        if (false !== strpos($req, 'securepay_return')) {
            $req = str_replace('&amp;', '&', $req);
            $req = str_replace('%26amp%3B', '&', $req);
            $req = str_replace('amp%3B', '&', $req);

            parse_str($req, $dataq);
            if (!empty($dataq)) {
                foreach ($dataq as $k => $v) {
                    $_REQUEST[$k] = $v;
                }
            }
        }

        $params = [
            'amount',
            'bank',
            'buyer_email',
            'buyer_name',
            'buyer_phone',
            'checksum',
            'client_ip',
            'created_at',
            'created_at_unixtime',
            'currency',
            'exchange_number',
            'fpx_status',
            'fpx_status_message',
            'fpx_transaction_id',
            'fpx_transaction_time',
            'id',
            'interface_name',
            'interface_uid',
            'merchant_reference_number',
            'name',
            'order_number',
            'payment_id',
            'payment_method',
            'payment_status',
            'receipt_url',
            'retry_url',
            'source',
            'status_url',
            'transaction_amount',
            'transaction_amount_received',
            'uid',
            'securepay_return',
        ];

        $response_params = [];
        if (isset($_REQUEST)) {
            foreach ($params as $k) {
                if (isset($_REQUEST[$k])) {
                    $response_params[$k] = sanitize_text_field($_REQUEST[$k]);
                }
            }
        }

        return $response_params;
    }

    private function response_status($response_params)
    {
        if ((isset($response_params['payment_status']) && 'true' === $response_params['payment_status']) || (isset($response_params['fpx_status']) && 'true' === $response_params['fpx_status'])) {
            return true;
        }

        return false;
    }

    private function is_response_callback($response_params)
    {
        if (isset($response_params['fpx_status'])) {
            return true;
        }

        return false;
    }

    private function redirect($redirect)
    {
        if (!headers_sent()) {
            wp_redirect($redirect);
            exit;
        }

        $html = "<script>window.location.replace('".$redirect."');</script>";
        $html .= '<noscript><meta http-equiv="refresh" content="1; url='.$redirect.'">Redirecting..</noscript>';
        echo wp_kses(
            $html,
            [
                'script' => [],
                'noscript' => [],
                'meta' => [
                    'http-equiv' => [],
                    'content' => [],
                ],
            ]
        );
        exit;
    }

    public function process_callback()
    {
        global $rcp_payments_db, $rcp_options;

        $response_params = $this->sanitize_response();

        if (!empty($response_params) && !empty($response_params['securepay_return'])) {
            $hash = base64_decode($response_params['securepay_return']);
            if (false === $hash) {
                exit('failed to decode securepay_return');
            }

            parse_str($hash, $data);
            if (empty($data) || !\is_array($data) || empty($data['pid']) || empty($data['ru'])) {
                exit('failed to decode securepay_return');
            }

            $success_url = !empty($this->return_url) ? $this->return_url : $data['ru'];
            $failed_url = get_permalink($rcp_options['account_page']);

            if (!empty($response_params['order_number'])) {
                $success = $this->response_status($response_params);

                $is_callback = $this->is_response_callback($response_params);
                $callback = $is_callback ? 'Callback' : 'Redirect';
                $receipt_link = !empty($response_params['receipt_url']) ? $response_params['receipt_url'] : '';
                $status_link = !empty($response_params['status_url']) ? $response_params['status_url'] : '';
                $retry_link = !empty($response_params['retry_url']) ? $response_params['retry_url'] : '';

                $payment_id = absint($response_params['order_number']);
                $payment = $rcp_payments_db->get_payment($payment_id);
                if (\is_object($payment) && ('pending' === $payment->status || 'abandoned' === $payment->status)) {
                    $membership = rcp_get_membership_by('subscription_key', $payment->subscription_key);

                    if ($success) {
                        $note = 'SecurePay payment successful<br>';
                        $note .= 'Response from: '.$callback.'<br>';
                        $note .= 'Transaction ID: '.$response_params['merchant_reference_number'].'<br>';

                        if (!empty($receipt_link)) {
                            $note .= 'Receipt link: <a href="'.$receipt_link.'" target=new rel="noopener">'.$receipt_link.'</a><br>';
                        }

                        if (!empty($status_link)) {
                            $note .= 'Status link: <a href="'.$status_link.'" target=new rel="noopener">'.$status_link.'</a><br>';
                        }

                        $rcp_payments_db->update(
                            $payment_id,
                            [
                                'transaction_id' => $response_params['merchant_reference_number'],
                                'status' => 'complete',
                            ]
                        );

                        if (!empty($membership)) {
                            $membership->add_note($note);
                        }

                        $this->redirect($success_url);
                        exit;
                    }

                    $note = 'SecurePay payment failed<br>';
                    $note .= 'Response from: '.$callback.'<br>';
                    $note .= 'Transaction ID: '.$response_params['merchant_reference_number'].'<br>';

                    if (!empty($retry_link)) {
                        $note .= 'Retry link: <a href="'.$retry_link.'" target=new rel="noopener">'.$retry_link.'</a><br>';
                    }

                    if (!empty($status_link)) {
                        $note .= 'Status link: <a href="'.$status_link.'" target=new rel="noopener">'.$status_link.'</a><br>';
                    }

                    $rcp_payments_db->update(
                        $payment_id,
                        [
                            'transaction_id' => $response_params['merchant_reference_number'],
                            'status' => 'failed',
                        ]
                    );

                    if (!empty($membership)) {
                        $membership->add_note($note);
                    }
                }

                $this->redirect($failed_url);
                exit;
            }

            // cancel / timeout
            if (!empty($data['cancel']) || !empty($data['timeout'])) {
                $return_url = get_permalink($rcp_options['account_page']);

                $status = 'abandoned';
                $payment_id = $data['pid'];
                $payment = $rcp_payments_db->get_payment($payment_id);
                if (\is_object($payment) && ('pending' === $payment->status || 'abandoned' === $payment->status)) {
                    $membership = rcp_get_membership_by('subscription_key', $payment->subscription_key);
                    $note = 'SecurePay payment '.$status.'<br>';

                    $rcp_payments_db->update(
                        $payment_id,
                        [
                            'transaction_id' => '',
                            'status' => $status,
                        ]
                    );

                    if (!empty($membership)) {
                        $membership->add_note($note);
                    }

                    $this->redirect($failed_url);
                    exit;
                }

                exit('failed to decode return_url from securepay_return');
            }
        }
    }
}
