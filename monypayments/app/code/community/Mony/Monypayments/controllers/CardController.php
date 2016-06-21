<?php
/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
class Mony_Monypayments_CardController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Validate that the user is logged in
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * Show the listing page of saved payment information
     */
    public function indexAction()
    {
        $this->loadLayout();

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My Saved Cards'));

        $this->renderLayout();
    }

    /**
     * @return Mony_Monypayments_Model_Customer
     */
    public function deleteAction()
    {
        if ($token = $this->getRequest()->getParam('token')) {
            $customer = Mage::getModel('customer/customer')->load($this->_getSession()->getId());

            if ($monyId = $customer->getMonyCustomerId()) {
                $response = Mage::getModel('monypayments/customer')->deleteSavedCard($monyId, $token);
            }
        }

        // Handling error and success message
        if ($response) {
            $this->_getSession()->addSuccess('Card has been successfully deleted');
        } else {
            $this->_getSession()->addError('There was an error deleting your payment. Please contact the administrator.');
        }

        // redirect to previous page
        $this->_redirect('*/*/index');
    }
}