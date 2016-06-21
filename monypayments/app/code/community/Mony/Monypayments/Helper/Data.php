<?php

/**
 * Default Mony payments helper class
 *
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
class Mony_Monypayments_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Constant variable from config
     */
    const CONFIG_PATH_MONYPAYMENTS_PAYMENT_REVIEW_STATUS     = 'payment/monypayments/payment_review_status';
    const CONFIG_PATH_MONYPAYMENTS_PAYMENT_REVIEW_EMAIL_SEND = 'payment/monypayments/payment_review_email_send';
    const CONFIG_PATH_MONYPAYMENTS_ENABLED                   = 'payment/monypayments/active';
    const CONFIG_PATH_MONYPAYMENTS_DEBUG                     = 'payment/monypayments/debug';
    const CONFIG_PATH_MONYPAYMENTS_TITLE                     = 'payment/monypayments/title';
    const CONFIG_PATH_MONYPAYMENTS_API_USERNAME              = 'payment/monypayments/api_username';
    const CONFIG_PATH_MONYPAYMENTS_API_PASSWORD              = 'payment/monypayments/api_password';
    const CONFIG_PATH_MONYPAYMENTS_API_KEY                   = 'payment/monypayments/api_key';
    const CONFIG_PATH_MONYPAYMENTS_SUPPORTED_CC_TYPES        = 'payment/monypayments/supported_cctypes';
    const CONFIG_PATH_MONYPAYMENTS_CC_TYPES                  = 'payment/monypayments/cctypes';
    const CONFIG_PATH_MONYPAYMENTS_ENABLE_SAVE_CARD          = 'payment/monypayments/enable_saved_card';

    /**
     * @var string
     */
    protected $logFileName = 'monypayments.log';

    /**
     * @var bool
     */
    protected $isDebugEnabled;

    /**
     * General logging method
     *
     * @param      $message
     * @param null $level
     */
    public function log($message, $level = null)
    {
        if ($this->isDebugMode() || $level != Zend_Log::DEBUG) {
            Mage::log($message, $level, $this->logFileName);
        }
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        if ($this->isDebugEnabled === null) {
            $this->isDebugEnabled = Mage::getStoreConfigFlag(self::CONFIG_PATH_MONYPAYMENTS_DEBUG);
        }

        return $this->isDebugEnabled;
    }

    /**
     * Get the current version of the Mony payments extension
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return (string) Mage::getConfig()->getModuleConfig('Mony_Monypayments')->version;
    }

    /**
     * Checking if Monypayments is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::CONFIG_PATH_MONYPAYMENTS_ENABLED, Mage::app()->getStore());
    }

    /**
     * Returning payment review status selected from Admin
     *
     * @return mixed
     */
    public function getPaymentReviewStatus()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_MONYPAYMENTS_PAYMENT_REVIEW_STATUS, Mage::app()->getStore());
    }

    /**
     * Checking if Payment Review order still sending
     *
     * @return mixed
     */
    public function isPaymentReviewOrderSend()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_MONYPAYMENTS_PAYMENT_REVIEW_EMAIL_SEND, Mage::app()->getStore());
    }

    /**
     * returning API username based on the config
     *
     * @return mixed
     */
    public function getMonyApiUsername()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_MONYPAYMENTS_API_USERNAME, Mage::app()->getStore());
    }

    /**
     * returning API password
     *
     * @return mixed
     */
    public function getMonyApiPassword()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_MONYPAYMENTS_API_PASSWORD, Mage::app()->getStore());
    }

    /**
     * returning API key
     *
     * @return mixed
     */
    public function getMonyApiKey()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_MONYPAYMENTS_API_KEY, Mage::app()->getStore());
    }

    /**
     * Return the cc type supported for Monypayments
     *
     * @return array
     */
    public function getSupportedCctypes()
    {
        return explode(',', Mage::getStoreConfig(self::CONFIG_PATH_MONYPAYMENTS_SUPPORTED_CC_TYPES, Mage::app()->getStore()));
    }

    /**
     * Return the cctype for Monypayments
     *
     * @return array
     */
    public function getAvailableCcTypes()
    {
        return explode(',', Mage::getStoreConfig(self::CONFIG_PATH_MONYPAYMENTS_CC_TYPES, Mage::app()->getStore()));
    }

    /**
     * Return the save card enabled
     *
     * @return mixed
     */
    public function isSaveCardEnabled()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_MONYPAYMENTS_ENABLE_SAVE_CARD, Mage::app()->getStore());
    }

}
