<?php
namespace Opencart\Catalog\Controller\Extension\Sensebank\Common;

define('SBPAYMENT_PAYMENT_NAME', 'Sense Bank');

define('SBPAYMENT_PROD_URL' , 'https://pay.sensebank.com.ua/payment/rest/');
define('SBPAYMENT_TEST_URL' , 'https://sand.sensebank.com.ua/payment/rest/');

define('SBPAYMENT_ENABLE_LOGGING', true);
define('SBPAYMENT_ENABLE_CART_OPTIONS', false);
define('SBPAYMENT_MEASUREMENT_NAME', 'шт');
define('SBPAYMENT_MEASUREMENT_CODE', 0);
define('SBPAYMENT_ENABLE_CALLBACK', true);

//this close tag must be here -> ?>
<?php

class SensebankLibrary extends \Opencart\System\Engine\Controller
{
    public $prod_url;
    public $test_url;
    public $alternative_domain = null;
    public $enable_cart_options = SBPAYMENT_ENABLE_CART_OPTIONS;
    public $enable_refund_options;
    public $logging = SBPAYMENT_ENABLE_LOGGING;
    public $language = 'en';
    public $version = '1.0.0';
    public $token;
    public $login;
    public $password;
    public $mode;
    public $stage;
    public $currency;
    public $ofd_status;
    public $FFDVersion;
    public $paymentMethodType;
    public $paymentObjectType;
    public $paymentMethodTypeDelivery;
    public $taxSystem;
    public $taxType;
    public $discountHelper;
    public $allowCallbacks = SBPAYMENT_ENABLE_CALLBACK;
    public $enable_sensebank_cacert = true;
    public $sensebank_cacert_path = null;
    public $enable_back_url_settings = false;
    public $api_version = 1;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->test_url = SBPAYMENT_TEST_URL;
        $this->prod_url = SBPAYMENT_PROD_URL;
        if (defined('SBPAYMENT_API_VERSION')) {
            $this->api_version = SBPAYMENT_API_VERSION;
        }
        if (defined('SBPAYMENT_PROD_URL_ALTERNATIVE_DOMAIN')) {
            $this->alternative_domain = SBPAYMENT_PROD_URL_ALTERNATIVE_DOMAIN;
        }
        if (defined('SBPAYMENT_PROD_URL_ALTERNATIVE_DOMAIN') && defined('SBPAYMENT_PROD_URL_ALT_PREFIX')) {
            $this->allowCallbacks = false;
        }
        if ($this->enable_cart_options === true){
            //$this->library('sensebank_discount');
            //$this->discountHelper = new SensebankDiscount();
			$this->discountHelper = $this->load->controller('extension/sensebank/common/sensebank_discount');
        }
        if (file_exists(DIR_EXTENSION . "sensebank/library/sensesank_cacert.cer")) {
            $this->enable_sensebank_cacert = true;
            $this->sensebank_cacert_path = DIR_EXTENSION . "sensebank/library/sensebank_cacert.cer";
        } else {
            $this->enable_sensebank_cacert = false;
        }
        if (defined('SBPAYMENT_ENABLE_REFUND_TAB') && SBPAYMENT_ENABLE_REFUND_TAB === true) {
            $this->enable_refund_options = true;
        } else {
            $this->enable_refund_options = false;
        }
        if (defined('SBPAYMENT_ENABLE_BACK_URL_SETTINGS') && SBPAYMENT_ENABLE_BACK_URL_SETTINGS === true) {
            $this->enable_back_url_settings = true;
        }
    }

    /**
     * @return string
     */
    public function getTestUrl()
    {
        return $this->test_url;
    }

    /**
     * @return string
     */
    public function getProdUrl()
    {
        return $this->prod_url;
    }

    /**
     * Get Payment GW url based on test mode status.
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->mode == 'test') {
            return $this->test_url;
        } else {
            return $this->prod_url;
        }
    }

    /**
     * @return string
     */
    public function getAlternativeDomain()
    {
        return $this->alternative_domain;
    }

    /**
     * @return mixed
     */
    public function getFFDVersion()
    {
        return $this->FFDVersion;
    }

    /**
     * @param $delivery
     * @return mixed
     */
    public function getPaymentMethodType($delivery = false)
    {
        if ($delivery) {
            return $this->paymentMethodTypeDelivery;
        }
        return $this->paymentMethodType;
    }

    /**
     * @return mixed
     */
    public function getPaymentObjectType()
    {
        return $this->paymentObjectType;
    }

    /**
     * @return string
     */
    public function getDefaultMeasurement()
    {
        if ($this->FFDVersion == "v1_05") {
            return SBPAYMENT_MEASUREMENT_NAME;
        }
        return SBPAYMENT_MEASUREMENT_CODE;
    }

    /**
     * @param $property
     * @param $value
     * @return $this
     */
    /*public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
        return $this;
    }*/

    /**
     * @param $data
     * @param $action_address
     * @param array $headers
     * @return string
     */
    public function _sendGatewayData($data, $action_address, $headers = array())
    {
        $curl_opt = array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_URL => $action_address,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HEADER => true,
        );
        $ssl_verify_peer = false;
        if ($this->sensebank_cacert_path != null && $this->enable_sensebank_cacert == true) {
            $ssl_verify_peer = true;
            $curl_opt[CURLOPT_CAINFO] = $this->sensebank_cacert_path;
        }
        $curl_opt[CURLOPT_SSL_VERIFYPEER] = $ssl_verify_peer;
        $ch = curl_init();
        curl_setopt_array($ch, $curl_opt);
        $response = curl_exec($ch);
        if ($response === false) {
            $error = array('errorCode' => 999, "errorMessage" => curl_error($ch));
            return json_encode($error);
        }
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        return substr($response, $header_size);
    }

    /**
     * @param string $url
     * @param string $method
     * @param mixed[] $request
     * @param mixed[] $response
     * @return integer
     */
    public function logger($url, $method, $request, $response)
    {
        $this->library('log');
        $file_name = "oc3x_sensebank_" . date("y-m") . ".log";
        $logger = new Log($file_name);
        $logger->write("Sensebank: ".$url.$method."\nREQUEST: ".json_encode($request). "\nRESPONSE: ".$response."\n\n");
    }

    /**
     *
     * @param string $orderId
     * @return mixed[]
     */
    public function _getGatewayOrderStatus($orderId)
    {
        $action_address = $this->getUrl() . "getOrderStatusExtended.do";

        if (!empty($this->token)) {
            $decoded_credentials = base64_decode($this->token);
            list($l, $p) = explode(':', $decoded_credentials);
            $data['userName'] = $l;
            $data['password'] = $p;
        } else {
            $data['userName'] = $this->login;
            $data['password'] = $this->password;
        }
        $data['orderId'] = $orderId;
        return $this->_sendGatewayData($data, $action_address);
    }

    /**
     * in oc 2.1 no Loader::library()
     * self realization
     * @param $library
     */
    public function library($library)
    {
        $file = DIR_EXTENSION . 'sensebank/admin/controller/common/' . str_replace('../', '', (string)$library) . '.php';
        if (file_exists($file)) {
            include_once($file);
        } else {
            trigger_error('Error: Could not load library ' . $file . '!');
            exit();
        }
    }
}
