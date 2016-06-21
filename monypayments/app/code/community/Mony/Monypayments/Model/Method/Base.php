<?php
/**
 * Abstract base class for Mony payments payment method models
 *
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */

/**
 * Class Mony_Monypayments_Model_Method_Base
 */
abstract class Mony_Monypayments_Model_Method_Base extends Mage_Payment_Model_Method_Abstract
{
    /* Configuration fields */
    const API_MODE_CONFIG_FIELD = 'api_mode';

    const API_URL_CONFIG_PATH_PATTERN = 'monypayments/api/{prefix}_api_url';
    const WEB_URL_CONFIG_PATH_PATTERN = 'monypayments/api/{prefix}_web_url';
    const PAYMENT_SCRIPT_PATH_PATTERN = 'monypayments/api/{prefix}_payment_script_path';
    const RESOURCE_BASE               = 'monypayments/api/resource_base';

    /* Order payment statuses */
    const RESPONSE_STATUS_APPROVED = 'APPROVED';
    const RESPONSE_STATUS_PENDING  = 'PENDING';
    const RESPONSE_STATUS_FAILED   = 'FAILED';
    const RESPONSE_STATUS_DECLINED = 'DECLINED';

    /* Customer method code */
    const CUSTOMER_METHOD_GET    = 'get';
    const CUSTOMER_METHOD_SEARCH = 'list';
    const CUSTOMER_METHOD_DELETE = 'delete';

    const CUSTOMER_DELETE_STATUS_OK = 204;

    const CARD_DECLINED_MESSAGE = "CARD_DECLINED";

    /**
     * Payment Method features common for all payment methods
     *
     * @var bool
     */
    protected $_isGateway                  = true;
    protected $_canAuthorize               = true;
    protected $_canCapture                 = true;
    protected $_canRefund                  = true;
    protected $_canRefundInvoicePartial    = true;
    protected $_canUseInternal             = false;
    protected $_canUseCheckout             = true;
    protected $_canUseForMultishipping     = false;
    protected $_canReviewPayment           = false;
    protected $_canFetchTransactionInfo    = true;
    protected $_canSaveCc                  = false;
    protected $_canManageRecurringProfiles = false;

    /**
     * Custom protected variable for Mony
     */
    protected $_transactionData;

    /**
     * Get configured gateway URL for payment method
     *
     * @return string|null
     */
    public function getOrdersApiUrl()
    {
        $apiMode      = $this->getConfigData(self::API_MODE_CONFIG_FIELD);
        $settings     = Mony_Monypayments_Model_System_Config_Source_ApiMode::getEnvironmentSettings($apiMode);

        return $settings[Mony_Monypayments_Model_System_Config_Source_ApiMode::KEY_API_URL] . 'orders/';
    }
    
    /**
     * Get configured gateway URL for payment method
     *
     * @return string
     */
    public function getRefundUrl($id)
    {
        $apiMode      = $this->getConfigData(self::API_MODE_CONFIG_FIELD);
        $settings     = Mony_Monypayments_Model_System_Config_Source_ApiMode::getEnvironmentSettings($apiMode);

        return $settings[Mony_Monypayments_Model_System_Config_Source_ApiMode::KEY_API_URL] . 'orders/' . $id . '/refunds';
    }

    /**
     * Get configured gateway URL for customer with query
     *
     * @param null $query
     * @param bool $type
     * @return string
     */
    public function getCustomerUrl($query = null, $type = false)
    {
        $apiMode      = $this->getConfigData(self::API_MODE_CONFIG_FIELD);
        $settings     = Mony_Monypayments_Model_System_Config_Source_ApiMode::getEnvironmentSettings($apiMode);

        $url = $settings[Mony_Monypayments_Model_System_Config_Source_ApiMode::KEY_API_URL] . 'customers';

        /**
         * Type of customer REST URL
         */
        switch ($type) {
            case self::CUSTOMER_METHOD_GET:
                $url .= '/' . $query['id'];
                break;
            case self::CUSTOMER_METHOD_SEARCH:
                $i = 0;
                foreach ($query as $attribute => $value) {
                    if ($i < 1) {
                        $url .= '?';
                    } else {
                        $url .= '&';
                    }
                    $url .= $attribute . '=' . $value;
                };
                break;
            case self::CUSTOMER_METHOD_DELETE:
                $url .= '/' . $query['id'] . '/payment-method/' . $query['payment-method'];
                break;
            default:
                break;

        }

        return $url;
    }

