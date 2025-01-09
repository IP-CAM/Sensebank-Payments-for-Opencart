<?php
namespace Opencart\Catalog\Controller\Extension\Sensebank\Payment;

class Sensebank extends \Opencart\System\Engine\Controller
{
    /**
     * @param $registry
     */
    /*public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language('extension/sensebank/payment/sensebank');
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/sensebank.twig')) {
            $this->have_template = true;
        }
    }*/

	//public $method_library = null;

    /**
     * @return mixed
     */
    public function index(): string
    {
        //$data['action'] = $this->url->link('extension/payment/sensebank/payment', '', true);
        //$data['entry_sensebank_button_confirm'] = $this->language->get('entry_sensebank_button_confirm');
        //return $this->get_template('extension/payment/sensebank', $data);

		$this->load->language('extension/sensebank/payment/sensebank');
		$data['language'] = $this->config->get('config_language');
		return $this->load->view('extension/sensebank/payment/sensebank', $data);
    }

	/**
	 * @return void
	 */
	public function confirm(): void {
		$this->load->language('extension/sensebank/payment/sensebank');

		$json = [];

		if (!isset($this->session->data['order_id'])) {
			$json['error'] = $this->language->get('error_order');
		}

		if (!isset($this->session->data['payment_method']) || $this->session->data['payment_method']['code'] != 'sensebank.sensebank') {
			$json['error'] = $this->language->get('error_payment_method');
		}

		if (!$json) {
			$this->load->model('checkout/order');

			//$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_sensebank_order_status_before_id'));

			//$json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
			$this->payment();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    /**
     * @param $template
     * @param $data
     * @return mixed
     */
    private function get_template($template, $data)
    {
        return $this->load->view($template, $data);
    }

    public function payment()
    {
        $this->initializeGatewayLibrary();
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_number = (int)$order_info['order_id'];
        $amount = round($order_info['total'] * $order_info['currency_value'], 2) * 100;

        $jsonParams_array = array(
            'CMS' => 'Opencart ' . VERSION,
            'Module-Version' => 'Sensebank ' //. $this->method_library->version ?? '',
        );
        if (!empty($order_info['email'])) {
            $jsonParams_array['email'] = $order_info['email'];
        }
        if (!empty($order_info['telephone'])) {
            $jsonParams_array['phone'] = "+" . preg_match('/[7]\d{9}/', $order_info['telephone']) ? $order_info['telephone'] : '';
        }
        if ( isset($this->method_library->enable_back_url_settings)
			&& $this->method_library->enable_back_url_settings
            && !empty($this->config->get('payment_sensebank_backToShopURL'))
        ) {
            $jsonParams_array['backToShopUrl'] = $this->config->get('payment_sensebank_backToShopURL');
        }
        $orderBundle = [];
        $orderBundle['customerDetails'] = array(
            'email' => $order_info['email'],
            'phone' => preg_match('/[7]\d{9}/', $order_info['telephone']) ? $order_info['telephone'] : ''
        );
        foreach ($this->cart->getProducts() as $product) {
            $product_taxSum = $this->tax->getTax($product['price'], $product['tax_class_id']);
            $product_amount = (round($product['price'] + $product_taxSum, 2)) * $product['quantity'];
            $tax_type = $this->config->get('payment_sensebank_taxType');
            if ($product['tax_class_id'] != 0) {
                $item_rate = $product_taxSum / $product['price'] * 100;
                switch ($item_rate) {
                    case 20:
                        $tax_type = 6;
                        break;
                    case 18:
                        $tax_type = 3;
                        break;
                    case 10:
                        $tax_type = 2;
                        break;
                    case 0:
                        $tax_type = 1;
                        break;
                    default:
                        $tax_type = $this->config->get('payment_sensebank_taxType');
                }
            }
            $product_data = array(
                'positionId' => $product['cart_id'],
                'name' => $product['name'],
                'quantity' => array(
                    'value' => $product['quantity'],
                    'measure' => $this->method_library->getDefaultMeasurement(),
                ),
                'itemAmount' => $product_amount * 100,
                'itemCode' => $product['product_id'] . "_" . $product['cart_id'], //fix by PLUG-1740, PLUG-2620
                'tax' => array(
                    'taxType' => $tax_type,
                    'taxSum' => $product_taxSum * 100
                ),
                'itemPrice' => round($product['price'] + $product_taxSum, 2) * 100,
            );

            $attributes = array();
            $attributes[] = array(
                "name" => "paymentMethod",
                "value" => $this->method_library->getPaymentMethodType()
            );
            $attributes[] = array(
                "name" => "paymentObject",
                "value" => $this->method_library->getPaymentObjectType()
            );
            $product_data['itemAttributes']['attributes'] = $attributes;
            $orderBundle['cartItems']['items'][] = $product_data;
        }
        if (isset($this->session->data['shipping_method']['cost']) && $this->session->data['shipping_method']['cost'] > 0) {
            $delivery['positionId'] = 'delivery';
            $delivery['name'] = $this->session->data['shipping_method']['title'] ?? '';
            $delivery['itemAmount'] = $this->session->data['shipping_method']['cost'] * 100;
            $delivery['quantity']['value'] = 1;
            $delivery['quantity']['measure'] = $this->method_library->getDefaultMeasurement(); //todo?
            $delivery['itemCode'] = $this->session->data['shipping_method']['code'];
            $delivery['tax']['taxType'] = $this->config->get('payment_sensebank_taxType');
            $delivery['tax']['taxSum'] = 0;
            $delivery['itemPrice'] = $this->session->data['shipping_method']['cost'] * 100;

            $attributes = array();
            $attributes[] = array(
                "name" => "paymentMethod",
                "value" => $this->method_library->getPaymentMethodType(true)
            );
            $attributes[] = array(
                "name" => "paymentObject",
                "value" => 4
            );
            $delivery['itemAttributes']['attributes'] = $attributes;
            $orderBundle['cartItems']['items'][] = $delivery;
        }
        if (isset($this->session->data['vouchers']) && count($this->session->data['vouchers']) > 0) {
            foreach ($this->session->data['vouchers'] as $key => $voucher) {
                $itemVoucher = array(
                    'positionId' => 'voucher_' . $key,
                    'name' => $voucher['description'],
                    'itemAmount' => $voucher['amount'] * 100,
                    'quantity' => array(
                        'value' => 1,
                        'measure' => $this->method_library->getDefaultMeasurement(),
                    ),
                    'itemCode' => 'voucher_' . $key,
                    'tax' => array(
                        'taxType' => $this->config->get('payment_sensebank_taxType'),
                        'taxSum' => 0,
                    ),
                    'itemPrice' => $voucher['amount'] * 100
                );
                $attributes = array();
                $attributes[] = array(
                    "name" => "paymentMethod",
                    "value" => $this->method_library->getPaymentMethodType(),
                );
                $attributes[] = array(
                    "name" => "paymentObject",
                    "value" => 1
                );
                $itemVoucher['itemAttributes']['attributes'] = $attributes;
                $orderBundle['cartItems']['items'][] = $itemVoucher;
            }
        }
        if ($this->method_library->enable_cart_options && $this->method_library->ofd_status) {
            $discount = $this->method_library->discountHelper->discoverDiscount($amount, $orderBundle['cartItems']['items']);
            if ($discount > 0) {
                $this->method_library->discountHelper->setOrderDiscount($discount);
                $recalculatedPositions = $this->method_library->discountHelper->normalizeItems($orderBundle['cartItems']['items']);
                $recalculatedAmount = $this->method_library->discountHelper->getResultAmount();
                $orderBundle['cartItems']['items'] = $recalculatedPositions;
            }
        }
        $orderNumber = $order_number . "_" . time();
        $args = array(
            'orderNumber' => $orderNumber,
            'amount' => $amount,
            'description' => 'Payment for order #' . $order_number,
            'jsonParams' => json_encode($jsonParams_array),
        );
        $args['returnUrl'] = $this->url->link('extension/payment/sensebank/comeback');

        if (!empty($order_info['customer_id'] && $order_info['customer_id'] > 0)) {
            $client_email = !empty($order_info['email']) ? $order_info['email'] : "";
            $args['clientId'] = md5($order_info['customer_id']  .  $client_email  . $order_info['store_url']);
        }
        if ($this->method_library->enable_cart_options && $this->method_library->ofd_status && !empty($orderBundle)) {
            $args['taxSystem'] = $this->method_library->taxSystem;
            $args['orderBundle']['orderCreationDate'] = date('c');
            $args['orderBundle'] = json_encode($orderBundle);
        }

        if (!empty($this->method_library->token)) {
            $decoded_credentials = base64_decode($this->method_library->token);
            list($l, $p) = explode(':', $decoded_credentials);
            $args['userName'] = $l;
            $args['password'] = $p;
        } else {
            $args['userName'] = $this->method_library->login;
            $args['password'] = $this->method_library->password;
        }
        if ($this->method_library->mode == 'test') {
            $action_address = $this->method_library->test_url;
        } else {
            $action_address = $this->method_library->prod_url;
            if (defined('SBPAYMENT_PROD_URL_ALTERNATIVE_DOMAIN') && defined('SBPAYMENT_PROD_URL_ALT_PREFIX')) {
                if (substr($this->method_library->login, 0, strlen(SBPAYMENT_PROD_URL_ALT_PREFIX)) == SBPAYMENT_PROD_URL_ALT_PREFIX) {
                    $pattern = '/^https:\/\/[^\/]+/';
                    $action_address = preg_replace($pattern, rtrim(SBPAYMENT_PROD_URL_ALTERNATIVE_DOMAIN, '/'), $action_address);
                }
            }
        }

        $method = $this->method_library->stage == 'two' ? 'registerPreAuth.do' : 'register.do';
        $request = http_build_query($args, '', '&');
        $response = $this->method_library->_sendGatewayData($request, $action_address . $method);
        if ($this->method_library->logging) {
            $this->method_library->logger($action_address, $method, $request, $response);
        }
        $response = json_decode($response, true);

        if (isset($response['orderId'])) {
            $order_store = array(
                'amount' => $amount,
                'currency' => $this->config->get('payment_sensebank_currency')
            );
            $this->_storeGatewayOrderData($response['orderId'], $order_number, $order_store);
            $comment = "Order created in payment gateway";
            $this->model_checkout_order->addHistory($order_number, $this->config->get('payment_sensebank_order_status_before_id'), $comment, false);
        }
        if (isset($response['errorCode'])) {
            $this->document->setTitle($this->language->get('error_title'));
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['button_continue'] = $this->language->get('error_continue');
            $data['heading_title'] = $this->language->get('error_title') . ' #' . $response['errorCode'];
            $data['text_error'] = $response['errorMessage'];
            $data['continue'] = $this->url->link('checkout/cart');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $this->response->setOutput($this->get_template('error/sensebank', $data));
        } else {
            $this->response->redirect($response['formUrl']);
        }
    }

    /**
     * Init Library
     */
    private function initializeGatewayLibrary()
    {
        $this->library('SensebankLibrary');
        $this->method_library = new \Opencart\System\Library\Extension\Sensebank\SensebankLibrary();

		$this->method_library->token = $this->config->get('payment_sensebank_merchantToken');
        $this->method_library->login = $this->config->get('payment_sensebank_merchantLogin');
        $this->method_library->password = htmlspecialchars_decode($this->config->get('payment_sensebank_merchantPassword'));
        $this->method_library->stage = $this->config->get('payment_sensebank_stage');
        $this->method_library->mode = $this->config->get('payment_sensebank_mode');
        $this->method_library->logging = $this->config->get('payment_sensebank_logging');
        $this->method_library->currency = $this->config->get('payment_sensebank_currency');
        $this->method_library->taxSystem = $this->config->get('payment_sensebank_taxSystem');
        $this->method_library->taxType = $this->config->get('payment_sensebank_taxType');
        $this->method_library->ofd_status = $this->config->get('payment_sensebank_ofd_status');
        $this->method_library->FFDVersion = $this->config->get('payment_sensebank_FFDVersion');
        $this->method_library->paymentMethodType = $this->config->get('payment_sensebank_paymentMethodType');
        $this->method_library->paymentObjectType = $this->config->get('payment_sensebank_paymentObjectType');
        $this->method_library->paymentMethodTypeDelivery = $this->config->get('payment_sensebank_paymentMethodTypeDelivery');

        if (file_exists(DIR_EXTENSION . "sensebank/library/sensebank_cacert.cer") && $this->config->get('payment_sensebank_enable_sensebank_cacert') == true) {
            $this->method_library->enable_sensebank_cacert = $this->config->get('payment_sensebank_enable_sensebank_cacert');
            $this->method_library->sensebank_cacert_path = DIR_EXTENSION . "sensebank/library/sensebank_cacert.cer";
        } else {
            $this->method_library->enable_sensebank_cacert = (float)$this->config->get('payment_sensebank_enable_sensebank_cacert');
        }
        $this->method_library->language = substr($this->language->get('code'), 0, 2);
        $this->method_library->backToShopURL = $this->config->get('payment_sensebank_backToShopURL');
    }

    /**
     * in oc 2.1 no Loader::library()
     * self realization
     * @param $library
     */
    private function library($library)
    {
        $file = DIR_EXTENSION . 'sensebank/system/library/' . str_replace('../', '', (string)$library) . '.php';
        if (file_exists($file)) {
            include_once($file);
        } else {
            trigger_error('Error: Could not load library ' . $file . '!');
            exit();
        }
    }

    /*public function callback()
    {
        if (isset($this->request->get['mdOrder'])) {
            $order_id = $this->request->get['mdOrder'];
        } else {
            die('Illegal Access.');
        }
        $this->initializeGatewayLibrary();
        $response = $this->method_library->_getGatewayOrderStatus($order_id);
        $response = json_decode($response, true);
        $ex = explode("_", $response['orderNumber']);
        $order_number = $ex[0];
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_number);
        if ($order_info) {
            if (($response['errorCode'] == 0) && (($response['orderStatus'] == 1) || ($response['orderStatus'] == 2))) {
                $this->_storeGatewayOrderData($order_id, $order_number, $response);
                $comment = "Incoming callback";
                $this->model_checkout_order->addOrderHistory($order_number, $this->config->get('payment_sensebank_order_status_completed_id'), $comment, false);
                $this->response->redirect($this->url->link('checkout/success', '', true));
            }
            elseif ($response['errorCode'] == 0 && $response['orderStatus'] == 6) {
                $comment = "Incoming callback declinedByTimeOut";
                $this->model_checkout_order->addOrderHistory($order_number, 14, $comment, false); //14 system status CMS
            }
            else {
                $this->response->redirect($this->url->link('checkout/failure', '', true));
            }
        }
    }*/

    public function checkstatuses ()
    {
        // TODO for cron job
    }

    public function comeback()
    {
        if (isset($this->request->get['orderId'])) {
            $orderId = $this->request->get['orderId'];
        } else {
            die('Illegal Access');
        }

        $this->initializeGatewayLibrary();
        $response = $this->method_library->_getGatewayOrderStatus($orderId);
        $response = json_decode($response, true);
        $order = $this->_getGatewayOrderByReference($orderId);
        $order_id = $order['order_id'];
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        if ($order_info) {
            if (($response['errorCode'] == 0) && (($response['orderStatus'] == 1) || ($response['orderStatus'] == 2))) {
                //if ($this->method_library->allowCallbacks == false) {
                $sql_data = array(
                    'status_deposited' => 1
                );
                $this->_updateGatewayOrder($order_id, $sql_data);
                //$this->_storeGatewayOrderData($orderId, $order_id, $response);
                $comment = "Payment Deposited";
                $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_sensebank_order_status_completed_id'), $comment, false);
                //}
                $this->response->redirect($this->url->link('checkout/success', '', true));
            } else {
                //print_r($response);
                $this->response->redirect($this->url->link('checkout/failure', '', true));
            }
        }
    }

    public function _storeGatewayOrderData($orderId, $order_id, $response)
    {
        $this->load->model('extension/sensebank/payment/sensebank');
        $data = array(
            'order_id' => (int)$order_id,
            'gateway_order_reference' => $orderId,
            'currency' => $response['currency'],
            'order_amount' => $response['amount'],
            'order_amount_deposited' => $response['amount'],
            'status_created' => 1, //todo
        );
        $this->model_extension_sensebank_payment_sensebank->storeGatewayOrder($data);
    }

    public function _getGatewayOrderByReference($orderId)
    {
        $this->load->model('extension/sensebank/payment/sensebank');
        return $this->model_extension_sensebank_payment_sensebank->getGatewayOrderByReference($orderId);
    }

    public function _updateGatewayOrder($order_id, $data)
    {
        $this->load->model('extension/sensebank/payment/sensebank');
        $this->model_extension_sensebank_payment_sensebank->updateGatewayOrder($order_id, $data);
    }
}
