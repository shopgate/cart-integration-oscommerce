<?php
/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * Handler for Shopgate order conversions.
 * Ideally all non DB related operations will be
 * moved to a helper instead.
 */
class Shopgate_Models_Orders_Shopgate extends Shopgate_Models_Abstract_Db_Collection
{
    protected $tableName   = TABLE_ORDERS_SHOPGATE_ORDER;
    protected $idFieldName = 'shopgate_order_id';
    /** @var Shopgate_Models_Customers_Shopgate */
    protected $customerModel;
    /** @var ShopgateExternalOrder */
    protected $shopgateExternalOrder;
    /** @var ShopgateAddress */
    protected $shopgateAddress;

    /**
     * @inheritdoc
     */
    public function __construct(
        Shopgate_Models_Customers_Shopgate $customerModel,
        ShopgateExternalOrder $shopgateExternalOrder,
        ShopgateAddress $shopgateAddress
    ) {
        $this->customerModel         = $customerModel;
        $this->shopgateExternalOrder = $shopgateExternalOrder;
        $this->shopgateAddress       = $shopgateAddress;
        parent::__construct();
    }

    /**
     * Gathers all order data to an customer
     *
     * @param string $customerToken
     * @param int    $limit
     * @param int    $offset
     * @param string $orderDateFrom
     * @param string $sortOrder
     *
     * @return ShopgateExternalOrder[]
     * @throws ShopgateLibraryException
     */
    public function getCustomerOrders($customerToken, $limit, $offset, $orderDateFrom, $sortOrder)
    {
        include_once(DIR_WS_CLASSES . 'order.php');
        $customerId = $this->customerModel->getIdFromToken($customerToken);

        if (!is_numeric($customerId)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_CUSTOMER_TOKEN_INVALID,
                'Shopgate Plugin - no customer found with token: ' . $customerToken,
                true
            );
        }
        $params = array(
            'limit'     => $limit,
            'offset'    => $offset,
            'date_from' => $orderDateFrom,
            'sort'      => $sortOrder
        );

        $ordersArray = $this->getOrdersByCustomer($customerId, $params);
        $orders      = array();

