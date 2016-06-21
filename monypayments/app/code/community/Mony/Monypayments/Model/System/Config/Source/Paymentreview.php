<?php

/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
class Mony_Monypayments_Model_System_Config_Source_Paymentreview extends Mage_Adminhtml_Model_System_Config_Source_Order_Status
{
    /**
     * Show only statuses mapped to Payment Review state
     * @var string
     */
    protected $_stateStatuses = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
}
