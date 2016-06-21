<?php
/**
 * Default Mony payments helper class for card
 *
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */

class Mony_Monypayments_Helper_Card extends Mage_Core_Helper_Abstract
{
    /**
     * Constant variable from API
     */
    const API_CARD_TOKEN = 'token';
    const API_CARD_TYPE = 'brand';
    const API_CARD_NUMBER = 'truncatedNumber';
    const API_CARD_EXP_MONTH = 'expiryMonth';
    const API_CARD_EXP_YEAR = 'expiryYear';

    /**
     * Get Card Type
     *
     * @param bool $response
     * @return bool|string
     */
    public function getCardType($response = false)
    {
        if ($response) {
            switch ($response->{self::API_CARD_TYPE}) {
                default:
                    $response = strtoupper($response->{self::API_CARD_TYPE});
            }
        }

        return $response;
    }

    /**
     * Get card number from response API
     *
     * @param bool $response
     * @return bool|string
     */
    public function getCardNumber($response = false)
    {
        if ($response) {
            $response = '**' . $response->{self::API_CARD_NUMBER};
        }

        return $response;
    }

    /**
     * Get Expiry date from response API
     *
     * @param bool $response
     * @return bool|string
     */
    public function getExpiryDate($response = false)
    {
        if ($response) {
            $response = sprintf("%02d", $response->{self::API_CARD_EXP_MONTH}) . '/' . $response->{self::API_CARD_EXP_YEAR};
        }

        return $response;
    }

    /**
     * Get token from API
     *
     * @param bool $card
     * @return bool|mixed
     */
    public function getToken($card = false)
    {
        if ($card) {
            $card = $card->{self::API_CARD_TOKEN};
        }
        return $card;
    }

    /**
     * Calculate on can save cards
     *
     * @param null $quote
     * @return Mony_Monypayments_Helper_Data
     */
    public function canSaveCards($quote = null)
    {
        // get the configuration from Admin
        $config = Mage::helper('monypayments')->isSaveCardEnabled();

        if ($config) {
            $customer = Mage::getSingleton('customer/session');
            $method = ($quote) ? $quote->getCheckoutMethod() : '';
            $request = Mage::app()->getRequest();

            // if customer login or method register
            if ($customer->isLoggedIn() || $method == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER) {
                return true;
            }

            // if not using Magento standard checkout, it will hide it
            if ($request->getRouteName() !== 'checkout' && $request->getControllerName() !== 'onepage') {
                return true;
            }
        }

        return false;
    }

    /**
     * Set Saved card to be hidden in certain checkout
     *
     * @return bool
     */
    public function isSaveCardsHidden()
    {
        $default_magento_checkout = false;
        
        $request = Mage::app()->getRequest();
        $customer = Mage::getSingleton('customer/session');

        $saveCard = $this->canSaveCards(Mage::getSingleton('checkout/session')->getQuote());
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        if ($saveCard) {
            if ($request->getRouteName() == 'checkout' && $request->getControllerName() == 'onepage') {
                $default_magento_checkout = true;
                // return true;
            }

            if( !$customer->isLoggedIn() && $default_magento_checkout && $quote->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER ) {
                return false;
            }
            else if( !$customer->isLoggedIn() ) {
                return true;
            }
        }

        return false;
    }
}