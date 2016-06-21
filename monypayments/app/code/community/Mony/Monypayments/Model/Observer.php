<?php
/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
class Mony_Monypayments_Model_Observer
{
    /**
     * @return Mony_Monypayments_Helper_Data
     */
    protected function helper()
    {
        return Mage::helper('monypayments');
    }

    /**
     * Adding comment on Credit Memo regarding details response from API
     *
     * @param $observer
     */
    public function addMonypaymentsDataComment($observer)
    {
        // Load some information need to be added
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        $payment = $order->getPayment();

        // Check wether the payment method is Mony and use refund online
        if ($payment->getMethod() == Mony_Monypayments_Model_Method_Card::PAYMENT_METHOD_CODE && $payment->getAdditionalInformation()) {
            $refundData = $payment->getAdditionalInformation();
            $creditmemo->addComment(
                $this->helper()->__('Online refund on %s with Mony payments Refund ID %s', $refundData['refund_date'], $refundData['refund_id']), // Created comment with information from API
                false // set to not notify customer about this
            );
        }
    }

    /**
     * Assigned order status as per selected on Admin
     *
     * @param $observer
     */
    public function assignOrderStatus($observer)
    {
        /* @var Mage_Sales_Model_Order $order */
        $payment = $observer->getEvent()->getPayment();
        $order = $payment->getOrder();

        // Apply order status for specific order
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW &&                // Order State is Payment Review
            $payment->getMethod() == Mony_Monypayments_Model_Method_Card::PAYMENT_METHOD_CODE && // Payment using Mony Payments
            $order->getStatus() != $this->helper()->getPaymentReviewStatus()                     // Order status is not the same
        ) {
            // Set status to be selected payment review status from admin
            $order->setStatus($this->helper()->getPaymentReviewStatus());

            // Set if new order email will be sending
            if (!$this->helper()->isPaymentReviewOrderSend()) {
                $order->setCanSendNewEmailFlag(false);
            }

        }
    }
}
