<?php
/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Setup script to create new column on sales_flat_quote_payment for:
 * - monypayments_save
 */
$table = $installer->getTable('sales/quote_payment');
$installer->getConnection()->addColumn($table, 'monypayments_save_card', "int DEFAULT NULL COMMENT 'Mony payments save payment'");

// End setup script
$installer->endSetup();
