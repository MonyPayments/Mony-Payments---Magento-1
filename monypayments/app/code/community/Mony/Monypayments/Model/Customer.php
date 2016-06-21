<?php
/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
class Mony_Monypayments_Model_Customer extends Mony_Monypayments_Model_Method_Card
{
    /**
     * Get mony customer ID
     *
     * @param $customer
     * @return Mage_Customer_Model_Customer|Magestore_Onestepcheckout_Model_Customer_Customer
     */
    protected function _getMonyCustomerId($customer)
    {
        $monyId = $customer->getMonyCustomerId();
        if (!$monyId) {
            switch (get_class($customer)) {
                case 'Mage_Customer_Model_Session':
                    $monyId = Mage::getModel('customer/customer')->load($customer->getId())->getMonyCustomerId();
            }

        }

        return $monyId;
    }

    /**
     * Get payment methods saved cards based on customer
     *
     * @param $customer
     * @return bool | array
     */
    public function getSavedCards($customer)
    {
        $monyCustomerId = $this->_getMonyCustomerId($customer);

        if ($monyCustomerId && Mage::helper('monypayments/card')->canSaveCards()) {
            $data = $this->_getCustomer($monyCustomerId);
            if (isset($data->paymentMethods)) {
                return $data->paymentMethods;
            }
        }

        return null;
    }

    /**
     * Deleting Saved Card from the admin
     * @todo Handle return when API has been build
     *
     * @param $monyId
     * @param $token
     * @return bool
     */
    public function deleteSavedCard($monyId, $token)
    {
        return $this->_deleteCard($monyId, $token);
    }
}