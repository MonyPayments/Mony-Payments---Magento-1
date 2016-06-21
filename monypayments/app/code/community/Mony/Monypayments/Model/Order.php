<?php
/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */

class Mony_Monypayments_Model_Order extends Mony_Monypayments_Model_Method_Card
{
    /**
     * Retrieving Order payment review using Mony as a method
     *
     * @param null $limit
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getPendingOrders($limit = null)
    {
        // Load collection form order with specified
        $collection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('state', array('eq' => Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)) // order state is payment review
            ->join(
                array('payment' => 'sales/order_payment'),
                'main_table.entity_id=payment.parent_id',
                array(
                    'payment_method' => 'payment.method',                                           // Get payment method
                    'monypayments_order_id' => 'payment.monypayments_order_id',                     // Get Mony order id
                )
            );

        // Filter only using Mony Payments
        $collection->addFieldToFilter('payment.method', array('eq' => Mony_Monypayments_Model_Method_Card::PAYMENT_METHOD_CODE));
        $collection->getSelect()->limit($limit); // Add limit for longer processes
        $collection->load();

        return $collection;
    }

    /**
     * Update order function to from Pending to Approved/Decline
     *
     * @param $api
     * @param $order
     * @param bool $updated
     *
     * @return bool
     */
    public function updateOrderStatus($api, $order, $updated = false)
    {
        // check wether the status response from API
        if (isset($api->status)) {

            // define variable
            $response = array(
                'update' => true,
                'status' => $api->status,
                'status_reason' => $api->statusReason,
            );

            // Check if status approved or decline
            switch ($api->status) {
                case Mony_Monypayments_Model_Method_Base::STATUS_APPROVED:
                    $response['state'] = Mage_Sales_Model_Order::STATE_PROCESSING;
                    break;

                case Mony_Monypayments_Model_Method_Base::STATUS_DECLINED:
                    $response['state'] = Mage_Sales_Model_Order::STATE_CANCELED;
                    break;

                default:
                    $response['update'] = false;
                    break;
            }

            // Update the actual order in Magento
            $updated = $this->_updateOrder($response, $order);
        }

        return $updated;
    }

    /**
     * Update actual order with save function
     *
     * @param $data
     * @param $order
     *
     * @return bool
     */
    public function _updateOrder($data, $order)
    {
        $updated = false;

        // Check if data is updated
        if ($data['update']) {
            // Set order state and add comment to the order
            try {
                $order->setState(
                    $data['state'],
                    true,
                    Mage::helper('monypayments')->__('Order updated %s from Gateway! With status reason %s', $data['status'], $data['status_reason'])
                )->save();

                // Set Response
                $updated = true;

            } catch (Exception $e) {
                Mage::helper('monypayments')->log($e->getMessage());
            }
        }

        return $updated;
    }

    /**
     * Update All Pending Orders  in Magento
     */
    public function updatePendingOrders()
    {
        $orders = $this->getPendingOrders();

        $url = $this->_getListOrderApiUrl($orders);

        $response = $this->getApiAdapter()->_sendRequest($url, false);

        foreach ($response->results as $result) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($result->merchantReference);
            $this->updateOrderStatus($result, $order);
        }

    }

    /**
     * Update single pending order in Magento
     *
     * @param $order
     * @param $monyOrderId
     *
     * @return bool
     */
    public function updatePendingOrder($order, $monyOrderId)
    {
        $url = $this->_getOrderApiUrl($monyOrderId);

        $response = $this->getApiAdapter()->_sendRequest($url, false);

        $process =  $this->updateOrderStatus($response, $order);

        if ($process && isset($response->status)) {
            return $response->status;
        }
        return $process;
    }

    /**
     * Building URL for retrieve single order;
     * Single pending order using this one for efficiency
     *
     * @param $monyOrderId
     * @return string
     */
    protected function _getOrderApiUrl($monyOrderId)
    {
        // Get the environment and return url
        $apiMode      = $this->getConfigData(self::API_MODE_CONFIG_FIELD);
        $settings     = Mony_Monypayments_Model_System_Config_Source_ApiMode::getEnvironmentSettings($apiMode);

        return $settings[Mony_Monypayments_Model_System_Config_Source_ApiMode::KEY_API_URL] . 'orders/' . $monyOrderId;
    }

    /**
     * Building URL for retrieve multiple order
     *
     * @param $orders
     * @return string
     */
    protected function _getListOrderApiUrl($orders)
    {
        // counter and base variable
        $i = 0;
        $query = '';

        // looping the orders as been given and calculate for URL
        foreach ($orders as $order) {
            if ($i > 0) {
                $query .= '&';
            }
            $query .= 'ids[]=' . $order->getMonypaymentsOrderId();
            $i++;
        }

        // Get the environment and return url
        $apiMode      = $this->getConfigData(self::API_MODE_CONFIG_FIELD);
        $settings     = Mony_Monypayments_Model_System_Config_Source_ApiMode::getEnvironmentSettings($apiMode);

        return $settings[Mony_Monypayments_Model_System_Config_Source_ApiMode::KEY_API_URL] . 'orders?' . $query;
    }


}