    /**
     * @return Mony_Monypayments_Helper_Data
     */
    protected function helper()
    {
        return Mage::helper('monypayments');
    }

    /**
     * @return Mony_Monypayments_Model_Api_Adapter
     */
    protected function getApiAdapter()
    {
        return Mage::getModel('monypayments/api_adapter');
    }

    /**
     * Authorize the order
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @throws Mage_Payment_Model_Info_Exception
     *
     * @return object
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        parent::authorize(); // run parent as per magento configuration
        $this->_authorize($payment, $amount, false);
    }

    /**
     * Authorize function to prepare data and check on available data
     *
     * @param Varien_Object $payment
     * @param $amount
     *
     * @return $this
     *
     * @throws Mage_Payment_Model_Info_Exception
     */
    protected function _authorize(Varien_Object $payment, $amount)
    {
        // Check if Token is empty
        if (!$payment->getMonypaymentsToken()) {
            $this->helper()->log('Capture Error: Mony Cart Token cannot be empty');
            Mage::throwException(
                Mage::helper('monypayments')->__('There was an error capturing the transaction.')
            );
        }

        try {
            $order = $payment->getOrder();
            // Check on customer and created if not exist and saved
            $monyCustomerId = false;
            if ($order->getCustomerId()) {
                $monyCustomerId = $this->_registerCustomer($payment);
            }

            // preparing data through Adapter API
            $order = $payment->getOrder();

            // Charge amount
            $chargeAmount = $this->getApiAdapter()->getChargeAmount($order, $amount);
            if ($chargeAmount) {
                $this->_transactionData['chargeAmount'] = $chargeAmount;
            }

            // Payment Methods
            $paymentMethod = $this->getApiAdapter()->getPaymentMethod($payment);
            if ($paymentMethod) {
                $this->_transactionData['paymentMethod'] = $paymentMethod;
            }

            // Customer details
            $details = $this->getApiAdapter()->getCustomerInfo($payment, $monyCustomerId);
            foreach ($details as $type => $info) {
                $this->_transactionData[$type] = $info;
            }

            // Merchant Reference
            $mechantReference = $this->getApiAdapter()->getMerchantReference($order);
            if ($mechantReference) {
                $this->_transactionData['merchantReference'] = $mechantReference;
            }

            // Order Details
            $orderDetail = $this->getApiAdapter()->getOrderDetail($order);
            if ($orderDetail) {
                $this->_transactionData['orderDetail'] = $orderDetail;
            }

            // Add Request to logs if debug mode on
            $this->helper()->log(array('Order Request' => $this->_transactionData));

        } catch (Exception $e) {
            // Add Request to logs if debug mode on
            $this->helper()->log('Order Request Error: ' . $e->getMessage());

            // Throw error to Magento Checkout
            throw new Mage_Payment_Model_Info_Exception($e->getMessage());
        }
        return $this;
    }

