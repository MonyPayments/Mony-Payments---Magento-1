<?php
/**
 * API Model configuration source model
 *
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */

class Mony_Monypayments_Model_System_Config_Source_Payment_Cctype extends Mage_Adminhtml_Model_System_Config_Source_Payment_Cctype
{
    /**
     * Show list of Cctypes that Mony supported
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $supportedTypes = Mage::helper('monypayments')->getSupportedCctypes();

        foreach (parent::toOptionArray() as $type) {
            if (in_array($type['value'], $supportedTypes)) {
                $options[] = $type;
            }
        }

        return $options;
    }
}