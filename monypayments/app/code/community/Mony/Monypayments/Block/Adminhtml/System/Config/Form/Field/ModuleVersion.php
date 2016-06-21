<?php

/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
class Mony_Monypayments_Block_Adminhtml_System_Config_Form_Field_ModuleVersion extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Get Module version from config.xml
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return SimpleXMLElement[]
     */
    public function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        /** @var Mage_Core_Model_Config_Element $moduleConfig */
        $moduleConfig = Mage::getConfig()->getModuleConfig($this->getModuleName());
        return $moduleConfig->version;
    }

}