    /**
     * Capture payment for order to Mony API
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return $this
     *
     * @throws Mage_Core_Exception
     * @throws Mage_Payment_Model_Info_Exception
     */
    function capture(Varien_Object $payment, $amount)
    {
        // Do Authorize
        if (!$this->_transactionData) {
            $this->_authorize($payment, $amount);
        }

        // Actual capture the payment to API
        try {
            // call API to capture the payment
            $response = $this->getApiAdapter()->_sendRequest(
                $this->getOrdersApiUrl(),
                $this->_transactionData,
                Varien_Http_Client::POST
            );

            // Add response to the log if debug mode on
            $this->helper()->log(array('Order Response' => $response));

            if (isset($response->status)) {
                switch ($response->status) {
                    case self::RESPONSE_STATUS_APPROVED:
                        $payment->setTransactionId($response->id)
                            ->setMonypaymentsOrderId($response->id);
                        break;
                    case self::RESPONSE_STATUS_PENDING:
                        $payment->setTransactionId($response->id)
                            ->setMonypaymentsOrderId($response->id)
                            ->setIsTransactionPending(true);
                        break;
                    default: // Any response that is not approved and pending
                        Mage::throwException($response->statusReason);
                        break;
                }
            } else { // Any response that doesn't have status on response
                Mage::throwException('Unable to get the status response from API');
            }
        } catch (Exception $e) {
            // Add response to the log if debug mode on
            $this->helper()->log('Order Response Error: ' . $e->getMessage());

            if( $e->getMessage() == self::CARD_DECLINED_MESSAGE ) {
                // Throw error to Magento Checkout
                Mage::throwException(
                    Mage::helper('monypayments')->__('Transaction has failed. Please use another card.')
                );
            }
            // Throw error to Magento Checkout
            Mage::throwException(
                Mage::helper('monypayments')->__('There was an error capturing the transaction. Please try again.')
            );
        }
        return $this;
    }

