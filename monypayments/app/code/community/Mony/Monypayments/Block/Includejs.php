<?php

class Mony_Monypayments_Block_Includejs extends Mage_Core_Block_Template
{
    /**
     * Getting Mony.JS url based on the environment (prod/sandbox)
     *
     * @return string
     */
    public function getMonyJs()
    {
        $apiMode      = Mage::getStoreConfig('payment/monypayments/' . Mony_Monypayments_Model_Method_Base::API_MODE_CONFIG_FIELD);
        $settings     = Mony_Monypayments_Model_System_Config_Source_ApiMode::getEnvironmentSettings($apiMode);

        return $settings[Mony_Monypayments_Model_System_Config_Source_ApiMode::KEY_WEB_URL] . 'mony.js';
    }

}