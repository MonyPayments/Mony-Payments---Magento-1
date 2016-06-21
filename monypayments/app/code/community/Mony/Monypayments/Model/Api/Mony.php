<?php
/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
class Mony_Monypayments_Model_Api_Mony extends Varien_Object
{
    /**
     * @var string
     */
    protected $_userAgent;

    /**
     * Get useragent on construct and set as variable
     *
     * Mony_Monypayments_Model_Api_Mony constructor.
     */
    public function __construct()
    {
        $this->_userAgent = 'MonypaymentsMagentoPlugin/' . $this->_helper()->getModuleVersion() . ' (Magento ' . Mage::getEdition() . ' ' . Mage::getVersion() . ')';
        return $this;
    }

    /**
     * @return Mony_Monypayments_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('monypayments');
    }

    /**
     * Send request to API using HTTP client (php-curl is required)
     *
     * @param $url
     * @param bool $body
     * @param string $method
     * @return Zend_Http_Response
     * @throws Zend_Http_Client_Exception
     */
    public function _sendRequest($url, $body = false, $method = Varien_Http_Client::GET)
    {
        /**
         * Use Zend HTTP Instead of Varien HTTP (Varien_Http_Client)
         * Due to Varien not allowing to use PUT and DELETE method
         */
        $client = new Zend_Http_Client($url);
        $coreHelper = Mage::helper('core');

        $client->setAuth(
            trim($this->_helper()->getMonyApiUsername()),
            trim($this->_helper()->getMonyApiPassword())
        );

        $client->setConfig(
            array('useragent' => $this->_userAgent)
        );

        if ($body !== false) {
            $client->setRawData($coreHelper->jsonEncode($body), 'application/json');
        }

        try {
            // Get the response
            $response = $client->request($method);

            // Preparing response before send to browser
            $statusCode = $response->getStatus();
            $response = json_decode($response->getBody());

            // Handle to set the response if return 204 (no content)
            if (!$response) {
                $response = new stdClass();
            }

            // added the status code to response
            $response->statusCode = $statusCode;
        } catch (Exception $e) {
            $response = (object) array(
                'error' => true,
                'message' => $e->getMessage(),
            );
        }

        return $response;
    }
}