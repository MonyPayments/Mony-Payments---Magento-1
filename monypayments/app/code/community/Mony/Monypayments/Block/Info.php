<?php
/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
class Mony_Monypayments_Block_Info extends Mage_Payment_Block_Info
{
    /**
     * Prepare information specific to current payment method
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $transport = parent::_prepareSpecificInformation($transport);
        $helper    = Mage::helper('monypayments');

        // check if current store is Admin Store
        if (Mage::app()->getStore()->getStoreId() == 0) {

            /** @var Mage_Sales_Model_Order_Payment $info */
            // preparing data to be shown
            $info = $this->getInfo();
            $orderId = $info->getMonypaymentsOrderId();

            // Added data to transport so Magento will loop the transport
            $transport->addData(array($helper->__('Order ID') => $orderId ? $orderId: $helper->__('(none)')));
        }

        return $transport;
    }
}