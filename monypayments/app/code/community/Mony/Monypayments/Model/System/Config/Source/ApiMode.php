<?php

/**
 * API Model configuration source model
 *
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
class Mony_Monypayments_Model_System_Config_Source_ApiMode
{
    const KEY_NAME      = 'name';
    const KEY_API_URL   = 'api_url';
    const KEY_WEB_URL   = 'web_url';

    /**
     * Convert to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $config = self::_getConfigSettings();

        foreach ($config as $name => $settings) {
            $options[$name] = $settings[self::KEY_NAME];
        }

        return $options;
    }

    /**
     * Get config prefix for selected API mode
     *
     * @param string $environment
     * @return null|string
     */
    public static function getEnvironmentSettings($environment)
    {
        $settings = self::_getConfigSettings();

        if (isset($settings[$environment])) {
            return $settings[$environment];
        }

        return null;
    }

    /**
     * Get configured monypayments environments from config.xml
     *
     * @return array
     */
    protected static function _getConfigSettings()
    {
        $options = array();

        foreach (Mage::getConfig()->getNode('mony_monypayments/environments')->children() as $environment) {
            $options[$environment->getName()] = array(
                self::KEY_NAME      => (string) $environment->name,
                self::KEY_API_URL   => (string) $environment->api_url,
                self::KEY_WEB_URL   => (string) $environment->web_url,
            );
        }

        return $options;
    }
}
