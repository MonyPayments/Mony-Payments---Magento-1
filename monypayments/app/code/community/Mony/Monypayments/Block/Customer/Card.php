<?php
/**
 * Mony Monypayments payment
 *
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */

class Mony_Monypayments_Block_Customer_Card extends Mage_Core_Block_Template
{
    /**
     * Prevent caching the block
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        return null;
    }

    /**
     * @return Mony_Monypayments_Helper_Card
     */
    protected function _cardHelper()
    {
        return Mage::helper('monypayments/card');
    }

    /**
     * Get Saved Card from API
     *
     * @return Mony_Monypayments_Model_Customer
     */
    public function getSavedCards()
    {
        if ($customerId = Mage::getSingleton('customer/session')->getId()) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            return Mage::getModel('monypayments/customer')->getSavedCards($customer);
        }
    }

    /**
     * Get deleted url for card
     *
     * @param bool $card
     * @return bool|string
     */
    public function getDeleteUrl($card = false)
    {
        if ($card) {
            $card = $this->getUrl('monypayments/card/delete', array('token' => $this->_cardHelper()->getToken($card)));
        }

        return $card;
    }
}