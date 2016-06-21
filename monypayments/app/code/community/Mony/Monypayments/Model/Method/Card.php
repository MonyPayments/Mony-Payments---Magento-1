<?php

/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
class Mony_Monypayments_Model_Method_Card extends Mony_Monypayments_Model_Method_Base
{
    const PAYMENT_METHOD_CODE = 'monypayments';
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CODE;

    /**
     * Info and form blocks
     *
     * @var string
     */
    protected $_formBlockType = 'monypayments/form_card';
    protected $_infoBlockType = 'monypayments/info';
}
