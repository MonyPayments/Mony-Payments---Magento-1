<?php

/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */

/**
 * Class Mony_Monypayments_Model_Api_Adapter
 *
 * Building API requests and parsing API responses.
 */
class Mony_Monypayments_Model_Api_Adapter extends Mony_Monypayments_Model_Api_Mony
{
    /**
     * Get Charge amount for API
     *
     * @param $order
     * @param $amount
     * @return array
     */
    public function getChargeAmount($order, $amount)
    {
        $currency = $order->getOrderCurrencyCode();

        if( $currency != "AUD" ) {
            Mage::helper('monypayments')->log(array('Marking as AUD from' => $currency));
            $currency = "AUD";
        }
        return array(
            'amount' => number_format($amount, 2),
            // 'currency' => $order->getOrderCurrencyCode()
            'currency' => $currency
            );
    }

    /**
     * Get Payment Method for API
     *
     * @param $payment
     * @return array
     */
    public function getPaymentMethod($payment)
    {
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();

        // Payment method data
        $data =  array(
            'token' => $order->getPayment()->getMonypaymentsToken(),
            'billingAddress' => array(
                'name'  => $billing->getName(),
                'address1'  => array_values($billing->getStreet())[0],
                'suburb'    => $billing->getCity(),
                'state'     => $billing->getRegion(),
                'postcode'  => $billing->getPostcode(),
                'countryCode' => $billing->getCountryId(),
                'phoneNumber' => $billing->getTelephone()
            )
        );

        // Save payment method
        if ($payment->getMonypaymentsSaveCard()) {
            $data['save'] = true;
        }

        return $data;
    }

    /**
     * Get customer info for mony to create order
     *
     * @param $payment
     * @param null $monyCustomerId
     * @return array
     */
    public function getCustomerInfo($payment, $monyCustomerId = null)
    {
        $order = $payment->getOrder();
        // if Mony Customer Id provided, return only customer id
        if ($monyCustomerId) {
             $data = array(
                 'email' => $order->getCustomerEmail(),
                'customerId' => $monyCustomerId,
            );
            return $data;
        } else { // return email if not login
            return array(
                'email' => $order->getCustomerEmail()
            );
        }
    }

    /**
     * Get merchant reference for API
     *
     * @param $order
     * @return mixed
     */
    public function getMerchantReference($order)
    {
        return $order->getIncrementId();
    }

    /**
     * Get Order details for API
     *
     * @param $order
     * @return mixed
     */
    public function getOrderDetail($order)
    {
        $data['items'] = $this->_itemData($order->getAllVisibleItems(), $order->getOrderCurrencyCode());

        if ($shipping = $order->getShippingAddress()) {
            $data['shippingAddress'] = array(
                'name' => $shipping->getName(),
                'address1' => array_values($shipping->getStreet())[0],
                'address2' => array_key_exists(1, $shipping->getStreet()) ? array_values($shipping->getStreet())[1] : '',
                'suburb'   => $shipping->getCity(),
                'state'    => $shipping->getRegion(),
                'postcode' => $shipping->getPostcode(),
                'countryCode' => $shipping->getCountryId(),
                'phoneNumber' => $shipping->getTelephone(),
            );
        }
        return $data;
    }

    /**
     * Get Item Data needed for Mony API
     *
     * @param $items
     * @param string $currency
     *
     * @return array
     */
    protected function _itemData($items, $currency = 'AUD')
    {
        // set original data
        $data = array();

        // looping all item data that needed for API
        foreach ($items as $item)
        {
            $data[] = array(
                'name' => $item->getName(),
                'sku'  => $item->getSku(),
                'quantity'  => $item->getQtyOrdered(),
                'price' => array(
                    'amount' => number_format($item->getPrice(), 2),
                    'currency' => $currency,
                )
            );

        }
        // return the item data
        return $data;
    }
}

