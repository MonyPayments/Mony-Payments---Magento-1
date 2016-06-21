<?php
/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */

$installer = $this;
$installer->startSetup();

/**
 * Create customer attribute for mony_customer_id
 */
$entityTypeId     = $installer->getEntityTypeId('customer');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

// Create customer attribute to store mony customer id
$installer->addAttribute('customer', 'mony_customer_id', array(
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Generated Mony Customer ID',
    'visible'       => 0,
    'required'      => 0,
    'user_defined' => 1,
));

// Add the attribute into the group
$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'mony_customer_id',
    '999'
);

$installer->endSetup();