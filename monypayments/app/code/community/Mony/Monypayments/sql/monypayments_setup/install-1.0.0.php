<?php

/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getTable('sales_flat_order_payment');
$installer->getConnection()->addColumn($table, "monypayments_token", "varchar(255) DEFAULT NULL COMMENT 'Mony payments Order Token'");
$installer->getConnection()->addColumn($table, "monypayments_order_id", "varchar(255) DEFAULT NULL COMMENT 'Mony payments Order ID'");

// add new status and map it to Payment Review state
$status = 'monypayments_payment_review';
$state  = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
$installer->run("INSERT INTO `{$this->getTable('sales_order_status')}` (`status`, `label`) VALUES ('{$status}', 'Mony payments Processing');");
$installer->run("INSERT INTO `{$this->getTable('sales_order_status_state')}` (`status`, `state`, `is_default`) VALUES ('{$status}', '{$state}', '0');");

$installer->endSetup();