        foreach ($ordersArray as $row) {
            /** @var ShopgateExternalOrder $order */
            $osOrderNumber = $row['orders_id'];
            $osOrder       = new order($osOrderNumber);
            $order         = new $this->shopgateExternalOrder;

            $shopgateOrder = $this->getShopgateOrder($osOrderNumber);

            $order->setOrderNumber($shopgateOrder['shopgate_order_number']);
            $order->setExternalOrderNumber($osOrderNumber);
            $order->setExternalOrderId($osOrderNumber);
            $order->setStatusName($osOrder->info['orders_status']);
            $order->setCreatedTime(date(DateTime::ISO8601, strtotime($osOrder->info['date_purchased'])));
            $order->setMail($osOrder->customer['email_address']);
            $order->setPhone($osOrder->customer['telephone']);
            $order->setCurrency($osOrder->info['currency']);
            $order->setPaymentMethod($osOrder->info['payment_method']);
            $order->setIsShippingCompleted(false);
            foreach ($this->getOrderHistory($osOrderNumber) as $status) {
                if ($status['orders_status_id'] = 3) {
                    $order->setIsShippingCompleted(true);
                    $order->setShippingCompletedTime(date(DateTime::ISO8601, strtotime($status['date_added'])));
                }
            }
            $order->setAmountComplete($osOrder->order_total['total']['plain']);
            /** @var ShopgateAddress $invoiceAddress */
            $invoiceAddress = new $this->shopgateAddress;
            $name           = explode(' ', $osOrder->billing['name']);
            $invoiceAddress->setFirstName($name[0]);
            $invoiceAddress->setLastName($name[1]);
            $invoiceAddress->setCompany($osOrder->billing['company']);
            $invoiceAddress->setStreet1($osOrder->billing['street_address']);
            $invoiceAddress->setStreet2($osOrder->billing['suburb']);
            $invoiceAddress->setCity($osOrder->billing['city']);
            $invoiceAddress->setZipcode($osOrder->billing['postcode']);
            $invoiceAddress->setCountry($osOrder->billing['country']['title']);
            $invoiceAddress->setState($osOrder->billing['state']);
            $invoiceAddress->setIsDeliveryAddress(false);
            $invoiceAddress->setIsInvoiceAddress(true);
            $order->setInvoiceAddress($invoiceAddress);
            /** @var ShopgateAddress $deliveryAddress */
            $deliveryAddress = new $this->shopgateAddress;
            $name            = explode(' ', $osOrder->delivery['name']);
            $deliveryAddress->setFirstName($name[0]);
            $deliveryAddress->setLastName($name[1]);
            $deliveryAddress->setCompany($osOrder->delivery['company']);
            $deliveryAddress->setStreet1($osOrder->delivery['street_address']);
            $deliveryAddress->setStreet2($osOrder->delivery['suburb']);
            $deliveryAddress->setCity($osOrder->delivery['city']);
            $deliveryAddress->setZipcode($osOrder->delivery['postcode']);
            $deliveryAddress->setCountry($osOrder->delivery['country']['title']);
            $deliveryAddress->setState($osOrder->delivery['state']);
            $deliveryAddress->setIsDeliveryAddress(true);
            $deliveryAddress->setIsInvoiceAddress(false);

            $order->setDeliveryAddress($deliveryAddress);
            $itemsAndTaxes = $this->getOrderItemsFormatted($osOrder->products, $osOrder->info['currency']);
            $order->setOrderTaxes($itemsAndTaxes[1]);
            $order->setItems($itemsAndTaxes[0]);

            $extraCosts = $this->getExtraCosts($osOrderNumber);
            $order->setExtraCosts($extraCosts);

            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Gets Shopgate order from system order ID
     *
     * @param $sysOrderId
     *
     * @return mixed[]
     * @throws ShopgateLibraryException
     */
    private function getShopgateOrder($sysOrderId)
    {
        $orders = ShopgateWrapper::db_select_query(TABLE_ORDERS_SHOPGATE_ORDER, array('orders_id' => $sysOrderId));

        return array_pop($orders);
    }

    /**
     * Read the history to an order from the database
     *
     * @param $sysOrderId
     *
     * @return mixed[]
     */
    private function getOrderHistory($sysOrderId)
    {
        return ShopgateWrapper::db_select_query(TABLE_ORDERS_STATUS_HISTORY, array('orders_id' => $sysOrderId));
    }

    /**
     * Read the total data to an order from the database
     *
     * @param $sysOrderId
     *
     * @return mixed[]
     */
    private function getOrderTotals($sysOrderId)
    {
        return ShopgateWrapper::db_select_query(TABLE_ORDERS_TOTAL, array('orders_id' => $sysOrderId));
    }


    /**
     * Gets customer data based on data provided
     *
     * @param       $customerId
     * @param array $data (
     *                    'limit'     => ...,
     *                    'offset'    => ...,
     *                    'date_from' => ...,
     *                    'sort'      => ...
     *                    )
     *
     * @return array
     * @throws ShopgateLibraryException
     */
    private function getOrdersByCustomer($customerId, $data = array())
    {
        /**
         * Query DB for order data
         */
        $sqlQuery = "
            SELECT
                c.*
            FROM " . TABLE_ORDERS . " c
            WHERE c.customers_id = " . $customerId;

        if (!empty($data['date_from'])) {
            $sqlQuery .= " AND c.date_purchased >= '" . $data['date_from'] . "'";
        }

        if (!empty($data['sort'])) {
            $sortOrder = strtoupper(str_replace('created_', '', $data['sort']));
            $sqlQuery .= " ORDER BY c.date_purchased {$sortOrder}";
        }

        if (!empty($data['limit'])) {
            $sqlQuery .= " LIMIT " . $data['limit'];
            if (isset($data['offset'])) {
                $sqlQuery .= " OFFSET " . $data['offset'];
            }
        }

        $sqlQuery .= ";";

        $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);

        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting an order.", true
            );
        }

        /**
         * Queue orders up
         */
        $orders = array();
        while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
            $orders[] = $row;
        }

        return $orders;
    }

    /**
     * Concerts order items into a formatted format
     *
     * @param $products
     * @param $currency
     *
     * @return array
     */
    private function getOrderItemsFormatted($products, $currency)
    {
        $orderItems = array();
        $orderTaxes = array();
        foreach ($products as $orderProduct) {
            $item = new ShopgateExternalOrderItem;

            $item->setItemNumber($orderProduct['id']);
            $item->setItemNumberPublic($orderProduct['model']);
            $item->setQuantity($orderProduct['qty']);
            $itemname       = $orderProduct['name'];
            $firstAttribute = true;
            if (!empty($orderProduct['attributes'])) {
                foreach ($orderProduct['attributes'] as $attribute) {
                    if ($firstAttribute) {
                        $itemname .= ' - ';
                    } else {
                        $itemname .= ', ';
                    }
                    $itemname .= $attribute['option'] . ':' . $attribute['value'];
                    $firstAttribute = false;
                }
            }
            $item->setName($itemname);
            $item->setUnitAmount($orderProduct["final_price"]);
            $itemTaxAmount = $orderProduct["final_price"] * ($orderProduct['tax'] / 100);
            $item->setUnitAmountWithTax($itemTaxAmount + $orderProduct["final_price"]);
            $item->setTaxPercent($orderProduct['tax']);
            $item->setCurrency($currency);

            if ($itemTaxAmount > 0) {
                $orderTaxAdded = false;
                foreach ($orderTaxes as $orderTax) {
                    if ($orderTax->getTaxPercent() == $orderProduct['tax']) {
                        $orderTax->setAmount($orderTax->getAmount() + $itemTaxAmount * $orderProduct['qty']);
                        $orderTaxAdded = true;
                    }
                }
                if (!$orderTaxAdded) {
                    $orderTax = new ShopgateExternalOrderTax();
                    $orderTax->setAmount($itemTaxAmount * $orderProduct['qty']);
                    $orderTax->setTaxPercent($orderProduct['tax']);
                    $orderTax->setLabel(round($orderProduct['tax'], 2) . '%');
                    $orderTaxes[] = $orderTax;
                }
            }
            $orderItems[] = $item;
        }

        return array($orderItems, $orderTaxes);
    }

    /**
     * Read the order status from the database by language an status
     *
     * @param $status
     * @param $customerLanguage
     *
     * @return mixed
     */
    protected function getStatusName($status, $customerLanguage)
    {
        $customerLanguageCode    = explode("_", $customerLanguage);
        $customerLanguageCode    = $customerLanguageCode[0];
        $customerLanguageCodeRow =
            ShopgateWrapper::db_select_query(TABLE_LANGUAGES, array('code' => $customerLanguageCode));
        $customerLanguageCodeRow = $customerLanguageCodeRow[0];
        if (empty($customerLanguageCodeRow)) {
            $customerLanguageCodeRow['languages_id'] = 1;
        }
        $name = ShopgateWrapper::db_select_query(
            TABLE_ORDERS_STATUS,
            array('orders_status_id' => $status, 'language_id' => $customerLanguageCodeRow['languages_id'])
        );

        return $name[0]['orders_status_name'];
    }

    /**
     * Gather all extra cost to an order
     *
     * @param $sysOrderId
     *
     * @return array
     */
    protected function getExtraCosts($sysOrderId)
    {
        $totals     = $this->getOrderTotals($sysOrderId);
        $extraCosts = array();
        foreach ($totals as $total) {
            $extraCost = new ShopgateExternalOrderExtraCost();
            $type      = '';
            switch ($total['class']) {
                case 'ot_subtotal':
                case 'ot_tax':
                    break;
                case 'ot_total':
                    break;
                case 'ot_shipping':
                    $type = 'shipping';
                    break;
                default:
                    $type = 'misc';
            }
            if (empty($type)) {
                continue;
            }
            $extraCost->setType($type);
            $extraCost->setAmount($total['value']);
            $extraCost->setLabel($total['title']);
            $extraCosts[] = $extraCost;
        }

        return $extraCosts;
    }
}
