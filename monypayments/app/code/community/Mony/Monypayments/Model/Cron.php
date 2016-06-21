<?php
/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */

class Mony_Monypayments_Model_Cron extends Mage_Cron_Model_Schedule
{
    /**
     * Getting updated to pending order through cron
     */
    public function pendingOrderUpdate()
    {
        try {
            Mage::getModel('monypayments/order')->updatePendingOrders();
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('monypayments')->__('Cron Monypayment Error:') .
                ' ' . $e->getMessage()
            );
        }

    }
}