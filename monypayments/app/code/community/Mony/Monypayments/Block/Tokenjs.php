<?php
/**
 * Mony Monypayments payment
 *
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */

class Mony_Monypayments_Block_Tokenjs extends Mage_Core_Block_Template
{
    /**
     * Return API key from config
     *
     * @return Mony_Monypayments_Model_Method_Card
     */
    public function getApiKey()
    {
        return Mage::helper('monypayments')->getMonyApiKey();
    }
}
