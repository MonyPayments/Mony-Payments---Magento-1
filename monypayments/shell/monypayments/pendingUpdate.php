<?php
/**
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
require_once '../abstract.php';

class Mage_Shell_Monypayments_PendingUpdate extends Mage_Shell_Abstract
{
    public function monyOrder()
    {
        return Mage::getModel('monypayments/order');
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f pendingUpdate.php -- [options]

  --magento <id>                Check and update single order in Magento
  info                          Show all list Magento order still payment review
  runall                        Check and update all pending Magento order with Mony
  help                          This help

  <id>     Magento order id

USAGE;
    }

    public function run()
    {
        // Script to run single order
        if ($orderId = $this->getArg('magento')) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if (null == $order->getIncrementId()) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            }

            // if still cannot find the order otherwise process order
            if (null == $order->getIncrementId()) {
                echo "There are no matching order with order id " . $orderId . "\n";
            } else {
                if ($this->monyOrder()->updatePendingOrder($order)) {
                    echo "Order " . $orderId . " has been updated" . "\n";
                }
                echo "No update for order " . $orderId . "\n";
            }

        } elseif ($this->getArg('runall')) {
            // Script to run all pending order update
            $this->monyOrder()->updatePendingOrders();
            echo "Process completed!" . "\n";

        } elseif ($this->getArg('info')) {
            // Script to run info
            $orders = $this->monyOrder()->getPendingOrders();
            echo "There are " . count() . " orders currently in Payment Review:" . "\n";
            foreach ($orders as $order) {
                echo $order->getIncrementId() . "\n";
            }
        } else {
            // Show help
            echo $this->usageHelp();
        }
    }
}

$shell = new Mage_Shell_Monypayments_PendingUpdate();
$shell->run();