<?php

/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 *
 * @method void setRedirectMessage(string $message);
 */
class Mony_Monypayments_Block_Form_Card extends Mage_Payment_Block_Form_Cc
{
    /**
     * Constant variable
     */
    const TEMPLATE_OPTION_TITLE = "monypayments/checkout/title.phtml";
    const TEMPLATE_OPTION_DETAILS = "monypayments/form/card.phtml";

    /**
     * _construct function for setting up frontend
     */
    protected function _construct()
    {
        parent::_construct();

        $titleBlock = Mage::getConfig()->getBlockClassName('core/template');
        $titleBlock = new $titleBlock;
        $titleBlock->setTemplateHelper($this);
        $titleBlock->setTemplate(self::TEMPLATE_OPTION_TITLE);

        $this->setTemplate('monypayments/form/card.phtml');
        $this->setMethodTitle('')
            ->setMethodLabelAfterHtml($titleBlock->toHtml());
    }

    /**
     * The method returns the path to the payment logo image file.
     *
     * @return string
     */
    public function getPaymentLogoPath()
    {

        return $this->getSkinUrl('monypayments/images/monypayments-logo.svg');
    }

    /**
     * Getting the Title of the payment method
     *
     * @return mixed
     */
    public function getTitle()
    {
        return Mage::getStoreConfig(Mony_Monypayments_Helper_Data::CONFIG_PATH_MONYPAYMENTS_TITLE, Mage::app()->getStore());

    }

    /**
     * Return the cctype icon based on the code
     *
     * @param $type
     * @return null|string
     */
    public function getImageCcTypes($type)
    {
        switch ($type) {
            case 'AE':
                return $this->getSkinUrl('monypayments/images/cc_types/americanexpress.png');
            case 'VI':
                return $this->getSkinUrl('monypayments/images/cc_types/visa.png');
            case 'MC':
                return $this->getSkinUrl('monypayments/images/cc_types/mastercard.png');
            case 'DI':
                return $this->getSkinUrl('monypayments/images/cc_types/discover.png');
            case 'SM':
                return $this->getSkinUrl('monypayments/images/cc_types/switchmaestro.png');
            case 'SO':
                return $this->getSkinUrl('monypayments/images/cc_types/solo.png');
            case 'JCB':
                return $this->getSkinUrl('monypayments/images/cc_types/jcb.png');
            default:
                return null;
        }
    }

    /**
     * Get text to show on the dropdown checkout
     *
     * @param $card
     * @return string
     */
    public function getCardOptionText($card = false)
    {
        if ($card) {
            $helper = Mage::helper('monypayments/card');
            $card = $helper->getCardType($card) . ' ' . $helper->getCardNumber($card) . ' expires ' . $helper->getExpiryDate($card);
        }

        return $card;
    }
}