    /**
     * Refund function to handle refund online
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return $this
     *
     * @throws Mage_Core_Exception
     */
    public function refund(Varien_Object $payment, $amount)
    {
        // run parent function first
        parent::refund($payment, $amount);

        try {
            $response = $this->getApiAdapter()->_sendRequest(
                $this->getRefundUrl($payment->getMonypaymentsOrderId()),
                $this->_refund($payment, $amount),
                Varien_Http_Client::POST
            );

            // Add response to the log if debug mode on
            $this->helper()->log(array('Refund Response' => $response));

            if (isset($response->errorCode)) {
                // Add response to the log if debug mode on
                $this->helper()->log($response->message);

                // Show an error message in Magento Admin
                Mage::throwException(
                    Mage::helper('monypayments')->__('There was an error refunding the transaction.') . ' '
                    . $response->message
                );
            } else {
                // Set data on Additional Information for later use on CreditMemo creation
                $payment->setAdditionalInformation(
                    array(
                        'refund_id' => $response->id,
                        'refund_date' => $response->createdDate,
                        'refund_amount' => $amount,
                    )
                );
            }

        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('monypayments')->__('There was an error refunding the transaction.') . ' '
                . $e->getMessage()
            );
        }
        return $this;
    }

    /**
     * prepare refund data
     *
     * @param $payment
     * @param $amount
     * @return array
     */
    protected function _refund($payment, $amount)
    {
        $order = $payment->getOrder();
        $currency = $order->getOrderCurrencyCode();

        $requestData = array(
            'amount' => array(
                'amount' => $amount,
                'currency' => $currency,
            ),
            'merchantReference' => 'order-' . $order->getIncrementId() . '-amount-' . $amount,
        );

        // Add Request to logs if debug mode on
        $this->helper()->log(array('Refund Request' => $requestData));

        return $requestData;
    }

    /**
     * Fetch Transaction info
     *
     * @param Mage_Payment_Model_Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(Mage_Payment_Model_Info $payment, $transactionId)
    {
        $order = $payment->getOrder();
        if($response = Mage::getModel('monypayments/order')->updatePendingOrder($order, $transactionId)) {
            switch ($response) {
                case Mony_Monypayments_Model_Method_Base::STATUS_APPROVED:
                    $payment->setIsTransactionApproved(true);
                    break;

                case Mony_Monypayments_Model_Method_Base::STATUS_DECLINED:
                    $payment->setIsTransactionDenied(true);
                    break;
            }
        }
    }

    /**
     * Register customer
     *
     * @param Varien_Object $payment
     * @return mixed
     */
    protected function _registerCustomer(Varien_Object $payment)
    {
        $monyCustomerId = null;
        $order = $payment->getOrder();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        // If customer not linked to Mony Customer and want save
        if (!$customer->getMonyCustomerId() && $payment->getMonypaymentsSaveCard()) {

            // Customer find and found on API
            if ($customerFound = $this->_findCustomer(array('email' => $customer->getEmail()))) {
                $monyCustomer = array_shift($customerFound);
                $monyCustomerId = $monyCustomer->id;
            }
            // Customer not found on API
            else {
                $customerData['email'] = $customer->getEmail();

                /**
                 * run to get optionals data
                 */
                // Firstname
                if ($firstname =  $customer->getFirstname()) {
                    $customerData['givenNames'] = $firstname;
                }

                // Lastname
                if ($lastname = $customer->getLastname()) {
                    $customerData['surname'] = $lastname;
                }

                // Telephone
                if ($phoneNumber = $customer->getTelephone()) {
                    $customerData['phoneNumber'] = $phoneNumber;
                }

                // Adding lo log
                $this->helper()->log(array('Customer Register Request' => $customerData));

                // Create customer in Mony API
                $response = $this->getApiAdapter()->_sendRequest(
                    $this->getCustomerUrl(),
                    $customerData,
                    Varien_Http_Client::POST
                );

                // Adding lo log
                $this->helper()->log(array('Customer Register Response' => $response));

                // If success
                if (isset($response->id)) {
                    $monyCustomerId = $response->id;
                } else { // Not success
                    Mage::throwException('There was an issue when creating customer on Mony payments.');
                }
            }

            /**
             * Linked Magento customer to Mony payments ID
             *
             * NOTE: Even it happen saving customer in Magento, this is belong to one transaction with Order.
             * So the customer will successfully updated when order fully success created
             */
            $customer->setMonyCustomerId($monyCustomerId)->save();
        }

        // Return with Mony customer ID
        return $customer->getMonyCustomerId();
    }

    /**
     * Find customer through API
     *
     * @param array $search
     * @return bool | array
     */
    protected function _findCustomer(array $search)
    {
        // Calculate URL
        $url = $this->getCustomerUrl($search, self::CUSTOMER_METHOD_SEARCH);

        // Adding to log
        $this->helper()->log(array('Customer Search Request' => $url));

        // Call API to find customer
        $response = $this->getApiAdapter()->_sendRequest($url);

        // Adding to log
        $this->helper()->log(array('Customer Search Response' => $response));

        // If error occur
        if (!isset($response->results)) {
            return false;
        }
        // return array from response
        return $response->results;
    }

    /**
     * Get Customer data as well as their payment methods.
     *
     * @param bool $id
     * @return bool|mixed|Zend_Http_Response
     */
    protected function _getCustomer($id = false)
    {
        // Check if ID is being pass
        if ($id) {
            $url = $this->getCustomerUrl(array('id' => $id), self::CUSTOMER_METHOD_GET);

            // Adding to log
            $this->helper()->log(array('Customer Get Request' => $url));

            // Call API to get mony customer information
            $response = $this->getApiAdapter()->_sendRequest($url);

            // Adding to log
            $this->helper()->log(array('Customer Get Response' => $response));

            // Check if the response is correct and return it
            if (isset($response->error)) {
                return false;
            }
        }

        return $response;

    }

    /**
     * Deleting credit card for customer
     *
     * @param $monyId
     * @param $token
     * @return bool
     */
    protected function _deleteCard($monyId, $token)
    {
        // Calculate URL
        $delete = array(
            'id'             => $monyId,
            'payment-method' => $token,
        );
        $url = $this->getCustomerUrl($delete, self::CUSTOMER_METHOD_DELETE);

        // Adding to log
        $this->helper()->log(array('Customer Delete Request' => $url));

        // Call API to find customer
        $response = $this->getApiAdapter()->_sendRequest($url, false, Varien_Http_Client::DELETE);

        // Adding to log
        $this->helper()->log(array('Customer Delete Response' => $response));

        if (isset($response->statusCode) && $response->statusCode == self::CUSTOMER_DELETE_STATUS_OK) {
            return true;
        }
        return false;
    }
}
