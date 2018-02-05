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
define("SHOPGATE_PLUGIN_VERSION", "2.9.36");

if (!defined('TABLE_ORDERS_SHOPGATE_ORDER')) {
    define('TABLE_ORDERS_SHOPGATE_ORDER', 'orders_shopgate_order');
}

if (!defined('TABLE_CUSTOMERS_SHOPGATE_CUSTOMER')) {
    define('TABLE_CUSTOMERS_SHOPGATE_CUSTOMER', 'customers_shopgate_customer');
}

if (!defined('TABLE_CUSTOMERS_MEMO')) {
    define('TABLE_CUSTOMERS_MEMO', "customers_memo");
}

if (!defined('TABLE_CUSTOMERS_INFO')) {
    define('TABLE_CUSTOMERS_INFO', "customers_info");
}

/**
 * osCommerce-Plugin for Shopgate
 */
class ShopgatePluginOsCommerce extends ShopgatePlugin
{
    /** @var ShopgateConfigOsCommerce */
    protected $config;
    /** @var int */
    protected $taxZone;
    /** @var string */
    protected $swisscartVersion;
    /** @var int */
    protected $swisscartImageCount;
    /** @var string */
    protected $language;
    /** @var string */
    protected $currency;
    /** @var int */
    protected $itemHighlightOrderIndex;
    /** @var int */
    protected $langID;
    /** @var tad_DI52_Container */
    protected $di;

    /**
     * Helps loads things post startup script
     * which needs only config loaded
     *
     * @param array $data
     */
    public function init(array $data)
    {
        if (isset($data['di'])) {
            $this->di = $data['di'];
        }

        $this->di->setVar('language_id', $this->langID);
    }

    /**
     * @param int $languageId
     */
    public function setLanguageId($languageId)
    {
        $this->langID = $languageId;
    }

    /**
     * @return bool
     * @throws ShopgateLibraryException
     */
    public function startup()
    {
        // Check Shoppingsystem-Type
        $this->swisscartVersion = defined("SWISSCART_VERSION") ? SWISSCART_VERSION : '';
        $this->config           = $this->createNewOsCommerceConfig(); //function loads before DI injection

        if (!empty($_REQUEST['shop_number'])) {
            $this->config->loadByShopNumber($_REQUEST['shop_number']);
        }

        // Map config language code to OSC language ID
        $langCode   = $this->config->getLanguage();
        $sql        = "SELECT languages_id, directory FROM " . TABLE_LANGUAGES . " WHERE code = '$langCode'";
        $langResult = $this->wrapperPerformQuery($sql);
        if (!$langResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting language.", true
            );
        }

        $language = $this->wrapperPerformFetchArray($langResult);
        if (empty($language) || empty($language['languages_id'])
            || empty($language['directory'])
        ) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - language code [$langCode] does not exist.",
                true
            );
        }

        $this->langID   = $language['languages_id'];
        $this->language = $language["directory"];
        $this->currency = $this->config->getCurrency();

        $sql       = "SELECT value FROM " . TABLE_CURRENCIES . " WHERE code = '" . $this->currency . "'";
        $result    = $this->wrapperPerformQuery($sql);
        $_currency = $this->wrapperPerformFetchArray($result);

        $this->exchangeRate = $_currency["value"];
        $this->taxZone      = $this->config->getTaxZoneId();
        if (!empty($this->taxZone)) {
            $sql       = "SELECT * FROM " . TABLE_TAX_RATES . " WHERE tax_zone_id = '" . $this->taxZone . "'";
            $result    = $this->wrapperPerformQuery($sql);
            $taxZone   = $this->wrapperPerformFetchArray($result);
            if (empty($taxZone)) {
                $this->taxZone = 0;
                $this->config->setTaxZoneId(0);
            }
        }


        if (!defined("DIR_FS_LANGUAGES")) {
            define('DIR_FS_LANGUAGES', dirname(__FILE__) . '/base/admin/includes/languages/');
        }

        $langFiles = array($this->language . '.php');

        foreach ($langFiles as $langFile) {
            include_once(DIR_FS_LANGUAGES . "/$langFile");
        }

        return true;
    }

    /**
     * @param string $query
     * @return resource
     */
    protected function wrapperPerformQuery($query)
    {
        return ShopgateWrapper::db_query($query);
    }

    /**
     * @param resource $resource
     *
     * @return mixed[]
     */
    protected function wrapperPerformFetchArray($resource)
    {
        return ShopgateWrapper::db_fetch_array($resource);
    }

    /**
     * @return ShopgateConfigOsCommerce
     */
    protected function createNewOsCommerceConfig()
    {
        return new ShopgateConfigOsCommerce();
    }

    /**
     * Registers the customer in the system after being
     * called by the API
     *
     * @param string           $user
     * @param string           $pass
     * @param ShopgateCustomer $customer
     *
     * @throws Exception
     * @throws ShopgateLibraryException
     */
    public function registerCustomer($user, $pass, ShopgateCustomer $customer)
    {
        if (!function_exists('tep_encrypt_password')) {
            /** @noinspection PhpIncludeInspection */
            require_once(rtrim(DIR_FS_CATALOG, "/") . "/" . rtrim(DIR_WS_FUNCTIONS, "/") . '/password_funcs.php');
        }
        /** @var ShopgateCustomer $customer */
        $customer = $customer->utf8Decode($this->config->getEncoding());
        $user     = $this->stringFromUtf8($user, $this->config->getEncoding());
        $encPass  = tep_encrypt_password($pass);
        $date     = date('Y-m-d H:i:s');

        /** @var Shopgate_Models_Customers_Native $dbCustomer */
        $dbCustomer = $this->di->make('Shopgate_Models_Customers_Native');
        $dbCustomer->load($user, 'customers_email_address');

        if ($dbCustomer->getId()) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::REGISTER_USER_ALREADY_EXISTS, '', true
            );
        }

        if ($customer->getBirthday()) {
            $dbCustomer->setCustomersDob(date('Y-m-d H:i:s', strtotime($customer->getBirthday())));
        }

        $dbCustomer->setCustomersFirstname($customer->getFirstName())
                   ->setCustomersLastname($customer->getLastName())
                   ->setCustomersEmailAddress($customer->getMail())
                   ->setCustomersTelephone($customer->getPhone())
                   ->setCustomersGender($customer->getGender())
                   ->setCustomersPassword($encPass)
                   ->setCustomersNewsletter(0)
                   ->save();

        /** @var Shopgate_Models_Customers_Info $customerInfo */
        $customerInfo = $this->di->make('Shopgate_Models_Customers_Info');
        $customerInfo
            ->setCustomersInfoId($dbCustomer->getId())
            ->setCustomersInfoNumberOfLogons(0)
            ->setCustomersInfoDateAccountCreated($date)
            ->setCustomersInfoDateAccountLastModified($date)
            ->save();


        $customer->setCustomerId($dbCustomer->getId());
        /** @var Shopgate_Models_Customers_Shopgate $shopgateCustomer */
        $shopgateCustomer = $this->di->make('Shopgate_Models_Customers_Shopgate');
        $shopgateCustomer->getTokenForCustomer($customer);

        $addresses   = array();
        $addressList = $customer->getAddresses();

        foreach ($addressList as $key => $address) {
            foreach ($addressList as $secondKey => $secondAddress) {
                if ($address->equals($secondAddress) && $secondKey < $key) {
                    $key = $secondKey;
                }
            }
            $addresses[$key] = $address;
        }

        $defaultAddress = true;
        foreach ($addresses as $address) {
            /** @var ShopgateAddress $address */
            $stateCode = ShopgateMapper::getShoppingsystemStateCode($address->getState());
            /** @var Shopgate_Models_Zones_Native $zones */
            $zones    = $this->di->make('Shopgate_Models_Zones_Native');
            $zones->getSelect()
                   ->joinLeft(
                       array('country' => TABLE_COUNTRIES),
                       'country.countries_id = main.zone_country_id',
                       array('entry_country' => 'countries_iso_code_2')
                   )
                   ->where('country.countries_iso_code_2 = ?', $address->getCountry());
            if (!empty($stateCode)) {
                $zones->getSelect()->where('main.zone_code = ?', $stateCode);
            }
            $zone = $zones->getCollection()->getFirstItem();

            /** @var Shopgate_Models_Address_Book $book */
            $book = $this->di->make('Shopgate_Models_Address_Book');
            $book->setCustomersId($dbCustomer->getId())
                 ->setEntryCompany($address->getCompany())
                 ->setEntryZoneId($zone->getId())
                 ->setEntryCountryId($zone->getZoneCountryId())
                 ->setEntryFirstname($address->getFirstName())
                 ->setEntryLastname($address->getLastName())
                 ->setEntryGender($address->getGender())
                 ->setEntryStreetAddress($address->getStreet1())
                 ->setEntrySuburb($address->getStreet2())
                 ->setEntryPostcode($address->getZipcode())
                 ->setEntryCity($address->getCity())
                 ->setEntryState($zone->getZoneName())
                 ->save();

            if ($defaultAddress) {
                $dbCustomer->setCustomersDefaultAddressId($book->getId())->save();
                $defaultAddress = false;
            }
        }
    }

    /**
     * Retrieves the customer data from the system
     * in a Shopgate ready format
     *
     * @param string $user
     * @param string $pass
     *
     * @return ShopgateCustomer
     * @throws Exception
     * @throws ShopgateLibraryException
     */
    public function getCustomer($user, $pass)
    {
        /** @var Shopgate_Models_Customers_Native $dbCustomer */
        $dbCustomer = $this->di->make('Shopgate_Models_Customers_Native');
        $dbCustomer->load($user, 'customers_email_address');

        if (!$dbCustomer->getId()) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting the customer.", true
            );
        }

        // Check if the user has been authenticated correctly
        if (!ShopgateWrapper::validate_password(
            $pass, $dbCustomer->getCustomersPassword()
        )
        ) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_WRONG_USERNAME_OR_PASSWORD,
                'User: ' . $user
            );
        }

        /**
         * Get all user addresses
         *
         * @var Shopgate_Models_Address_Book $collection
         */
        $collection = $this->di->make('Shopgate_Models_Address_Book');
        $collection->getSelect()
          ->joinLeft(
              array('zone' => TABLE_ZONES),
              'zone.zone_id = main.entry_zone_id',
              array('entry_state_zone' => 'zone_code')
          )
          ->joinLeft(
              array('country' => TABLE_COUNTRIES),
              'country.countries_id = main.entry_country_id',
              array('entry_country' =>'countries_iso_code_2')
              )
          ->where('main.customers_id = ?', $dbCustomer->getId());
        $collection->getCollection();

        if (!$collection->getSize()) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting the customer addresses.",
                true
            );
        }

        /**
         * Get all customer data and all addresses out of the customers address
         *
         * @var Shopgate_Models_Address_Book[] $customerAddresses
         */
        $customerAddresses = array();
        foreach($collection as $key => $customerAddress) {
            if ($customerAddress->getData('address_book_id') == $dbCustomer->getCustomersDefaultAddressId()) {
                $customerAddresses[0] = $customerAddress;
            } else {
                $customerAddresses[] = $customerAddress;
            }
        }

        // Create list of shopgate addresses
        $addresses = array();
        foreach ($customerAddresses as $customerAddress) {
            // Get the correct state
            try {
                $stateCode = ShopgateMapper::getShopgateStateCode(
                    $customerAddress->getData('entry_country'),
                    $customerAddress->getData('entry_state_zone')
                );
            } catch (ShopgateLibraryException $e) {
                // Happens if the state code can not be mapped to ISO use system state code
                $stateCode = $customerAddress->getData('entry_state_zone');
            }

            // Invoice and delivery addresses are always identical in this shopping system
            $addressType = ShopgateAddress::BOTH;
            $addressGender
                         = $customerAddress->getEntryGender() ? ShopgateAddress::MALE: ShopgateAddress::FEMALE;

            /**
             * Now fill a new address data container
             *
             * @var ShopgateAddress $address
             */
            $address = $this->di->make('ShopgateAddress');
            $address->setId($customerAddress->getAddressBookId());
            $address->setAddressType($addressType);
            $address->setGender($addressGender);
            $address->setFirstName($customerAddress->getEntryFirstname());
            $address->setLastName($customerAddress->getEntryLastname());
            $address->setBirthday(null);
            $address->setCompany($customerAddress->getEntryCompany());
            $address->setStreet1($customerAddress->getEntryStreetAddress());
            $address->setStreet2($customerAddress->getEntrySuburb());
            $address->setZipcode($customerAddress->getEntryPostcode());
            $address->setCity($customerAddress->getEntryCity());
            $address->setCountry($customerAddress->getData('entry_country'));
            $address->setState($stateCode);
            $address->setPhone($dbCustomer->getCustomersTelephone());
            $address->setMobile('');
            $address->setMail($dbCustomer->getCustomersEmailAddress());
            $addresses[] = $address;
        }

        /** @var Shopgate_Models_Customers_Shopgate $shopgateCustomer */
        $shopgateCustomer = $this->di->make('Shopgate_Models_Customers_Shopgate');
        /** @var ShopgateCustomer $customer */
        $customer = $this->di->make('ShopgateCustomer');
        $customer->setCustomerId($dbCustomer->getId());
        $customer->setCustomerNumber($dbCustomer->getId());
        $customer->setCustomerGroup(null);
        $customer->setCustomerGroupId(null);
        $customer->setGender(
            $dbCustomer->getCustomersGender() ? ShopgateCustomer::MALE : ShopgateCustomer::FEMALE
        );
        $customer->setFirstName($dbCustomer->getCustomersFirstname());
        $customer->setLastName($dbCustomer->getCustomersLastname());
        $customer->setBirthday($dbCustomer->getShopgateReadyBirthday());
        $customer->setPhone($dbCustomer->getCustomersTelephone());
        $customer->setMobile('');
        $customer->setMail($dbCustomer->getCustomersEmailAddress());
        $customer->setNewsletterSubscription($dbCustomer->getCustomersNewsletter());
        $customer->setAddresses($addresses);
        $customer->setCustomerToken($shopgateCustomer->getTokenForCustomer($customer));

        return $customer;
    }

    /**
     * Translates the Shopgate order and imports it
     * into the system
     *
     * @param ShopgateOrder $oShopgateOrder
     *
     * @return array
     * @throws ShopgateLibraryException
     */
    public function addOrder(ShopgateOrder $oShopgateOrder)
    {
        $this->di['ShopgateOrder'] = $oShopgateOrder;
        // #1: Check if the order already exists (already imported)
        // -> TABLE_ORDERS_SHOPGATE_ORDER must exist to be able to execute queries on it!
        if (!$this->tableExists(TABLE_ORDERS_SHOPGATE_ORDER)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error: \"" . TABLE_ORDERS_SHOPGATE_ORDER
                . "\" table is missing.", true
            );
        }
        $sqlQuery = "SELECT\n" . "\t`shopgateorder`.`shopgate_order_id`,\n"
            . "\t`shopgateorder`.`orders_id`,\n"
            . "\t`shopgateorder`.`shopgate_order_number`\n" . "FROM\n" . "\t`"
            . TABLE_ORDERS_SHOPGATE_ORDER . "` AS `shopgateorder`\n" . "WHERE\n"
            . "\t`shopgateorder`.`shopgate_order_number` = '"
            . $oShopgateOrder->getOrderNumber() . "'\n" . ";";

        // -> Get results
        $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);
        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting shopgate orders.", true
            );
        }

        // -> Prevent an existing order to be inserted twice (or more)
        $order = ShopgateWrapper::db_fetch_array($queryResult);
        if (!empty($order)) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DUPLICATE_ORDER);
        }

        // Get currency for the order
        $currencyCode = strtoupper($oShopgateOrder->getCurrency());
        // Load from currencies table
        $sqlQuery    = "SELECT * FROM " . TABLE_CURRENCIES . " WHERE UPPER(code) = '"
            . ShopgateWrapper::db_input($currencyCode) . "' LIMIT 1";
        $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);
        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error on selecting the currency with the currency code \"$currencyCode\".",
                true
            );
        }
        $currency = ShopgateWrapper::db_fetch_array($queryResult);
        if (!$currency) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - No currency found for the currency code \"$currencyCode\".",
                true
            );
        }

        // Create a complete order
        $statusHistoryComment     = '';
        $isShippingBlockedComment = '';
        $ordersStatusId           = 1;
        $order                    = $this->saveNewOrder(
            $oShopgateOrder, $currency, $ordersStatusId, $statusHistoryComment, $isShippingBlockedComment
        );

        // -> Save as Shopgate-order
        $this->saveShopgateOrder($oShopgateOrder, $order->getId());

        // -> Save all products that have been ordered (including their specific attributes)
        $errors         = array();
        $ordersProducts = $this->saveOrdersItems($oShopgateOrder, $order->getId(), $currency, $errors);

        // -> Save the total amount
        $orderTotals = $this->saveOrdersTotal($oShopgateOrder, $order->getId(), $currency, $ordersProducts);

        // -> Save status history and corresponding comments
        $this->saveOrdersStatusHistory(
            $oShopgateOrder, $order->getId(),
            $order->getPaymentMethod() == ENTRY_PAYMENT_SHOPGATE_GENERIC ? 1 : 0,
            $ordersStatusId, $statusHistoryComment, $isShippingBlockedComment,
            $errors
        );
        if ($this->config->getSendOrderConfirmationMail()) {
            $this->sendOrderConfirmationMail($order->getData(), $ordersProducts, $orderTotals, $statusHistoryComment);
        }

        return array(
            'external_order_id'     => $order->getId(),
            'external_order_number' => $order->getId()
        );
    }

    /**
     * @param array $order
     * @param array $products
     * @param array $orderTotals
     * @param string $statusHistoryComment
     */
    public function sendOrderConfirmationMail($order, $products, $orderTotals, $statusHistoryComment)
    {
        $currencies     = $this->di->make('Shopgate_Helpers_NativeCurrencies');
        $ordersProducts = "";

        foreach ($products as $product) {
            $displayPrice = $currencies->calculate_price(
                $product['final_price'], $product['products_tax'], $product['products_quantity']
            );

            $displayPrice = $currencies->format($displayPrice, 0, $order['currency']);
            $ordersProducts .= $product['products_quantity'] . ' x ' . $product['products_name'] . ' ('
                . $product['products_model'] . ') = ' . $displayPrice . "\n";
        }
        $emailOrder = STORE_NAME . "\n" .
            EMAIL_SEPARATOR . "\n" .
            EMAIL_TEXT_ORDER_NUMBER . ' ' . $order['orders_id'] . "\n" .
            EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(
                FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $order['orders_id'], 'SSL', false
            ) . "\n" .
            EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
        if (!empty($statusHistoryComment)) {
            $emailOrder .= tep_db_output($statusHistoryComment) . "\n\n";
        }
        $emailOrder .= EMAIL_TEXT_PRODUCTS . "\n" .
            EMAIL_SEPARATOR . "\n" .
            $ordersProducts .
            EMAIL_SEPARATOR . "\n";

        foreach ($orderTotals as $orderTotal) {
            $emailOrder .= strip_tags($orderTotal['title']) . ' ' . strip_tags($orderTotal['text']) . "\n";
        }

        if ($order->content_type != 'virtual') {
            $emailOrder .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                EMAIL_SEPARATOR . "\n";
            $deliveryAddress = array(
                'firstname'      => $order['delivery_firstname'],
                'lastname'       => $order['delivery_lastname'],
                'company'        => $order['delivery_company'],
                'street_address' => $order['delivery_street_address'],
                'suburb'         => $order['delivery_suburb'],
                'city'           => $order['delivery_city'],
                'postcode'       => $order['delivery_postcode'],
                'state'          => $order['delivery_state'],
                'zone_id'        => $order['delivery_zone_id'],
                'country_id'     => $order['delivery_country_id'],
            );
            $emailOrder .= tep_address_format($order['customers_address_format_id'], $deliveryAddress, false, '', "\n")
                . "\n\n";
        }

        $billingAddress = array(
            'firstname'      => $order['billing_firstname'],
            'lastname'       => $order['billing_lastname'],
            'company'        => $order['billing_company'],
            'street_address' => $order['billing_street_address'],
            'suburb'         => $order['billing_suburb'],
            'city'           => $order['billing_city'],
            'postcode'       => $order['billing_postcode'],
            'state'          => $order['billing_state'],
            'zone_id'        => $order['billing_zone_id'],
            'country_id'     => $order['billing_country_id'],
        );
        $emailOrder .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
            EMAIL_SEPARATOR . "\n";
        $emailOrder .= tep_address_format($order['customers_address_format_id'], $billingAddress, false, '', "\n")
            . "\n\n";
        $emailOrder .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
            EMAIL_SEPARATOR . "\n";

        $emailOrder .= $order['payment_method'] . "\n\n";
        tep_mail(
            $order['customers_name'], $order['customers_email_address'],
            EMAIL_TEXT_SUBJECT, $emailOrder, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS
        );
    }

    /**
     * @inheritdoc
     */
    public function updateOrder(ShopgateOrder $order)
    {
        // save UTF-8 payment infos (to build proper json)
        $paymentInfosUtf8 = $order->getPaymentInfos();
        // data needs to be utf-8 decoded for äöüß and the like to be saved correctly
        /** @var ShopgateOrder $order */
        $order = $order->utf8Decode($this->config->getEncoding());

        $qry = "SELECT
                    o.*,
                    so.shopgate_order_id,
                    so.shopgate_order_number,
                    so.is_paid,
                    so.is_shipping_blocked,
                    so.payment_infos
                FROM " . TABLE_ORDERS . " o
                INNER JOIN " . TABLE_ORDERS_SHOPGATE_ORDER . " so ON (so.orders_id = o.orders_id)
                WHERE so.shopgate_order_number = '{$order->getOrderNumber()}'";

        $result  = ShopgateWrapper::db_query_print_on_err($qry);
        $dbOrder = ShopgateWrapper::db_fetch_array($result);

        if ($dbOrder == false) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_ORDER_NOT_FOUND,
                "Shopgate order number: '{$order->getOrderNumber()}'."
            );
        }

        $errorOrderStatusIsSent                     = false;
        $errorOrderStatusAlreadySet                 = array();
        $statusShoppingsystemOrderIsPaid            = $dbOrder['is_paid'];
        $statusShoppingsystemOrderIsShippingBlocked = $dbOrder['is_shipping_blocked'];
        $status                                     = $dbOrder["orders_status"];

        // check if shipping is already done, then throw at end of method a OrderStatusIsSent - Exception
        if ($status == $this->config->getOrderStatusShipped()
            && ($statusShoppingsystemOrderIsShippingBlocked
                || $order->getIsShippingBlocked())
        ) {
            $errorOrderStatusIsSent = true;
        }

        if ($order->getUpdatePayment() == 1) {
            if (!is_null($statusShoppingsystemOrderIsPaid)
                && $order->getIsPaid() == $statusShoppingsystemOrderIsPaid
                && !is_null($dbOrder['payment_infos'])
                && $dbOrder['payment_infos'] == $this->jsonEncode(
                    $paymentInfosUtf8
                )
            ) {
                $errorOrderStatusAlreadySet[] = 'payment';
            }

            if (is_null($statusShoppingsystemOrderIsPaid) || $order->getIsPaid() != $statusShoppingsystemOrderIsPaid) {
                $orderStatus = array();
                if ($order->getIsPaid()) {
                    $orderStatusComment = ENTRY_NEW_PAYMENT_STATUS_IS_PAID_COMMENT_TEXT;
                } else {
                    $orderStatusComment = ENTRY_NEW_PAYMENT_STATUS_IS_NOT_PAID_COMMENT_TEXT;
                };

                $orderStatus['comments'] = $this->stringFromUtf8($orderStatusComment, $this->config->getEncoding());

                $this->saveSingleOrdersStatusHistory(
                    $dbOrder['orders_id'], $status, $orderStatusComment,
                    date('Y-m-d H:i:s')
                );

                // update the shopgate order status information
                $ordersShopgateOrder = array(
                    "is_paid"  => (int)$order->getIsPaid(),
                    "modified" => "now()",
                );
                ShopgateWrapper::db_execute_query(
                    TABLE_ORDERS_SHOPGATE_ORDER, $ordersShopgateOrder, "update",
                    "shopgate_order_id = {$dbOrder['shopgate_order_id']}"
                );

                // Save status in order
                $orderData                  = array();
                $orderData["orders_status"] = $status;
                $orderData["last_modified"] = date('Y-m-d H:i:s');

                ShopgateWrapper::db_execute_query(
                    TABLE_ORDERS, $orderData, "update",
                    "orders_id = {$dbOrder['orders_id']}"
                );
            }

            // update payment infos
            if (!is_null($dbOrder['payment_infos'])
                && $dbOrder['payment_infos'] != $this->jsonEncode(
                    $paymentInfosUtf8
                )
            ) {
                $dbPaymentInfos = $this->jsonDecode($dbOrder['payment_infos'], true);
                $paymentInfos   = $order->getPaymentInfos();
                $histories      = array();

                switch ($order->getPaymentMethod()) {
                    case ShopgateOrder::SHOPGATE:
                    case ShopgateOrder::INVOICE:
                    case ShopgateOrder::COD:
                        break;
                    case ShopgateOrder::PREPAY:
                        if (isset($dbPaymentInfos['purpose'])
                            && $paymentInfos['purpose']
                            != $dbPaymentInfos['purpose']
                        ) {
                            $comments = $this->stringFromUtf8(
                                ENTRY_PAYMENT_UPDATED
                                . ENTRY_PAYMENT_PREPAY_PAYMENT_PURPOSE . ' '
                                . $paymentInfos['purpose'],
                                $this->config->getEncoding()
                            );

                            // Order is not paid yet
                            $histories[] = array(
                                "orders_id"         => $dbOrder["orders_id"],
                                "orders_status_id"  => $status,
                                "date_added"        => date('Y-m-d H:i:s'),
                                "customer_notified" => false,
                                "comments"          => ShopgateWrapper::db_prepare_input($comments)
                            );
                        }
                        break;
                    case ShopgateOrder::DEBIT:
                        // Save additional information into status history comments
                        $newPaymentMethodExtraInfo = '';
                        $newPaymentMethodExtraInfo .= ENTRY_PAYMENT_ELV_INFOTEXT
                            . "\n";
                        $newPaymentMethodExtraInfo
                            .= ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_OWNER . " "
                            . $paymentInfos["bank_account_holder"] . "\n";
                        $newPaymentMethodExtraInfo
                            .= ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_NUMBER . " "
                            . $paymentInfos["bank_account_number"] . "\n";
                        $newPaymentMethodExtraInfo
                            .= ENTRY_PAYMENT_ELV_TEXT_BANK_CODE . " "
                            . $paymentInfos["bank_code"] . "\n";
                        $newPaymentMethodExtraInfo
                            .= ENTRY_PAYMENT_ELV_TEXT_BANK_NAME . " "
                            . $paymentInfos["bank_name"] . "\n";

                        $comments = $this->stringFromUtf8(
                            ENTRY_PAYMENT_UPDATED . $newPaymentMethodExtraInfo,
                            $this->config->getEncoding()
                        );
                        $comments .= $this->_createPaymentInfos(
                            $paymentInfos, $dbOrder['orders_id'], $status, false
                        );

                        $histories[]
                            = array(
                            "orders_id"         => $dbOrder["orders_id"],
                            "orders_status_id"  => $status,
                            "date_added"        => date('Y-m-d H:i:s'),
                            "customer_notified" => false,
                            "comments"          => ShopgateWrapper::db_prepare_input(
                                $comments
                            )
                        );

                        break;
                    case ShopgateOrder::PAYPAL:
                        // Save payment infos in history
                        $history             =
                            $this->_createPaymentInfos($paymentInfos, $dbOrder["orders_id"], $status);
                        $history['comments'] = $this->stringFromUtf8(
                                ENTRY_PAYMENT_UPDATED,
                                $this->config->getEncoding()
                            ) . $history['comments'];
                        $histories[]         = $history;
                        break;
                    default:
                        // mobile_payment
                        // Save payment infos in history
                        $history             = $this->_createPaymentInfos(
                            $paymentInfos, $dbOrder["orders_id"], $status
                        );
                        $history['comments'] = $this->stringFromUtf8(
                                ENTRY_PAYMENT_UPDATED,
                                $this->config->getEncoding()
                            ) . $history['comments'];
                        $histories[]         = $history;
                        break;
                }

                foreach ($histories as $history) {
                    $this->saveSingleOrdersStatusHistory(
                        $history['orders_id'], $history['orders_status_id'],
                        $history['comments'], $history['date_added']
                    );
                }
            }

            $ordersShopgateOrder = array(
                "payment_infos" => $this->jsonEncode($paymentInfosUtf8),
                "modified"      => "now()"
            );

            ShopgateWrapper::db_execute_query(
                TABLE_ORDERS_SHOPGATE_ORDER, $ordersShopgateOrder, 'update',
                "shopgate_order_id = {$dbOrder['shopgate_order_id']}"
            );
        }

        if ($order->getUpdateShipping() == 1) {
            if (!is_null($statusShoppingsystemOrderIsShippingBlocked)
                && $order->getIsShippingBlocked()
                == $statusShoppingsystemOrderIsShippingBlocked
            ) {
                $errorOrderStatusAlreadySet[] = 'shipping';
            } else {
                if ($status != $this->config->getOrderStatusShipped()) {
                    if ($order->getIsShippingBlocked() == 1) {
                        $status = $this->config->getOrderStatusShippingBlocked();
                    } else {
                        $status = $this->config->getOrderStatusOpen();
                    }
                }

                $orderStatus                      = array();
                $orderStatus["orders_id"]         = $dbOrder["orders_id"];
                $orderStatus["date_added"]        = date('Y-m-d H:i:s');
                $orderStatus["customer_notified"] = false;
                $orderStatus['orders_status_id']  = $status;

                if ($order->getIsShippingBlocked() == 0) {
                    $orderStatus["comments"] = ENTRY_NEW_SHIPPING_STATUS_SHIPPING_APPROVED_COMMENT_TEXT;
                } else {
                    $orderStatus['comments'] = ENTRY_NEW_SHIPPING_STATUS_SHIPPING_BLOCKED_COMMENT_TEXT;
                }

                $orderStatus['comments'] = $this->stringFromUtf8(
                    $orderStatus['comments'], $this->config->getEncoding()
                );

                $this->saveSingleOrdersStatusHistory(
                    $orderStatus['orders_id'], $orderStatus['orders_status_id'],
                    $orderStatus['comments'], $orderStatus['date_added']
                );

                $ordersShopgateOrder = array(
                    "is_shipping_blocked" => (int)$order->getIsShippingBlocked(),
                    "modified"            => "now()"
                );

                ShopgateWrapper::db_execute_query(
                    TABLE_ORDERS_SHOPGATE_ORDER, $ordersShopgateOrder, 'update',
                    "shopgate_order_id = {$dbOrder['shopgate_order_id']}"
                );

                // Save status in order
                $orderData                  = array();
                $orderData["orders_status"] = $status;
                $orderData["last_modified"] = date('Y-m-d H:i:s');
                ShopgateWrapper::db_execute_query(
                    TABLE_ORDERS,
                    $orderData,
                    'update',
                    "orders_id = {$dbOrder['orders_id']}"
                );
            }
        }

        if ($errorOrderStatusIsSent) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_ORDER_STATUS_IS_SENT
            );
        }

        if (!empty($errorOrderStatusAlreadySet)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_ORDER_ALREADY_UP_TO_DATE,
                implode(',', $errorOrderStatusAlreadySet), true
            );
        }

        return array(
            'external_order_id'     => $dbOrder["orders_id"],
            'external_order_number' => $dbOrder["orders_id"]
        );
    }

    /**
     * @inheritdoc
     */
    public function createPluginInfo()
    {
        $aInfos = $this->executeLoaders($this->_getPluginInfoLoaders(), array(), null);

        return $aInfos;
    }

    /**
     * @inheritdoc
     */
    public function getSettings()
    {
    // fake customer group as OsCommerce doesn't have such a feature
        $customerGroup                 = array(
            'id'                     => 1,
            'name'                   => 'default',
            'is_default'             => 1,
            'customer_tax_class_key' => 'default',
        );

        $customerTaxClass = array(
            'id'         => "1",
            'key'        => 'default',
            'is_default' => "1");

        // Tax rates are pretty much a combination of tax rules and tax rates in osCommerce. So we're using them to generate both:
        $oscTaxRates = $this->getTaxRates();
        $taxRates    = array();
        $taxRules    = array();
        foreach ($oscTaxRates as $oscTaxRate) {
            $description = empty($oscTaxRate['tax_description'])
                ? $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rate']
                : $oscTaxRate['tax_description'];
            // build and append tax rate
            $taxRates[] = array(
                'id'            => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                'key'           => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                'display_name'  => $description,
                'tax_percent'   => $oscTaxRate['tax_rate'],
                'country'       => (!empty($oscTaxRate['countries_iso_code_2'])) ? $oscTaxRate['countries_iso_code_2'] : '',
                'state'         => (!empty($oscTaxRate['countries_iso_code_2']) && !empty($oscTaxRate['zone_code']))
                    ? ShopgateMapper::getShopgateStateCode(
                        $oscTaxRate['countries_iso_code_2'], $oscTaxRate['zone_code']
                    )
                    : '',
                'zip_code_type' => 'all',
            );

            // build and append tax rule
            if (!empty($taxRules[$oscTaxRate['tax_rates_id']])) {
                $taxRules[$oscTaxRate['tax_rates_id']]['tax_rates'][] = array(
                    // one rate per rule (since rates are in fact also rules) in xtModified
                    'id'  => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                    'key' => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                );
            } else {
                $taxRules[$oscTaxRate['tax_rates_id']] = array(
                    'id'                   => $oscTaxRate['tax_rates_id'],
                    'name'                 => $description,
                    'priority'             => $oscTaxRate['tax_priority'],
                    'product_tax_classes'  => array(
                        array(
                            'id'  => $oscTaxRate['tax_class_id'],
                            'key' => $oscTaxRate['tax_class_title'],
                        )
                    ),
                    'customer_tax_classes' => array(
                        array(
                            'id' => 1,
                            'key' => 'default'
                        )
                    ),
                    'tax_rates'            => array(
                        array(
                            'id'  => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                            'key' => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                        )
                    ),
                );
            }
        }

        return array(
            'customer_groups' => array($customerGroup),
            'tax'             => array(
                'product_tax_classes'  => $this->getTaxClasses(),
                'customer_tax_classes' => array($customerTaxClass),
                'tax_rates'            => $taxRates,
                'tax_rules'            => $taxRules,
            ),
        );
    }

    /**
     * @inheritdoc
     */
    public function createShopInfo()
    {
        $shopInfo = array();

        $productCountQuery      = "SELECT count(*) cnt FROM " . TABLE_PRODUCTS . " as p WHERE p.products_status = 1";
        $result                 = ShopgateWrapper::db_query_print_on_err($productCountQuery);
        $row                    = ShopgateWrapper::db_fetch_array($result);
        $shopInfo['item_count'] = $row['cnt'];

        $catQry                     = "SELECT count(*) cnt FROM " . TABLE_CATEGORIES;
        $result                     = ShopgateWrapper::db_query_print_on_err($catQry);
        $row                        = ShopgateWrapper::db_fetch_array($result);
        $shopInfo['category_count'] = $row['cnt'];

        $reviewColumnExistQuery
                = "SELECT count(1) AS cnt FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE TABLE_SCHEMA = '" . DB_DATABASE
            . "' AND TABLE_NAME = '" . TABLE_REVIEWS
            . "' AND COLUMN_NAME = 'reviews_status'";
        $result = ShopgateWrapper::db_query_print_on_err($reviewColumnExistQuery);
        $row    = ShopgateWrapper::db_fetch_array($result);

        $revQry                   = "SELECT COUNT(*) AS cnt FROM " . TABLE_REVIEWS . " as r "
            . ($row["cnt"] > 0 ? " WHERE r.reviews_status = 1" : "");
        $result                   = ShopgateWrapper::db_query_print_on_err($revQry);
        $row                      = ShopgateWrapper::db_fetch_array($result);
        $shopInfo['review_count'] = $row['cnt'];

        // Not provided by Osc
        $shopInfo['plugins_installed '] = array();

        return $shopInfo;
    }

    /**
     * @inheritdoc
     */
    public function checkCart(ShopgateCart $cart)
    {
        global $customer_id;
        /** @var Shopgate_Helpers_Coupon $couponObject */
        $couponObject               = $this->di->make('Shopgate_Helpers_Coupon');
        $customer_id                = $cart->getExternalCustomerId();
        $result['external_coupons'] = $couponObject->checkCoupon($cart);
        $result['shipping_methods'] = $this->getShipping($cart);
        $result['currency']         = $this->config->getCurrency();
        $result['items']            = $this->checkValidityOfCartItems($cart);

        return $result;
    }

    /**
     * Used to reserve a coupon as soon as the user checks out
     *
     * @param ShopgateCart $cart
     *
     * @return array
     */
    public function redeemCoupons(ShopgateCart $cart)
    {
        /** @var Shopgate_Helpers_Coupon $couponObject */
        $couponObject = $this->di->make('Shopgate_Helpers_Coupon');
        $coupons      = $couponObject->checkCoupon($cart);

        foreach ($coupons as $coupon) {
            if ($coupon->getIsValid()) {
                if (!$couponObject->redeemValidCouponByCode($coupon->getCode())) {
                    $coupon->setIsValid(false);
                    $coupon->setNotValidMessage('Coupon\'s maximum usage reached');
                }
            }
        }

        return $coupons;
    }

    /**
     * @param string $jobname
     * @param        $params
     * @param string $message
     * @param int    $errorcount
     *
     * @throws ShopgateLibraryException
     */
    public function cron($jobname, $params, &$message, &$errorcount)
    {
        switch (strtoupper($jobname)) {
            case 'SET_SHIPPING_COMPLETED':
                $this->cronSetOrdersShippingCompleted($message, $errorcount);
                break;
            case 'CANCEL_ORDERS':
                $this->cronCancelOrder($message, $errorcount);
                break;
            default:
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_CRON_UNSUPPORTED_JOB,
                    'Job name: "' . $jobname . '"', true
                );
        }
    }

    /**
     * @inheritdoc
     */
    public function checkStock(ShopgateCart $cart)
    {
        return $this->checkValidityOfCartItems($cart);
    }

    protected function createMediaCsv()
    {
        // TODO: Implement createMediaCsv() method.
    }

    /**
     * Retrieves a list of external orders
     *
     * @param string $customerToken
     * @param string $customerLanguage - not currently used
     * @param int    $limit
     * @param int    $offset
     * @param string $orderDateFrom
     * @param string $sortOrder
     *
     * @return ShopgateExternalOrder[]
     * @throws ShopgateLibraryException
     */
    public function getOrders(
        $customerToken, $customerLanguage, $limit = 10, $offset = 0, $orderDateFrom = '', $sortOrder = 'created_desc'
    ) {
        /** @var Shopgate_Models_Orders_Shopgate $orderObject */
        $orderObject = $this->di->make('Shopgate_Models_Orders_Shopgate');

        return $orderObject->getCustomerOrders(
            $customerToken, $limit, $offset, $orderDateFrom, $sortOrder
        );
    }

    /**
     * @inheritdoc
     */
    public function syncFavouriteList($customerToken, $items)
    {
        // TODO: Implement syncFavouriteList() method.
    }

    /**
     * @param        $orderId
     * @param int    $ordersStatusId
     * @param string $commentText
     * @param string $forcedDate
     *
     * @return bool
     * @throws ShopgateLibraryException
     */
    private function saveSingleOrdersSatusHistory($orderId, $ordersStatusId = 1, $commentText = '', $forcedDate = '')
    {
        if (empty($forcedDate)) {
            $forcedDate = 'now()';
        }
        $ordersStatusHistory = array(
            'orders_id'         => $orderId, 'orders_status_id' => $ordersStatusId, 'date_added' => $forcedDate,
            'customer_notified' => 0, 'comments' => $this->stringFromUtf8($commentText, $this->config->getEncoding()),
        );

        // Save entry to DB
        $queryResult = ShopgateWrapper::db_execute_query(TABLE_ORDERS_STATUS_HISTORY, $ordersStatusHistory);
        if (!$queryResult) {
            // DB-Error
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error on inserting an orders status history entry.", true
            );
        }

        return true;
    }

    /**
     * Checks if the given table exists
     *
     * @param string $tableName
     *
     * @throws ShopgateLibraryException
     * @return boolean
     */
    private function tableExists($tableName)
    {
        $tableName = trim($tableName);
        if (empty($tableName)) {
            return false;
        }

        // Get all table names
        $query = ShopgateWrapper::db_query("SHOW TABLES");
        if (!$query) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error checking for table \"$tableName\".",
                true
            );
        }
        while ($array = ShopgateWrapper::db_fetch_array($query)) {
            $array = array_values($array);

            // Check for table name
            if ($array[0] == $tableName) {
                return true;
            }
        }

        // The requested table has not been found if execution reaches here
        return false;
    }

    /**
     * Takes a shopgate order from the library (using Merchant API [2.0]) and creates a new dataset for that order.
     * After saving it to the db the full order dataset including the id is returned as an array.
     * The parameters $statusHistoryComment and $isShippingBlockedComment will be filled with the information that is needed
     * to be put to the order status history comment
     *
     * @param ShopgateOrder $oShopgateOrder (UTF-8 encoded data)
     * @param               $currency
     * @param               $ordersStatusId
     * @param :out $statusHistoryComment
     * @param :out $isShippingBlockedComment
     *
     * @return Shopgate_Models_Orders_Native
     * @throws ShopgateLibraryException
     */
    private function saveNewOrder(
        ShopgateOrder $oShopgateOrder, $currency, &$ordersStatusId, &$statusHistoryComment, &$isShippingBlockedComment
    ) {
        // Get the customer and his address (create a dataset if there is no customer found)
        $customerData = $this->getCustomerData($oShopgateOrder);
        // getCustomerData returns a 2-dimensional array: array('customer' => $dbCustomerData, 'customers_address' => $dbCustomersAddress)

        // Get a valid customers email address to save for the order (guest users get a "guest user" email address, so here the real email address has to be taken)
        if (empty($customerData['customer']['customers_email_address'])
            || $customerData['customer']['customers_email_address'] == $this->createGuestUserEmail(
                $oShopgateOrder->getOrderNumber()
            )
        ) {
            $customersEmailAddress = $oShopgateOrder->getMail(); // Take the email address given by shopgate
        } else {
            $customersEmailAddress = $customerData['customer']['customers_email_address'];
        }

        // -> Get the addresses (in UTF-8 format) and convert to the intern format
        /** @var ShopgateAddress $billingAddress */
        /** @var ShopgateAddress $deliveryAddress */
        $billingAddress    = $oShopgateOrder->getInvoiceAddress()->utf8Decode($this->config->getEncoding());
        $deliveryAddress   = $oShopgateOrder->getDeliveryAddress()->utf8Decode($this->config->getEncoding());
        $billingStateCode  = $billingAddress->getState();
        $deliveryStateCode = $deliveryAddress->getState();

        // -> Get delivery and billing zone and country (do only multiple selects if the two addresses differ)
        $billingCountry = $this->getCountry(
            $billingAddress->getCountry(),
            array('countries_id', 'countries_name', 'address_format_id')
        );

        if (empty($billingCountry)) {
            // Missing database-entry
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_UNKNOWN_COUNTRY_CODE,
                "Shopgate Plugin - No country found with the iso-code-2 \""
                . $billingAddress->getCountry() . "\".", true
            );
        }

        $billingStateZone = array();

        if (!empty($billingStateCode)) {
            $billingStateZone = $this->getStateZone(
                $billingCountry['countries_id'], $billingStateCode,
                array('zone_id', 'zone_name')
            );
        }

        if ($billingAddress->getCountry() == $deliveryAddress->getCountry()) {
            // Countries are identical, so just take the same country
            $deliveryCountry = $billingCountry;

            // The state can still be different
            if ($billingStateCode == $deliveryStateCode) {
                $deliveryStateZone = $billingStateZone;
            } else {
                $deliveryStateZone = array();
                if (!empty($deliveryStateCode)) {
                    $deliveryStateZone = $this->getStateZone(
                        $deliveryCountry['countries_id'], $deliveryStateCode,
                        array('zone_id', 'zone_name')
                    );
                }
            }
        } else {
            // Countries differ
            $deliveryCountry = $this->getCountry(
                $deliveryAddress->getCountry(),
                array('countries_id', 'countries_name', 'address_format_id')
            );
            if (empty($deliveryCountry)) {
                // Missing database-entry
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_UNKNOWN_COUNTRY_CODE,
                    "Shopgate Plugin - No country found with the iso-code-2 \""
                    . $deliveryAddress->getCountry() . "\".", true
                );
            }

            // Different country means ALWAYS a different state!
            $deliveryStateZone = array();
            if (!empty($deliveryStateCode)) {
                $deliveryStateZone = $this->getStateZone(
                    $deliveryCountry['countries_id'], $deliveryStateCode,
                    array('zone_id', 'zone_name')
                );
            }
        }

        // -> Get the customers country data
        if (!empty($customerData['customers_address'])) {
            $customersCountry = $this->getCountry(
                null /* get by countries_id, instead of isoCode2 */,
                array('countries_id', 'countries_name', 'address_format_id'),
                $customerData['customers_address']['entry_country_id']
            );
        }
        // -> Fallback for the case if no country is found
        if (empty($customersCountry)) {
            // Default the country to "German" or "United States" if none set (depending on plugin version)
            if (strpos(SHOPGATE_PLUGIN_VERSION, 'usa') === false) {
                $customersCountry
                    = array(
                    'countries_id'      => '81', 'countries_name' => 'Germany',
                    'address_format_id' => '5'
                );
            } else {
                $customersCountry = array(
                    'countries_id'      => '223',
                    'countries_name'    => 'United States',
                    'address_format_id' => '2'
                );
            }
        }

        // -> set orders status
        //    =>    1 = Pending;
        //    =>    2 = Processing;
        //    =>    3 = Delivered;
        //        There are two possible status that can be valid, depending on if the shipping is blocked or not
        if ($oShopgateOrder->getIsShippingBlocked()) {
            $ordersStatusId = $this->config->getOrderStatusShippingBlocked();
        } else {
            $ordersStatusId = $this->config->getOrderStatusOpen();
        }

        // On default "pending" status there is more information needed, that is set es comment to the status history
        $isShippingBlockedComment = '';

        if ($oShopgateOrder->getIsShippingBlocked()) {
            $isShippingBlockedComment = ENTRY_SHIPPING_BLOCKED_COMMENT_TEXT . "\n";
        } else {
            $isShippingBlockedComment = ENTRY_SHIPPING_NOT_BLOCKED_COMMENT_TEXT . "\n";
        }

        // Data is encoded using latin-1 here
        $customersName = $customerData['customers_address']['entry_firstname'] . ' '
            . $customerData['customers_address']['entry_lastname'];
        /** @var Shopgate_Models_Orders_Native $order */
        $order = $this->di->make('Shopgate_Models_Orders_Native');
        $order->setCustomersId($customerData['customer']['customers_id'])
            ->setCustomersName($customersName)
            ->setCustomersCompany($customerData['customers_address']['entry_company'])
            ->setCustomersStreetAddress($customerData['customers_address']['entry_street_address'])
            ->setCustomersSuburb($customerData['customers_address']['entry_suburb'])
            ->setCustomersCity($customerData['customers_address']['entry_city'])
            ->setCustomersPostcode($customerData['customers_address']['entry_postcode'])
            ->setCustomersState($customerData['customers_address']['entry_state'])
            ->setCustomersCountry($customersCountry['countries_name'])
            ->setCustomersTelephone($customerData['customer']['customers_telephone'])
            ->setCustomersEmailAddress($customersEmailAddress)
            ->setCustomersAddressFormatId($customersCountry['address_format_id'])
            // -> Orders delivery address
            ->setDeliveryName($deliveryAddress->getFirstName() . ' ' . $deliveryAddress->getLastName())
            ->setDeliveryCompany($deliveryAddress->getCompany())
            ->setDeliveryStreetAddress(
                $deliveryAddress->getStreet1()
                . (strlen($deliveryAddress->getStreet2()) > 0 ? (' '. $deliveryAddress->getStreet2()) : ''))
            ->setDeliverySuburb('') // Unsupported
            ->setDeliveryCity($deliveryAddress->getCity())
            ->setDeliveryPostcode($deliveryAddress->getZipcode())
            ->setDeliveryState((!empty($deliveryStateZone) ? $deliveryStateZone['zone_name'] : ""))
            ->setDeliveryCountry($deliveryCountry['countries_name'])
            ->setDeliveryAddressFormatId($deliveryCountry['address_format_id'])
            // -> Orders billing address
            ->setBillingName($billingAddress->getFirstName() . ' ' . $billingAddress->getLastName())
            ->setBillingCompany($billingAddress->getCompany())
            ->setBillingStreetAddress(
                $billingAddress->getStreet1()
                . (strlen($billingAddress->getStreet2()) > 0 ? (' '. $billingAddress->getStreet2()) : ''))
            ->setBillingSuburb('') // Unsupported
            ->setBillingCity($billingAddress->getCity())
            ->setBillingPostcode($billingAddress->getZipcode())
            ->setBillingState((!empty($billingStateZone) ? $billingStateZone['zone_name'] : ""))
            ->setBillingCountry($billingCountry['countries_name'])
            ->setBillingAddressFormatId($billingCountry['address_format_id']);

        // -> Payment information
        $statusHistoryComment = "";
        $paymentInfo          = $oShopgateOrder->getPaymentInfos();
        $paymentMethod        = $oShopgateOrder->getPaymentMethod();

        $paymentName         = '';
        $paymentWasMapped    = false;
        $paymentMapping      = array();

        $paymentMappingStrings = explode(';', $this->config->getPaymentNameMapping());
        foreach ($paymentMappingStrings as $paymentMappingString) {
            $paymentMappingArray = explode('=', $paymentMappingString);
            if (isset($paymentMappingArray[1])) {
                $paymentMapping[$paymentMappingArray[0]] = $paymentMappingArray[1];
            }
        }
        if (isset($paymentMapping[$paymentMethod])) {
            $paymentName        = $paymentMapping[$paymentMethod];
            $paymentWasMapped   = true;
        }

        switch ($paymentMethod) {
            case ShopgateOrder::SHOPGATE:
                $paymentMethodName = $paymentWasMapped ? $paymentName : ENTRY_PAYMENT_SHOPGATE;
                break;
            case ShopgateOrder::PREPAY:
                $paymentMethodName = $paymentWasMapped ? $paymentName : ENTRY_PAYMENT_PREPAY;
                // Save additional information into order status history comment
                $statusHistoryComment .= ENTRY_PAYMENT_PREPAY_PAYMENT_PURPOSE . ' ' . $paymentInfo['purpose'] . "\n";
                break;
            case ShopgateOrder::DEBIT:
                // -> not directly supported by osCommerce; use a generic import method
                $paymentMethodName = $paymentWasMapped ? $paymentName : ENTRY_PAYMENT_SHOPGATE_GENERIC;
                // Save additional information into status history comments
                $statusHistoryComment .= ENTRY_PAYMENT_ELV . ":\n\n";
                $statusHistoryComment .= ENTRY_PAYMENT_ELV_INFOTEXT . "\n\n";
                $statusHistoryComment
                    .= ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_OWNER . " " . $paymentInfo["bank_account_holder"] . "\n";
                $statusHistoryComment
                    .= ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_NUMBER . " " . $paymentInfo["bank_account_number"] . "\n";
                $statusHistoryComment .= ENTRY_PAYMENT_ELV_TEXT_BANK_CODE . " " . $paymentInfo["bank_code"] . "\n";
                $statusHistoryComment .= ENTRY_PAYMENT_ELV_TEXT_BANK_NAME . " " . $paymentInfo["bank_name"] . "\n";
                break;
            case ShopgateOrder::COD:
                $paymentMethodName = $paymentWasMapped ? $paymentName : ENTRY_PAYMENT_CASH_ON_DELIVERY;
                break;
            default:
                // Use a generic payment type for all other payment methods, that are not directly supported by shoppingsystem
                $paymentMethodName = $paymentWasMapped ? $paymentName : ENTRY_PAYMENT_SHOPGATE_GENERIC;
                // Put all available information as comment into the order status

                $statusHistoryComment .= $this->_createPaymentInfoComment($paymentInfo);
                break;
        }
        $order->setPaymentMethod($paymentMethodName)
            ->setLastModified(date('Y-m-d H:i:s'))
            ->setDatePurchased(date('Y-m-d H:i:s', strtotime($oShopgateOrder->getCreatedTime())))
            ->setOrdersStatus($ordersStatusId)
            ->setCurrency(!empty($currency['code']) ? $currency['code'] : '')
            ->setCurrencyValue(!empty($currency['value']) ? $currency['value'] : 1);

        // -> Insert the order to the database
        $order->save();
        if (!$order->getId()) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error on inserting an order dataset.", true
            );
        }
        $order->setData('billing_firstname', $billingAddress->getFirstName())
            ->setData('billing_lastname', $billingAddress->getLastName())
            ->setData('billing_country_id', $billingCountry['countries_id'])
            ->setData('billing_zone_id', $billingCountry['zone_id'])
            ->setData('delivery_firstname', $deliveryAddress->getFirstName())
            ->setData('delivery_lastname', $deliveryAddress->getLastName())
            ->setData('delivery_country_id', $deliveryCountry['countries_id'])
            ->setData('delivery_zone_id', $deliveryStateZone['zone_id']);

        if ($paymentWasMapped) {
            $comments = $this->stringFromUtf8(
                sprintf(ENTRY_PAYMENT_MAPPED, $paymentMethod, $paymentMapping[$paymentMethod]),
                $this->config->getEncoding()
            );

            $this->saveSingleOrdersStatusHistory(
                 $order->getId(),
                 $ordersStatusId,
                 $comments,
                 date('Y-m-d H:i:s')
            );
        }

        return $order;
    }

    /**
     * Takes the order data and creates a customer dataset.
     * After finishing it returns all customer data for a new order to be created
     *
     * @param ShopgateOrder $oShopgateOrder (UTF-8 encoded data)
     *
     * @return array[][]
     * @throws ShopgateLibraryException
     */
    private function getCustomerData(ShopgateOrder $oShopgateOrder)
    {
        $dbCustomerData     = array();
        $externalCustomerId = $oShopgateOrder->getExternalCustomerId();
        if (!empty($externalCustomerId)) {
            // Check if there is a customer with the given id set
            $sqlQuery = "SELECT\n" . "\t*\n" . "FROM\n" . "\t" . TABLE_CUSTOMERS
                . " AS customer\n" . "WHERE\n"
                . "\tcustomer.customers_id={$externalCustomerId}\n" . ";";

            // -> Get results
            $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);
            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error selecting shopgate orders.", true
                );
            }
            $dbCustomerData = ShopgateWrapper::db_fetch_array($queryResult);
        }

        // Create a new guest-account if there is none found
        if (empty($dbCustomerData)) {
            /** @var ShopgateAddress $customerAddress */
            // Use the invoice address, since it is only possible to pass one address as user data
            $customerAddress = $oShopgateOrder->getInvoiceAddress();
            $customerAddress = $customerAddress->utf8Decode(
                $this->config->getEncoding()
            );

            // Fill the customers data
            $newCustomerData                                 = array();
            $newCustomerData['customers_gender']             = $customerAddress->getGender();
            $newCustomerData['customers_firstname']          = $customerAddress->getFirstName();
            $newCustomerData['customers_lastname']           = $customerAddress->getLastName();
            $newCustomerData['customers_dob']                = $customerAddress->getBirthday();
            $newCustomerData['customers_email_address']      = $this->createGuestUserEmail(
                $oShopgateOrder->getOrderNumber()
            );
            $newCustomerData['customers_default_address_id'] = '0'; // Unknown yet
            $newCustomerData['customers_telephone']          = $oShopgateOrder->getPhone();
            $newCustomerData['customers_fax']                = ''; // Unsupported
            $newCustomerData['customers_password']           = ''; // Stays empty. Guest customers can't log in
            $newCustomerData['customers_newsletter']         = '0'; // Do not activate newsletter

            // Create the guest-customer
            $queryResult = ShopgateWrapper::db_execute_query(TABLE_CUSTOMERS, $newCustomerData);
            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error on inserting a customer dataset.",
                    true
                );
            }

            // Set the id
            $newCustomerData['customers_id'] = ShopgateWrapper::db_insert_id();

            // Create a customers_info dataset
            $newCustomerInfo = array(
                'customers_info_id'                   => $newCustomerData['customers_id'],
                'customers_info_number_of_logons'     => 0,
                'customers_info_date_account_created' => date('Y-m-d H:i:s'),
                'global_product_notifications'        => '0'
            );
            $queryResult     = ShopgateWrapper::db_execute_query(
                TABLE_CUSTOMERS_INFO, $newCustomerInfo
            );
            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error on inserting a customers info-dataset.",
                    true
                );
            }

            // Create a new address book entry
            $newCustomersAddress                    = array();
            $newCustomersAddress['customers_id']    = $newCustomerData['customers_id'];
            $newCustomersAddress['entry_gender']    = $customerAddress->getGender();
            $newCustomersAddress['entry_company']   = $customerAddress->getCompany();
            $newCustomersAddress['entry_firstname'] = $customerAddress->getFirstName();
            $newCustomersAddress['entry_lastname']  = $customerAddress->getLastName();
            $newCustomersAddress['entry_street_address']
                                                    =
                $customerAddress->getStreet1() . (strlen($customerAddress->getStreet2()) > 0 ? (' '
                    . $customerAddress->getStreet2()) : '');
            $newCustomersAddress['entry_suburb']    = '';
            $newCustomersAddress['entry_postcode']  = $customerAddress->getZipcode();
            $newCustomersAddress['entry_city']      = $customerAddress->getCity();
            // Add additional data (country_id, zone_id and state)
            $countryData = $this->getCountry($customerAddress->getCountry(), array('countries_id'));
            if (!empty($countryData)) {
                $stateData = $this->getStateZone(
                    $countryData['countries_id'], $customerAddress->getState(), array('zone_id', 'zone_name')
                );

                $newCustomersAddress['entry_country_id'] = $countryData['countries_id'];
                if (!empty($stateData)) {
                    $newCustomersAddress['entry_state']   = $stateData['zone_name'];
                    $newCustomersAddress['entry_zone_id'] = $stateData['zone_id'];
                }
            }

            $queryResult = ShopgateWrapper::db_execute_query(TABLE_ADDRESS_BOOK, $newCustomersAddress);
            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error on inserting a customer address.",
                    true
                );
            }
            $newCustomersAddress['address_book_id'] = ShopgateWrapper::db_insert_id();

            // Set default address for new customer
            $updateCustomer = array('customers_default_address_id' => $newCustomersAddress['address_book_id']);
            $queryResult    = ShopgateWrapper::db_execute_query(
                TABLE_CUSTOMERS, $updateCustomer, 'update',
                "customers_id = $newCustomersAddress[customers_id]"
            );
            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error on updating a customers default address.",
                    true
                );
            }

            // Fill the return array
            $customerData = array(
                'customer'          => $newCustomerData,
                'customers_address' => $newCustomersAddress
            );
        } else {
            // Get customers address
            $sqlQuery = "SELECT\n" . "\t*\n" . "FROM `" . TABLE_ADDRESS_BOOK
                . "` AS `ab`\n"
                . "WHERE `ab`.`customers_id` = '{$dbCustomerData['customers_id']}'\n"
                . (!empty($dbCustomerData['customers_default_address_id'])
                    ? ("\tAND `ab`.`address_book_id` = '{$dbCustomerData['customers_default_address_id']}'\n")
                    : ("")) . ";";

            $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);

            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error on selecting the customers default address.",
                    true
                );
            }

            $dbCustomersAddress = ShopgateWrapper::db_fetch_array($queryResult);

            // Fill the return array
            $customerData = array(
                'customer'          => $dbCustomerData,
                'customers_address' => $dbCustomersAddress
            );
        }

        return $customerData;
    }

    /**
     * Creates a shopgate guest user email address based on the shopgate order number
     *
     * @param string $shopgateOrderNumber
     *
     * @return string
     */
    private function createGuestUserEmail($shopgateOrderNumber)
    {
        return "noreply+{$shopgateOrderNumber}@shopgate.com";
    }

    /**
     * Takes a country code in "ISO-3166-1 ALPHA-2" - format and an array of all needed fields and returns the
     * corresponding data that is saved in the database
     *
     * @param string $isoCode2
     * @param array  $fields
     * @param int    $id (optional)
     *
     * @throws ShopgateLibraryException
     * @return integer
     */
    private function getCountry($isoCode2, $fields = array(), $id = null)
    {
        // Get the desired fields id from databases using the given iso code
        if (empty($fields)) {
            $elements = 'country.*';
        } else {
            $elements = 'country.' . implode(",\ncountry.", $fields);
        }
        $sqlQuery
            = "SELECT\n" . "\t$elements\n" . "FROM\n" . "\t" . TABLE_COUNTRIES
            . " AS country\n" . "WHERE\n" . (!empty($id)
                ? "country.countries_id=$id"
                : "\tUPPER(country.countries_iso_code_2)='" . addslashes(
                    $isoCode2
                ) . "'\n") . ";";

        // -> Get results
        $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);
        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting country.", true
            );
        }

        return ShopgateWrapper::db_fetch_array($queryResult);
    }

    /**
     * Takes a country_id and a zone code in "ISO 3166-2" - format and an array with all fields needed and returns the
     * corresponding zone_id that is saved in the database
     *
     * @param integer $countryId
     * @param string  $shopgateZoneCode
     * @param array   $fields
     *
     * @throws ShopgateLibraryException
     * @return array
     */
    private function getStateZone($countryId, $shopgateZoneCode, $fields = array())
    {
        if (!empty($countryId) && !empty($shopgateZoneCode)) {
            // Convert the state code to system format
            $oscStateCode = ShopgateMapper::getShoppingsystemStateCode($shopgateZoneCode);

            // Load the desired fields id from the database, using the corresponding state-code and the previously selected country id
            if (empty($fields)) {
                $elements = 'zone.*';
            } else {
                $elements = 'zone.' . implode(",\nzone.", $fields);
            }
            $sqlQuery = "SELECT\n" . $elements . ' ' . "FROM\n" . "\t" . TABLE_ZONES
                . " AS zone\n" . "WHERE\n" . "\tzone.zone_code='" . addslashes($oscStateCode)
                . "'\n" . "\t\tAND\n" . "\tzone.zone_country_id=" . intval($countryId) . "\n" . ";";

            // -> Get results
            $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);
            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error selecting state zone.", true
                );
            }
            $zoneData = ShopgateWrapper::db_fetch_array($queryResult);

            return $zoneData;
        }

        // By default there is no valid zone returned
        return array();
    }

    /**
     * Creates a shopgate-order dataset and saves it to the database. After saving the dataset is returned including the inserted id.
     *
     * @param ShopgateOrder $oShopgateOrder (UTF-8 encoded data)
     * @param array         $orderId
     *
     * @throws ShopgateLibraryException
     * @return array
     */
    private function saveShopgateOrder(ShopgateOrder $oShopgateOrder, $orderId)
    {
        $shopgateOrder = array(
            'orders_id'                        => $orderId,
            'shopgate_order_number'            => $oShopgateOrder->getOrderNumber(),
            'shopgate_shop_number'             => $this->config->getShopNumber(),
            'is_paid'                          => $oShopgateOrder->getIsPaid(),
            'is_shipping_blocked'              => $oShopgateOrder->getIsShippingBlocked(),
            'order_data'                       => serialize($oShopgateOrder),
            'payment_infos'                    => $this->jsonEncode($oShopgateOrder->getPaymentInfos()),
            'is_sent_to_shopgate'              => 0, // No "shipped" request is sent on adding an order
            'is_cancellation_sent_to_shopgate' => 0,
            'created'                          => date('Y-m-d H:i:s')
        );

        // -> Insert the order to the database
        $queryResult = ShopgateWrapper::db_execute_query(TABLE_ORDERS_SHOPGATE_ORDER, $shopgateOrder);
        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error on inserting a shopgate-order dataset.",
                true
            );
        }
        $shopgateOrder['shopgate_order_id'] = ShopgateWrapper::db_insert_id();

        return $shopgateOrder;
    }

    /**
     * Adds all ordered items into the orders_products table
     *
     * @param ShopgateOrder $oShopgateOrder (UTF-8 encoded data)
     * @param int           $orderId
     * @param array         $currency       - array row from database table
     * @param array         $errors
     *
     * @return array
     * @throws ShopgateLibraryException
     */
    private function saveOrdersItems(ShopgateOrder $oShopgateOrder, $orderId, $currency, &$errors)
    {
        $errors       = array();
        $ordersItems  = array();
        $orderedItems = $oShopgateOrder->getItems();

        /**
         * @var Shopgate_Helpers_ShopgateOrder  $helper
         * @var Shopgate_Models_Products_Native $nativeModel
         * @var Shopgate_Models_Orders_Products $orderProduct
         */
        $helper = $this->di->make('Shopgate_Helpers_ShopgateOrder');
        $helper->validateItemsExist();

        foreach ($orderedItems as $singleItem) {
            $nativeModel  = $this->di->make('Shopgate_Models_Products_Native');
            $dbProduct    = $nativeModel->load($singleItem->getItemNumber());
            $orderProduct = $this->di->make('Shopgate_Models_Orders_Products');

            // Calculate the products price under consideration of all options
            $optionsUpPrice         = 0;
            $itemTaxPercentAdjusted = $singleItem->getTaxPercent() + 100;
            $itemOptions            = $singleItem->getOptions();
            if (!empty($itemOptions)) {
                foreach ($itemOptions as $itemOption) {
                    /* @var $itemOption ShopgateOrderItemOption */
                    // Remove taxes here! -> the amount is a shopgate-internal-amount (integer type)
                    $optionsUpPrice += $itemOption->getAdditionalAmountWithTax() / $itemTaxPercentAdjusted * 100;
                }
            }

            // Remove the options prices from the complete price to get the product price
            $productsPrice    = $singleItem->getUnitAmountWithTax() / $itemTaxPercentAdjusted * 100 - $optionsUpPrice;
            $currencyExchange = !empty($currency['value']) ? round($currency['value'], 6) : 1;

            /**
             * Unit amount already contains the full amount including the options price offset
             */
            $finalPrice = $singleItem->getUnitAmountWithTax() / $itemTaxPercentAdjusted * 100 * $currencyExchange;
            $orderProduct->setOrdersId($orderId)
                         ->setProductsId($singleItem->getItemNumber())
                         ->setProductsModel($singleItem->getItemNumberPublic())
                         ->setProductsName($singleItem->getName())
                         ->setProductsPrice($productsPrice * $currencyExchange)// price w/o tax & w/o options
                         ->setFinalPrice($finalPrice)
                         ->setProductsTax($singleItem->getTaxPercent())
                         ->setProductsQuantity($singleItem->getQuantity());

            // Check if price from db product is nearly the same to get a more exact value
            if ($dbProduct->getProductsPrice()
                && round($dbProduct->getProductsPrice(), 1) == round($orderProduct->getProductsPrice(), 1)
            ) {
                // Take price from database in this case
                $orderProduct->setProductsPrice($dbProduct->getProductsPrice());
                $orderProduct->setFinalPrice($dbProduct->getProductsPrice());

                // Check if the actual product has options
                if (!empty($itemOptions)) {
                    $additionalAmount = 0;
                    foreach ($singleItem->getOptions() as $itemOption) {
                        $additionalAmount += round(
                            $itemOption->getAdditionalAmountWithTax() / $itemTaxPercentAdjusted * 100
                            * $currencyExchange, 2
                        );
                    }
                    $orderProduct->setFinalPrice($orderProduct->getFinalPrice() + $additionalAmount);
                }
            }

            // Coupons and payment fees are exported as single item with a special item number
            if ($singleItem->isSgCoupon()
                || $singleItem->isPayment()
            ) {
                $orderProduct->setOrdersProductsId(0);
                $orderProduct->setProductsModel($singleItem->getItemNumber());
            } else {
                $newQty = $dbProduct->getProductsQuantity() - $singleItem->getQuantity();
                $dbProduct->setProductsQuantity($newQty);
                $dbProduct->save();
            }

            // -> Insert the order to the database
            $orderProduct->save();

            // Insert all selected options
            if (!empty($itemOptions)) {
                foreach ($singleItem->getOptions() as $itemOption) {
                    /** @var Shopgate_Models_Orders_Products_Attributes $prodOrderAttr */
                    $prodOrderAttr = $this->di->make('Shopgate_Models_Orders_Products_Attributes');
                    /* @var $itemOption ShopgateOrderItemOption */
                    $prodOrderAttr->setOrdersId($orderId)
                                  ->setOrdersProductsId($orderProduct->getId())
                                  ->setProductsOptions($itemOption->getName())
                                  ->setProductsOptionsValues($itemOption->getValue())
                                  ->setOptionsValuesPrice(
                                      round(
                                          abs($itemOption->getAdditionalAmountWithTax()) / $itemTaxPercentAdjusted * 100
                                          * $currencyExchange,
                                          2
                                      )
                                  )
                                  ->setPricePrefix($itemOption->getAdditionalAmountWithTax() < 0 ? '-' : '+')
                                  ->save();
                }
            }
            $ordersItems[] = $orderProduct;
        }

        return $ordersItems;
    }

    /**
     * Calculates all amounts to a total price and adds all needed entries to the orders_total table
     *
     * @param ShopgateOrder $oShopgateOrder (UTF-8 encoded data)
     * @param integer       $orderId
     * @param array         $currency
     * @param array         $aOrdersItems
     *
     * @throws ShopgateLibraryException
     * @return array
     */
    private function saveOrdersTotal(ShopgateOrder $oShopgateOrder, $orderId, $currency, $aOrdersItems)
    {
        $oCurrencies          = new currencies();
        $currencyCode         = !empty($currency['code']) ? $currency['code'] : '';
        $currencyExchangeRate = !empty($currency['value']) ? $currency['value'] : 1;
        $valueShipping        = $oShopgateOrder->getAmountShipping();
        $valuePaymentCosts    = $oShopgateOrder->getAmountShopPayment();
        $shippingInfos        = $oShopgateOrder->getShippingInfos();
        $valueTaxes           = array();
        $valueTaxTotal        = 0;

        // Compute all values
        foreach ($aOrdersItems as $aSingleItem) {

            $positionValue      = $aSingleItem['final_price'] * $aSingleItem['products_quantity'];
            $positionTaxPercent = $aSingleItem['products_tax'];
            $positionTax        = $positionValue * $positionTaxPercent / 100;

            // Add up tax price value only
            if (empty($valueTaxes[$positionTaxPercent])) {
                $valueTaxes[$positionTaxPercent] = 0;
            }

            $valueTaxes[$positionTaxPercent] += $positionTax * $currencyExchangeRate;
        }

        foreach ($valueTaxes as $valueTax) {
            $valueTaxTotal += $valueTax;
        }

        $valueTotal = $oShopgateOrder->getAmountComplete();

        // Use better method for calculating sub total (without tax, payment costs and shipping)
        $valueSubTotal = $valueTotal - $valueTaxTotal - $valuePaymentCosts - $valueShipping;

        $ordersTotals = array();
        $i            = 0;

        //check coupons
        if (defined("MODULE_ORDER_TOTAL_INSTALLED")
            && strpos(MODULE_ORDER_TOTAL_INSTALLED, "ot_discount_coupon.php") !== false
        ) {
            $coupons = $oShopgateOrder->getExternalCoupons();

            foreach ($coupons as $coupon) {
                $valueSubTotal -= ($coupon->getAmount());
                $amount           = ($coupon->getAmount() * (-1));
                $ordersTotals[$i] = array(
                    'orders_id'  => $orderId, 'title' => sprintf(ENTRY_COUPON, $coupon->getCode()),
                    'text'       => $oCurrencies->format($amount, false, $currencyCode),
                    'value'      => $amount,
                    'class'      => 'ot_discount_coupon',
                    'sort_order' => $i + 1,
                );
                $i++;
            }
        }

        // Add price without taxes and shipping costs
        $ordersTotals[++$i] = array(
            'orders_id'  => $orderId, 'title' => ENTRY_SUB_TOTAL,
            'text'       => $oCurrencies->format($valueSubTotal, false, $currencyCode),
            'value'      => $valueSubTotal, 'class' => 'ot_subtotal',
            'sort_order' => $i + 1,
        );

        $ordersTotals[++$i] = array(
            'orders_id'  => $orderId,
            'title'      => $shippingInfos->getDisplayName(),
            'text'       => $oCurrencies->format($valueShipping, false, $currencyCode),
            'value'      => $valueShipping,
            'class'      => 'ot_shipping',
            'sort_order' => $i + 1,
        );

        // Add payment costs
        if ($valuePaymentCosts > 0) {
            $ordersTotals[++$i]
                = array(
                'orders_id' => $orderId, 'title' => ENTRY_PAYMENT,
                'text'      => $oCurrencies->format($valuePaymentCosts, false, $currencyCode),
                'value'     => $valuePaymentCosts,
                'class'     => 'ot_payment', 'sort_order' => $i + 1,
            );
        }

        // Add taxes (separated per percent)
        foreach ($valueTaxes as $valueTaxPercent => $valueTax) {
            $ordersTotals[++$i] = array(
                'orders_id'  => $orderId, 'title' => sprintf(ENTRY_TAX, $valueTaxPercent),
                'text'       => $oCurrencies->format($valueTax, false, $currencyCode),
                'value'      => $valueTax, 'class' => 'ot_tax',
                'sort_order' => $i + 1,
            );
        }

        // Finally add the total value
        $ordersTotals[++$i]
            = array(
            'orders_id'  => $orderId, 'title' => ENTRY_TOTAL,
            'text'       => '<strong>' . ($oCurrencies->format($valueTotal, false, $currencyCode)) . '</strong>',
            'value'      => $valueTotal, 'class' => 'ot_total',
            'sort_order' => $i + 1,
        );

        // Add all lines to the database
        foreach ($ordersTotals as $key => $dbEntry) {
            $queryResult = ShopgateWrapper::db_execute_query(TABLE_ORDERS_TOTAL, $dbEntry);
            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error on inserting an orders total entry.",
                    true
                );
            }
            $ordersTotals[$key]['orders_total_id'] = ShopgateWrapper::db_insert_id();
        }

        return $ordersTotals;
    }

    /**
     * Builds status history entries out of given comment data and inserts them into the database
     *
     * @param ShopgateOrder $oShopgateOrder
     * @param string | int  $orderId
     * @param int           $isGenericPaymentInfo
     * @param int           $ordersStatusId
     * @param string        $statusHistoryComment
     * @param string        $isShippingBlockedComment
     * @param array         $errors
     * @param string        $forcedDate
     *
     * @return bool
     * @throws ShopgateLibraryException
     */
    private function saveOrdersStatusHistory(
        ShopgateOrder $oShopgateOrder, $orderId, $isGenericPaymentInfo = 0,
        $ordersStatusId = 1, $statusHistoryComment = '',
        $isShippingBlockedComment = '', $errors = array(), $forcedDate = ''
    ) {
        if (empty($forcedDate)) {
            $forcedDate = 'now()';
        }
        $ordersStatusHistory = array(
            'orders_id'         => $orderId,
            'orders_status_id'  => $ordersStatusId,
            'date_added'        => $forcedDate,
            'customer_notified' => 0,
            'comments'          => ''
        );

        // Setup comment
        if ($oShopgateOrder->getIsTest()) {
            $ordersStatusHistory['comments'] .= $this->stringFromUtf8(
                ENTRY_IS_TEST_ORDER_COMMENT_TEXT . "\n",
                $this->config->getEncoding()
            );
        }
        // -> added by shopgate comment text
        if (!empty($ordersStatusHistory['comments'])) {
            $ordersStatusHistory['comments'] .= "\n";
        }

        $ordersStatusHistory['comments'] .= $this->stringFromUtf8(
            ENTRY_ORDER_ADDED_BY_SHOPGATE_COMMENT_TEXT . "\n",
            $this->config->getEncoding()
        );

        // Insert shopgate order-number
        $shopgateOrderNumber = $oShopgateOrder->getOrderNumber();
        if (!empty($shopgateOrderNumber)) {
            $ordersStatusHistory['comments'] .= $this->stringFromUtf8(
                ENTRY_PAYMENT_SHOPGATE_ORDER_NUMBER_COMMENT_TEXT . " $shopgateOrderNumber\n"
                . "\n", $this->config->getEncoding()
            );
        }

        // Insert transaction number
        $paymentTransactionNumber = $oShopgateOrder->getPaymentTransactionNumber();

        if (!empty($paymentTransactionNumber)) {
            $ordersStatusHistory['comments'] .= $this->stringFromUtf8(
                ENTRY_PAYMENT_TRANSACTION_NUMBER_COMMENT_TEXT
                . " $paymentTransactionNumber\n", $this->config->getEncoding()
            );
        }

        // Insert Shipping method
        $shippingInfos = $oShopgateOrder->getShippingInfos();

        if (!empty($shippingInfos)) {
            $ordersStatusHistory['comments'] .= $this->stringFromUtf8(
                ENTRY_SHIPPING_METHOD_COMMENT_TEXT . " "
                . $shippingInfos->getDisplayName() . "("
                . $shippingInfos->getName() . ")\n",
                $this->config->getEncoding()
            );
        }
        // -> Shipping blocked text as the last sentence at the bottom!
        if (!empty($isShippingBlockedComment)) {
            if (!empty($ordersStatusHistory['comments'])) {
                $ordersStatusHistory['comments'] .= "\n";
            }
            $ordersStatusHistory['comments'] .= $this->stringFromUtf8(
                $isShippingBlockedComment['comments'], $this->config->getEncoding()
            );
        }

        $this->insertOrderStatusHistory($ordersStatusHistory);

        // Save payment info
        // -> Output status history comment text and corresponding errors
        if (!empty($statusHistoryComment)) {
            $ordersStatusHistory['comments'] = $this->stringFromUtf8(
                $statusHistoryComment, $this->config->getEncoding()
            );
            $this->insertOrderStatusHistory($ordersStatusHistory);
        }

        // Safe FULL payment info on non generic data (additionally); generic data already has this form of information saved already!
        if (!$isGenericPaymentInfo) {
            $paymentInfo          = $oShopgateOrder->getPaymentInfos();
            $statusHistoryComment = $this->_createPaymentInfoComment($paymentInfo);

            $ordersStatusHistory['comments'] = $this->stringFromUtf8(
                $statusHistoryComment, $this->config->getEncoding()
            );

            $this->insertOrderStatusHistory($ordersStatusHistory);
        }

        // order custom fields
        $ordersStatusHistory['comments'] = $this->stringFromUtf8(
            $this->generateCustomFieldsComment(
                $oShopgateOrder, MODULE_PAYMENT_SHOPGATE_ORDER_CUSTOM_FIELDS
            ), $this->config->getEncoding()
        );

        if (!empty($ordersStatusHistory['comments'])) {
            $this->insertOrderStatusHistory($ordersStatusHistory);
        }

        $ordersStatusHistory['comments'] = $this->stringFromUtf8(
            $this->generateCustomFieldsComment(
                $oShopgateOrder->getInvoiceAddress(),
                MODULE_PAYMENT_SHOPGATE_INVOICE_ADDRESS_CUSTOM_FIELDS
            ), $this->config->getEncoding()
        );

        if (!empty($ordersStatusHistory['comments'])) {
            $this->insertOrderStatusHistory($ordersStatusHistory);
        }

        $ordersStatusHistory['comments'] = $this->stringFromUtf8(
            $this->generateCustomFieldsComment(
                $oShopgateOrder->getDeliveryAddress(),
                MODULE_PAYMENT_SHOPGATE_DELIVERY_ADDRESS_CUSTOM_FIELDS
            ), $this->config->getEncoding()
        );

        if (!empty($ordersStatusHistory['comments'])) {
            $this->insertOrderStatusHistory($ordersStatusHistory);
        }

        // Add errors to separate status history entry
        if (!empty($errors)) {
            // Set up errors text
            $ordersStatusHistory['comments'] = ENTRY_ERRORS_EXIST;
            foreach ($errors as $err) {
                $ordersStatusHistory['comments'] .= "\n$err";
            }
            $ordersStatusHistory['comments'] = $this->stringFromUtf8(
                $ordersStatusHistory['comments'], $this->config->getEncoding()
            );
            $this->insertOrderStatusHistory($ordersStatusHistory);
        }

        return true;
    }

    /**
     * @param $ordersStatusHistory
     *
     * @throws ShopgateLibraryException
     */
    private function insertOrderStatusHistory($ordersStatusHistory)
    {
        // Save entry to DB
        $queryResult = ShopgateWrapper::db_execute_query(TABLE_ORDERS_STATUS_HISTORY, $ordersStatusHistory);

        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error on inserting an orders status history entry.",
                true
            );
        }

        sleep(1); // Make sure the comments are imported in the same order as they are listed here
    }

    /**
     * generates a comment from custom fields
     *
     * @param ShopgateOrder|ShopgateAddress $object
     * @param string                        $title
     *
     * @return string
     */
    public function generateCustomFieldsComment($object, $title = "")
    {
        if (empty($object)) {
            return "";
        }

        $comment      = "";
        $customFields = $object->getCustomFields();
        $isFirstItem  = true;
        foreach ($customFields as $customField) {
            $comment .= ((!empty($title) && $isFirstItem) ? "(" . $title . ")" : "")
                . $customField->getLabel() . ": " . $customField->getValue()
                . "\n";
            $isFirstItem = false;
        }

        return $comment;
    }

    /**
     * Creates a status history entry from one single comment text
     *
     * @param        $orderId
     * @param int    $ordersStatusId
     * @param string $commentText
     * @param string $forcedDate
     *
     * @return bool
     * @throws ShopgateLibraryException
     */
    private function saveSingleOrdersStatusHistory($orderId, $ordersStatusId = 1, $commentText = '', $forcedDate = '')
    {
        if (empty($forcedDate)) {
            $forcedDate = 'now()';
        }
        $ordersStatusHistory = array(
            'orders_id'         => $orderId,
            'orders_status_id'  => $ordersStatusId,
            'date_added'        => $forcedDate,
            'customer_notified' => 0,
            'comments'          => $this->stringFromUtf8($commentText, $this->config->getEncoding())
        );

        $queryResult = ShopgateWrapper::db_execute_query(TABLE_ORDERS_STATUS_HISTORY, $ordersStatusHistory);

        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error on inserting an orders status history entry.",
                true
            );
        }

        return true;
    }

    /**
     * @param array $paymentInfos
     *
     * @return string
     */
    private function _createPaymentInfoComment($paymentInfos, $prependKey = '')
    {
        $paymentInformation = '';
        if (is_array($paymentInfos)) {
            foreach ($paymentInfos as $key => $value) {
                if (is_array($value)) {
                    $paymentInformation .= $this->_createPaymentInfoComment($value, $key .' ');
                } else {
                    $paymentInformation .= $prependKey . $key . ': ' . $value . "\n";
                }
            }
        }
        return $paymentInformation;
    }

    /**
     * @param      $paymentInfos
     * @param      $dbOrderId
     * @param      $currentOrderStatus
     * @param bool $asArray
     *
     * @return array|string
     */
    private function _createPaymentInfos($paymentInfos, $dbOrderId, $currentOrderStatus, $asArray = true)
    {
        $paymentInformation = $this->_createPaymentInfoComment($paymentInfos);

        if ($asArray) {
            return array(
                "orders_id"         => $dbOrderId,
                "orders_status_id"  => $currentOrderStatus,
                "date_added"        => date('Y-m-d H:i:s'),
                "customer_notified" => false,
                "comments"          => ShopgateWrapper::db_prepare_input(
                    $paymentInformation
                )
            );
        } else {
            return $paymentInformation;
        }
    }

    /**
     * Creates a full csv file structure out of the given parameters and possibly some additional helper methods
     *
     * @param string $sqlQuery
     * @param string $subjectName
     * @param array  $additionalFormatterMethods
     *
     * @throws ShopgateLibraryException
     * @return boolean
     */
    private function buildCsv($sqlQuery, $subjectName, $additionalFormatterMethods = array())
    {
        if (empty($sqlQuery) || empty($subjectName)) {
            return false;
        }

        // Pluralize the subject name for proper exception output (not correct in some cases like "information", because the plural is the exact same name, a pluralizer-blacklist is needed to handle this)
        $subjectNamePlural = $this->pluralize($subjectName);

        $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);

        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting $subjectNamePlural.", true
            );
        } else {
            // Process each item and put it on the list, that will be exported to the csv-file
            while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
                // Format the data from raw format to possibly multiple datasets
                $formattedRows = $this->buildSubject(
                    $row, $subjectName, $additionalFormatterMethods
                );

                // Skip if there is no data (can happen on failure)
                if ($formattedRows == false) {
                    continue;
                }

                // Can contain multiple data arrays
                foreach ($formattedRows as $index => $formattedRow) {
                    $this->addItem($formattedRow);
                }
            }
        }

        return true;
    }

    /**
     * Helper method for valid pluralizing of nouns in most cases (on errors it is possible to add the right plural form as array-entry as the second parameter)
     *
     * @param string $subject
     * @param array  $extendedWordList
     *
     * @return string
     */
    protected function pluralize($subject, $extendedWordList = array())
    {
        $pluralForms = array(// nouns that are already in plural form
                             'information' => 'information',
                             'sheep'       => 'sheep',
                             'fish'        => 'fish',
                             'hair'        => 'hair',
                             'fruit'       => 'fruit',
                             'aircraft'    => 'aircraft',
                             'chinese'     => 'chinese',
                             'japanese'    => 'japanese',
                             'swiss'       => 'swiss',
                             // irregular nouns
                             'child'       => 'children',
                             'ox'          => 'oxen',
                             'man'         => 'men',
                             'woman'       => 'women',
                             'foot'        => 'feet',
                             'goose'       => 'geese',
                             'tooth'       => 'teeth',
                             'mouse'       => 'mice',
                             'louse'       => 'lice',
                             // o at end for living things get es appended, otherwise just s
                             'hero'        => 'heroes',
                             'potato'      => 'potatoes',
                             'tomato'      => 'tomatoes',// possibly more
        );

        if (!empty($extendedWordList)) {
            foreach ($extendedWordList as $key => $value) {
                // key and value must both exist
                $key   = strtolower(trim($key));
                $value = strtolower(trim($value));

                // non-associative (indexed) arrays must have the keys deleted
                if (is_numeric($key)) {
                    $key = '';
                }

                // ignore fully empty fields and copy non-empty keys to empty values and backwards
                if (empty($key) && empty($value)) {
                    continue;
                } else {
                    if (empty($key)) {
                        $key = $value;
                    } else {
                        if (empty($value)) {
                            $value = $key;
                        }
                    }
                }

                // append all extended words in lowercase
                $pluralForms[$key] = $value;
            }
        }

        // Check whitelist with predefined plural forms first (keep case of the first letter)
        $pluralized = trim($subject);
        if (isset($pluralForms[strtolower($pluralized)])) {
            return substr($pluralized, 0, 1) . substr(
                $pluralForms[strtolower($pluralized)], 1
            );
        }

        $endingChar = strtolower(substr($pluralized, -1));
        $prevChar   = strtolower(substr($pluralized, -2, -1));
        if ($endingChar != 's' && $prevChar != 's') { // not ss at end
            if ($endingChar == 'y') {
                $pluralized = substr($pluralized, 0, -1) . 'ies';
            } else {
                if ($prevChar != 'f' && $endingChar == 'f') { // ff at end
                    $pluralized = substr($pluralized, 0, -1) . 'ves';
                } else {
                    if ($prevChar == 'f' && $endingChar == 'e') { // fe at end
                        $pluralized = substr($pluralized, 0, -2) . 'ves';
                    } else {
                        if ($prevChar == 's' && $endingChar == 'h'
                            || $prevChar == 'c' && $endingChar == 'h'
                            || $endingChar == 'x'
                            || $prevChar == 's' && $endingChar == 's'
                        ) { // ch or sh or x or ss
                            $pluralized .= 'es';
                        } else {
                            $pluralized .= 's';
                        }
                    }
                }
            }
        }

        return $pluralized;
    }

    /**
     * Takes the given data and maps it to the internal structure using the corresponding helper methods, called by the "executeLoaders" method
     * and by the additional modificator (formatter) methods
     *
     * @param array  $rawSubjectData
     * @param string $subjectName
     * @param array  $additionalFormatterMethods
     *
     * @return array[] | false
     */
    private function buildSubject($rawSubjectData, $subjectName, $additionalFormatterMethods = array())
    {
        $formattedSubjectData = array();

        $subjectName = trim($subjectName);

        if (empty($subjectName)) {
            return false;
        }

        // Build the names for the methods to be called
        $subjectName         = strtolower($subjectName);
        $buildDefaultRow     = 'buildDefault' . ucfirst($subjectName) . 'Row';
        $getCreateCsvLoaders = "getCreate" . ucfirst(
                $this->camelize($this->pluralize($subjectName))
            ) . "CsvLoaders";

        // Build a default array structure where the formatted data will be stored in by calling the specific class-method (by name -> the method must exist)
        if (!method_exists($this, $buildDefaultRow)) {
            return false;
        }
        // -> get only one row, so the index 0 is used
        $formattedSubjectData[0] = $this->{$buildDefaultRow}();

        // Get a list of all processing methods by calling the specific class-method (by name -> the method must exist)
        if (!method_exists($this, $getCreateCsvLoaders)) {
            return false;
        }

        $csvLoaders = $this->{$getCreateCsvLoaders}();

        // Map the raw data to the internal structure provided before
        // -> there is still only one row there
        $formattedSubjectData[0] = $this->executeLoaders(
            $csvLoaders, $formattedSubjectData[0], $rawSubjectData
        );

        // There can be more methods given to modify (or fill) the corresponding subject (i.e options or attributes for products)
        // -> the additional formatter methods can append additional rows!
        foreach ($additionalFormatterMethods as $formatMethod) {
            if (!empty($formatMethod)) {
                if (method_exists($this, $formatMethod)) {
                    $formattedSubjectData = $this->{$formatMethod}(
                        $formattedSubjectData, $rawSubjectData
                    );
                }
            }
        }

        // Returns all fully formatted data rows for the subject (can be multiple rows after post processing, using the additional formatter methods)
        return $formattedSubjectData;
    }

    /**
     * @return array
     */
    public function _getPluginInfoLoaders()
    {
        return array('_loadPluginInfo_name', '_loadPluginInfo_version', '_loadPluginInfo_edition');
    }

    /**
     * tax classes
     *
     * @return array
     * @throws ShopgateLibraryException
     */
    protected function getTaxClasses()
    {
        $sqlQuery = "SELECT `tbl_tc`.`tax_class_id` 'id', `tbl_tc`.`tax_class_title` 'key' FROM `" . TABLE_TAX_CLASS
            . "` as tbl_tc";

        $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);
        $result      = array();

        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting.", true
            );
        } else {
            while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
                array_push($result, $row);
            }
        }

        return $result;
    }

    /**
     * get tax rates
     *
     * @return array<string, mixed>[]
     * @throws ShopgateLibraryException
     */
    protected function getTaxRates()
    {
        $query = "SELECT tr.tax_rates_id, tr.tax_description, tr.tax_rate, tr.tax_priority, "
            . "c.countries_iso_code_2, z.zone_code, tc.tax_class_id, tc.tax_class_title "
            . "FROM `" . TABLE_TAX_RATES . "` AS tr "
            . "JOIN `" . TABLE_ZONES_TO_GEO_ZONES . "` AS ztgz ON tr.tax_zone_id = ztgz.geo_zone_id "
            . "LEFT OUTER JOIN `" . TABLE_COUNTRIES . "` AS c ON ztgz.zone_country_id = c.countries_id "
            . "LEFT OUTER JOIN `" . TABLE_ZONES . "` AS z ON ztgz.zone_id = z.zone_id "
            . "JOIN `" . TABLE_TAX_CLASS . "` tc ON tr.tax_class_id = tc.tax_class_id "
            . "WHERE (c.countries_id IS NOT NULL OR ztgz.zone_country_id = '0') "
            . "AND (z.zone_id IS NOT NULL OR ztgz.zone_id = '0');";

        $result = ShopgateWrapper::db_query($query);
        if (!$result) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                'Shopgate Plugin - Error selecting.', true
            );
        }

        $taxRates = array();
        while ($row = ShopgateWrapper::db_fetch_array($result)) {
            $taxRates[] = $row;
        }

        return $taxRates;
    }

    /**
     * wrapper function to create a instance of the ShopgateItemModel
     *
     * @return Shopgate_Helpers_Item
     */
    protected function getItemModelInstance()
    {
        $itemObject = $this->di->make('Shopgate_Helpers_Item');
        $itemObject->setLanguageId($this->langID);
        $itemObject->setStoreCurrency($this->currency);
        $itemObject->setStoreLanguage($this->language);
        $itemObject->setStoreExchangeRate($this->exchangeRate);
        $itemObject->setIsUsaPlugin($this->config->getIsUsaPlugin());
        $itemObject->setTaxZone($this->taxZone);
        $itemObject->setSwisscartImageCount($this->swisscartImageCount);
        $itemObject->setSwisscartVersion($this->swisscartVersion);

        return $itemObject;
    }


    /**
     * return an array with all valid shipping methods to an order
     *
     * @param ShopgateCart $sgShoppingCart
     *
     * @return array of ShopgateShippingMethod
     */
    protected function getShipping(ShopgateCart $sgShoppingCart)
    {
        if (!defined('MODULE_SHIPPING_INSTALLED') || !tep_not_null(MODULE_SHIPPING_INSTALLED)
            || MODULE_SHIPPING_INSTALLED == ""
        ) {
            return array();
        }

        if (!class_exists('currencies')) {
            /** @noinspection PhpIncludeInspection */
            include_once(rtrim(DIR_WS_CLASSES, "/") . "/currencies.php");
        }
        if (!class_exists('shoppingCart')) {
            /** @noinspection PhpIncludeInspection */
            include_once(rtrim(DIR_WS_CLASSES, "/") . "/shopping_cart.php");
        }
        /** @noinspection PhpIncludeInspection */
        include_once(rtrim(DIR_WS_CLASSES, "/") . "/order.php");
        /** @noinspection PhpIncludeInspection */
        include_once(rtrim(DIR_WS_MODULES, "/") . "/order_total/ot_shipping.php");
        /** @noinspection PhpIncludeInspection */
        require_once(DIR_WS_CLASSES . 'shipping.php');
        if (file_exists(DIR_WS_CLASSES . 'http_client.php')) {
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_WS_CLASSES . 'http_client.php');
        }

        global $total_count, $shipping_weight, $total_weight,
               $shipping_num_boxes, $currencies, $cart, $order,
               $sendto, $billto, $ot_shipping;

        /** @var Shopgate_Helpers_Zones $locationObj */
        $locationObj        = $this->di->make('Shopgate_Helpers_Zones');
        /** @var Shopgate_Helpers_Products $cartItemObj */
        $cartItemObj        = $this->di->make('Shopgate_Helpers_Products');
        $currencies         = new currencies();
        $cart               = new shoppingCart();
        $total_count        = count($sgShoppingCart->getItems());
        $total_weight       = $cartItemObj->getProductsWeight($sgShoppingCart->getItems());
        $shipping_num_boxes = 1;

        foreach ($sgShoppingCart->getItems() as $product) {
            $options   = $product->getOptions();
            $sgOptions = array();

            foreach ($options as $option) {
                $sgOptions[$option->getOptionNumber()] = $option->getValueNumber();
            }
            $cart->add_cart($product->getItemNumber(), $product->getQuantity(), $sgOptions);
        }

        $sgDeliverAddress = $sgShoppingCart->getDeliveryAddress();
        if (!empty($sgDeliverAddress)) {
            $country = $locationObj->getCountryByIso2Name($sgDeliverAddress->getCountry());
            $zone    = $locationObj->getZoneByCountryIdModuleShippingFlat($country["countries_id"]);

            $sendto = array(
                "firstname"          => $sgDeliverAddress->getFirstName(),
                "lastname"           => $sgDeliverAddress->getLastName(),
                "company"            => $sgDeliverAddress->getCompany(),
                "street_address"     => $sgDeliverAddress->getStreet1(),
                "suburb"             => "",
                "postcode"           => $sgDeliverAddress->getZipcode(),
                "city"               => $sgDeliverAddress->getCity(),
                "zone_id"            => $zone["zone_id"],
                "zone_name"          => $zone["zone_name"],
                "country_id"         => $country["countries_id"],
                "country_iso_code_2" => $country["countries_iso_code_2"],
                "country"            => array(
                    "id"         => $country["countries_id"],
                    "iso_code_2" => $country["countries_iso_code_2"]
                ),
                "country_iso_code_3" => $country["countries_iso_code_3"],
                "address_format_id"  => ""
            );
        }

        $sgInvoiceAddress = $sgShoppingCart->getInvoiceAddress();
        if (empty($sgInvoiceAddress)) {
            $billto = $sendto;
        } else {
            $country = $locationObj->getCountryByIso2Name($sgInvoiceAddress->getCountry());
            $zone    = $locationObj->getZoneByCountryId($country["countries_id"]);
            $billto  = array(
                "firstname"          => $sgInvoiceAddress->getFirstName(),
                "lastname"           => $sgInvoiceAddress->getLastName(),
                "company"            => $sgInvoiceAddress->getCompany(),
                "street_address"     => $sgInvoiceAddress->getStreet1(),
                "suburb"             => "",
                "postcode"           => $sgInvoiceAddress->getZipcode(),
                "city"               => $sgInvoiceAddress->getCity(),
                "zone_id"            => $zone["zone_id"],
                "zone_name"          => $zone["zone_name"],
                "country_id"         => $country["countries_id"],
                "country_iso_code_2" => $country["countries_iso_code_2"],
                "country"            => array(
                    "id"         => $country["countries_id"],
                    "iso_code_2" => $country["countries_iso_code_2"]
                ),
                "country_iso_code_3" => $country["countries_iso_code_3"],
                "address_format_id"  => ""
            );
        }

        $order = new order();
        $order->cart();
        $order->customer = $sendto;
        $order->delivery = $sendto;
        $order->billing  = $billto;

        $ot_shipping = new ot_shipping();
        $ot_shipping->process();

        $shipping_modules = new shipping;

        if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING')
            && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')
        ) {
            $pass = false;

            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                case 'national':
                    if ($order->delivery['country_id'] == STORE_COUNTRY) {
                        $pass = true;
                    }
                    break;
                case 'international':
                    if ($order->delivery['country_id'] != STORE_COUNTRY) {
                        $pass = true;
                    }
                    break;
                case 'both':
                    $pass = true;
                    break;
            }

            $free_shipping = false;
            if (($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                $free_shipping = true;
                /** @noinspection PhpIncludeInspection */
                include(DIR_WS_LANGUAGES . $this->language . '/modules/order_total/ot_shipping.php');
            }
        } else {
            $free_shipping = false;
        }

        // if shipping is free all other shipping methods will be ignored
        if ($free_shipping) {
            /** @var ShopgateShippingMethod $sgShippingMethod */
            $sgShippingMethod = $this->di->make('ShopgateShippingMethod');
            $sgShippingMethod->setDescription(
                "Total amount over "
                . MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER . " is free"
            );
            $sgShippingMethod->setTitle("Free Shipping");
            $sgShippingMethod->setAmount(0);

            return array($sgShippingMethod);
        }

        $quotes = $shipping_modules->quote();
        /** @var Shopgate_Helpers_Shipping $shippingHelper */
        $shippingHelper = $this->di->make('Shopgate_Helpers_Shipping');

        return $shippingHelper->getShippingMethodExport($quotes);
    }

    /**
     * check the validity of the shopgate cart against the shop
     *
     * @param ShopgateCart $cart
     *
     * @return array
     */
    private function checkValidityOfCartItems(ShopgateCart $cart)
    {
        /**
         * @var Shopgate_Helpers_Zones    $locationObj
         * @var Shopgate_Helpers_Tax      $taxObject
         * @var Shopgate_Helpers_Products $cartItemObj
         * @var ShopgateCartItem          $sgProduct
         */
        $resultItems = array();
        $itemObject  = $this->getItemModelInstance();
        $locationObj = $this->di->make('Shopgate_Helpers_Zones');
        $taxObject   = $this->di->make('Shopgate_Helpers_Tax');
        $cartItemObj = $this->di->make('Shopgate_Helpers_Products');

        foreach ($cart->getItems() as $item) {
            $pId       = $cartItemObj->getProductIdFromCartItems($item);
            $sgProduct = $this->di->make('ShopgateCartItem');

            if (!$itemObject->productExist($pId)) {
                $sgProduct->setError(ShopgateLibraryException::CART_ITEM_PRODUCT_NOT_FOUND);
                $sgProduct->setIsBuyable(false);
                $resultItems[] = $sgProduct;
                continue;
            } else {
                global $customer_zone_id, $customer_country_id;
                $address = $cart->getDeliveryAddress();

                if (!empty($address)) {
                    $country             = $locationObj->getCountryByIso2Name($address->getCountry());
                    $customer_country_id = $country["countries_id"];
                    $customer_zone_id    = $locationObj->getCustomerZoneId(
                        $customer_country_id,
                        ShopgateMapper::getShoppingsystemStateCode($address->getState())
                    );
                    $productTaxRate      = tep_get_tax_rate(
                        $taxObject->getProductsTaxClass($pId),
                        $customer_country_id, $customer_zone_id
                    );
                } else {
                    $productTaxRate = tep_get_tax_rate($taxObject->getProductsTaxClass($pId));
                }

                $sgProduct = $cartItemObj->generateCartItemProduct($item, $sgProduct);
                $sgProduct = $cartItemObj->getCartItemOptions($item, $sgProduct, $productTaxRate);
                $sgProduct = $cartItemObj->getCartItemAttributes($item, $sgProduct);
                $dbProduct = $itemObject->getProduct($pId);
                $sgProduct->setIsBuyable(
                    $itemObject->getIsAvailable($dbProduct["products_quantity"], $dbProduct["products_status"])
                );

                $tmpPriceItem = array(
                    "specials_new_products_price" => tep_get_products_special_price($pId),
                    "products_price"              => $dbProduct["products_price"]
                );

                $price = $this->getPrice($tmpPriceItem, $productTaxRate, $this->exchangeRate);
                $sgProduct->setUnitAmountWithTax($price);
                $sgProduct->setUnitAmount(
                    (empty($productTaxRate)) ? $price : $this->formatPriceNumber($price / (1 + ($productTaxRate / 100)))
                );
                $sgProduct->setQtyBuyable(
                    $itemObject->buildUseStock() ? min($dbProduct["products_quantity"], $item->getQuantity())
                        : $item->getQuantity()
                );
                $sgProduct->setStockQuantity($dbProduct["products_quantity"]);

                if ($sgProduct->getQtyBuyable() < $item->getQuantity()) {
                    $sgProduct->setError(ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE);
                }

                if (!$sgProduct->getIsBuyable()
                    && (empty($dbProduct["products_quantity"])
                        || $dbProduct["products_quantity"] == 0)
                ) {
                    $sgProduct->setError(ShopgateLibraryException::CART_ITEM_OUT_OF_STOCK);
                }

                $resultItems[] = $sgProduct;
            }
        }

        return $resultItems;
    }

    /**
     * Checks if there is a special price and returns the special price with tax, if given and adds up exchange if given OR
     * the default price if no special is set
     *
     * @param array  $aArticle
     * @param string $taxPercent
     * @param int    $exchangeValue
     *
     * @return string - empty if there is no special price or a float value with the old price [with tax and exchange]
     */
    protected function getPrice($aArticle, $taxPercent = null, $exchangeValue = 1)
    {
        if ($this->isSpecialPrice($aArticle)) {
            $price = $aArticle["specials_new_products_price"];
        } else {
            $price = $aArticle["products_price"];
        }

        if (!empty($price)) {
            if (!empty($taxPercent)) {
                $price *= 1 + ($taxPercent / 100);
            }
            if (!empty($exchangeValue)) {
                $price *= $exchangeValue;
            }

            $price = $this->formatPriceNumber($price, 2);
        }

        return $price;
    }

    /**
     * Checks if the product has a valid special price (returns true if so, otherwise it returns false)
     *
     * @param array $aArticle
     *
     * @return boolean
     */
    protected function isSpecialPrice($aArticle)
    {
        return !empty($aArticle["specials_new_products_price"]);
    }

    /**
     * Takes all as "shipping completed" marked orders and apply this status to the equivalent Shopgate order.
     *
     * @param $errCount
     * @param $message
     *
     * @return bool
     * @throws ShopgateLibraryException
     */
    protected function cronSetOrdersShippingCompleted(&$errCount, &$message)
    {
        if (!$this->tableExists(TABLE_ORDERS_SHOPGATE_ORDER)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error: \"" . TABLE_ORDERS_SHOPGATE_ORDER
                . "\" table is missing.", true
            );
        }

        $query = "SELECT `sgo`.`orders_id`, `sgo`.`shopgate_order_number` "
            . "FROM `" . TABLE_ORDERS_SHOPGATE_ORDER . "` sgo "
            . "INNER JOIN `" . TABLE_ORDERS . "` osco ON (`osco`.`orders_id` = `sgo`.`orders_id`) "
            . "WHERE `sgo`.`is_sent_to_shopgate` = 0 " . "AND ("
            . "`sgo`.`shopgate_shop_number` = '" . ShopgateWrapper::db_input($this->config->getShopNumber())
            . "' " . "OR `sgo`.`shopgate_shop_number` IS NULL"
            . ") " . "AND `osco`.`orders_status` = '"
            . ShopgateWrapper::db_input($this->config->getOrderStatusShipped()) . "';";

        $result = ShopgateWrapper::db_query_print_on_err($query);

        if (empty($result)) {
            return true;
        }

        while ($shopgateOrder = ShopgateWrapper::db_fetch_array($result)) {

            if (!$this->setOrderShippingCompleted(
                $shopgateOrder, $shopgateOrder['orders_id'], $this->merchantApi, $this->config
            )
            ) {
                $errCount++;
                $message .= 'Shopgate order number "'
                    . $shopgateOrder['shopgate_order_number'] . '": error'
                    . "\n";
            }
        }
    }

    /**
     * request Shopgate to set shipping complete to an order
     *
     * @param                          string [} $shopgateOrder
     * @param                          $orderId
     * @param ShopgateMerchantApi      $merchantApi
     * @param ShopgateConfigOsCommerce $config
     *
     * @return bool
     * @throws ShopgateLibraryException
     */
    protected function setOrderShippingCompleted(
        $shopgateOrder, $orderId, ShopgateMerchantApi &$merchantApi,
        ShopgateConfigOsCommerce &$config
    ) {
        $success = false;

        // These are expected and should not be added to error count:
        $ignoreCodes = array(
            ShopgateMerchantApiException::ORDER_ALREADY_COMPLETED,
            ShopgateMerchantApiException::ORDER_SHIPPING_STATUS_ALREADY_COMPLETED
        );

        try {
            $merchantApi->setOrderShippingCompleted($shopgateOrder['shopgate_order_number']);
            $this->saveSingleOrdersStatusHistory(
                $orderId, $config->getOrderStatusShipped(),
                "[Shopgate] " . ENTRY_ORDER_MARKED_AS_SHIPPED
            );
            $success = true;
        } catch (ShopgateLibraryException $e) {
            $response = $this->stringFromUtf8($e->getAdditionalInformation(), $config->getEncoding());
            $this->saveSingleOrdersStatusHistory(
                $orderId, $config->getOrderStatusShipped(),
                "[Shopgate] " . ENTRY_SHOPGATE_MODULE_ERROR . " ({$e->getCode()}): {$response}"
            );
        } catch (ShopgateMerchantApiException $e) {
            $response = $this->stringFromUtf8($e->getMessage(), $config->getEncoding());
            $this->saveSingleOrdersStatusHistory(
                $orderId, $config->getOrderStatusShipped(),
                "[Shopgate] " . ENTRY_SHOPGATE_MODULE_ERROR . " ({$e->getCode()}): {$response}"
            );
            $success = (in_array($e->getCode(), $ignoreCodes)) ? true : false;
        } catch (Exception $e) {
            $response = $this->stringFromUtf8($e->getMessage(), $config->getEncoding());
            $this->saveSingleOrdersStatusHistory(
                $orderId, $config->getOrderStatusShipped(),
                "[Shopgate] " . ENTRY_SHOPGATE_UNKNOWN_ERROR . " ({$e->getCode()}): {$response}"
            );
        }

        // Update shopgate order on success
        if ($success) {
            $qry = 'UPDATE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` SET `is_sent_to_shopgate` = 1 WHERE `shopgate_order_number` = '
                . $shopgateOrder['shopgate_order_number'] . ';';
            ShopgateWrapper::db_query($qry);
        }

        return $success;
    }

    /**
     * logic for cron job CANCEL_ORDERS.
     * Takes all as "cancel" marked orders and apply this status to the equivalent Shopgate order.
     *
     * @param $message
     * @param $errCount
     *
     * @throws ShopgateLibraryException
     */
    protected function cronCancelOrder(&$message, &$errCount)
    {
        if (!$this->tableExists(TABLE_ORDERS_SHOPGATE_ORDER)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error: \"" . TABLE_ORDERS_SHOPGATE_ORDER
                . "\" table is missing.", true
            );
        }

        $query = "SELECT `sgo`.`orders_id`, `sgo`.`shopgate_order_number`, `sgo`.`order_data` "
            . "FROM `" . TABLE_ORDERS_SHOPGATE_ORDER . "` sgo "
            . "INNER JOIN `" . TABLE_ORDERS . "` osco ON (`osco`.`orders_id` = `sgo`.`orders_id`) "
            . "WHERE `sgo`.`is_cancellation_sent_to_shopgate` = 0 "
            . "AND (" . "`sgo`.`shopgate_shop_number` = '"
            . ShopgateWrapper::db_input($this->config->getShopNumber()) . "' "
            . "OR `sgo`.`shopgate_shop_number` IS NULL"
            . ") " . "AND `osco`.`orders_status` = '"
            . ShopgateWrapper::db_input($this->config->getOrderStatusCanceled()) . "';";

        $result = ShopgateWrapper::db_query_print_on_err($query);

        if (empty($result)) {
            return;
        }

        while ($shopgateOrder = ShopgateWrapper::db_fetch_array($result)) {
            if (!$this->setOrderCanceled(
                $shopgateOrder, $shopgateOrder['orders_id'], $this->merchantApi, $this->config
            )
            ) {
                $errCount++;
                $message .= 'Shopgate order number "' . $shopgateOrder['shopgate_order_number'] . '": error' . "\n";
            }
        }
    }

    /**
     * request Shopgate to cancel/cancel parts(shipping) of an order
     *
     * @param                          $shopgateOrder
     * @param                          $orderId
     * @param ShopgateMerchantApi      $merchantApi
     * @param ShopgateConfigOsCommerce $config
     *
     * @return bool
     * @throws ShopgateLibraryException
     */
    protected function setOrderCanceled(
        $shopgateOrder,
        $orderId,
        ShopgateMerchantApi &$merchantApi,
        ShopgateConfigOsCommerce &$config
    ) {

        $success = false;
        // These are expected and should not be added to error count:
        //Order already cancelled
        $ignoreCodes = array(222);

        try {
            $orderStatus       = $config->getOrderStatusCanceled();
            $cancellationItems = array();

            // fallback when order data was not inserted into db.
            if (empty($shopgateOrder) || empty($shopgateOrder["order_data"])) {
                $params = array(
                    'shop_number'   => $this->config->getShopNumber(), 'with_items' => 1,
                    'order_numbers' => array(0 => "{$shopgateOrder['shopgate_order_number']}"),
                );

                /* @var ShopgateMerchantApiResponse $order */
                $merchantOrderResponse = $merchantApi->getOrders($params);
                $sgRequestedOrders     = $merchantOrderResponse->getData();

                if (count($sgRequestedOrders) == 0) {
                    $this->saveSingleOrdersSatusHistory(
                        $shopgateOrder["orders_id"], $orderStatus,
                        "[Shopgate] " . SHOPGATE_ORDER_NOT_FOUND
                    );

                    return false;
                }

                /* @var ShopgateOrder $shopgateOrderObject */
                $shopgateOrderObject = reset(
                    $sgRequestedOrders
                );// there can be only one order. cause only one was requested

            } else {
                $shopgateOrderObject = unserialize(
                    $shopgateOrder["order_data"]
                );
            }

            /* @var ShopgateOrder $shopgateOrderObject */
            if (!$config->getAlwaysCancelShipping()) {
                foreach ($shopgateOrderObject->getItems() as $item) {
                    $cancellationItems[] = array(
                        'order_item_id' => $item->getOrderItemId(),
                        'item_number'   => (!is_null($item->getParentItemNumber())
                            ? $item->getParentItemNumber()
                            : $item->getItemNumber()),
                        'quantity'      => $item->getQuantity(),
                    );
                }
            }

            // if cancelShipping is true, cancelCompleteOrder must be true too
            $merchantApi->cancelOrder(
                $shopgateOrder['shopgate_order_number'],
                $config->getAlwaysCancelShipping(), $cancellationItems,
                $config->getAlwaysCancelShipping()
            );
            $this->saveSingleOrdersSatusHistory(
                $shopgateOrder["orders_id"], $orderStatus,
                "[Shopgate] " . ENTRY_ORDER_MARKED_AS_CANCELED
            );
            $success = true;
        } catch (ShopgateLibraryException $e) {
            $response = $this->stringFromUtf8(
                $e->getAdditionalInformation(), $config->getEncoding()
            );
            $this->saveSingleOrdersSatusHistory(
                $orderId, $orderStatus,
                "[Shopgate] " . ENTRY_SHOPGATE_MODULE_ERROR . " ({$e->getCode()}): {$response}"
            );
        } catch (ShopgateMerchantApiException $e) {
            $response = $this->stringFromUtf8($e->getMessage(), $config->getEncoding());
            $this->saveSingleOrdersSatusHistory(
                $orderId, $orderStatus,
                "[Shopgate] " . ENTRY_SHOPGATE_MODULE_ERROR . " ({$e->getCode()}): {$response}"
            );
            $success = (in_array($e->getCode(), $ignoreCodes)) ? true : false;
        } catch (Exception $e) {
            $response = $this->stringFromUtf8($e->getMessage(), $config->getEncoding());
            $this->saveSingleOrdersSatusHistory(
                $orderId, $orderStatus,
                "[Shopgate] " . ENTRY_SHOPGATE_UNKNOWN_ERROR . " ({$e->getCode()}): {$response}"
            );
        }

        // Update shopgate order on success
        if ($success) {
            $qry = 'UPDATE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` SET `is_cancellation_sent_to_shopgate` = 1 WHERE `shopgate_order_number` = '
                . $shopgateOrder['shopgate_order_number'] . ';';
            ShopgateWrapper::db_query($qry);
        }

        return $success;
    }

    /**
     * check if shops ystem is osCommerce or Swiss Cart
     *
     * @param $aInfo
     * @param $unused
     *
     * @return mixed
     */
    public function _loadPluginInfo_name($aInfo, $unused)
    {
        $aInfo['system_name'] = (!empty($this->swisscartVersion) ? 'swisscart / osCommerce' : 'osCommerce');

        return $aInfo;
    }

    /**
     * set the shop systems version
     *
     * @param $aInfo
     * @param $unused
     *
     * @return mixed
     */
    public function _loadPluginInfo_version($aInfo, $unused)
    {
        $aInfo['version'] = defined('PROJECT_VERSION') ? PROJECT_VERSION : 'undefined';

        return $aInfo;
    }

    /**
     * set the shop systems edition
     *
     * @param $aInfo
     * @param $unused
     *
     * @return mixed
     */
    public function _loadPluginInfo_edition($aInfo, $unused)
    {
        $aInfo['edition'] = (!empty($this->swisscartVersion) ? $this->swisscartVersion : '');

        return $aInfo;
    }

    /**
     * Match the order status between merchants system and Shopgate.
     * This is not called via api.php, so DI will not work here.
     *
     * @param array $orderIds
     * @param string $status
     *
     * @throws ShopgateLibraryException
     */
    public function updateOrdersStatus($orderIds, $status)
    {
        if (!is_array($orderIds) || empty($orderIds)) {
            return;
        }

        // Get shopgate orders
        if (!$this->tableExists(TABLE_ORDERS_SHOPGATE_ORDER)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error: \"" . TABLE_ORDERS_SHOPGATE_ORDER
                . "\" table is missing.", true
            );
        }

        $query = ShopgateWrapper::db_input(
            "SELECT `sgo`.`orders_id`, `sgo`.`shopgate_order_number`, `sgo`.`shopgate_shop_number` "
            . "FROM `" . TABLE_ORDERS_SHOPGATE_ORDER . "` sgo "
            . "WHERE `sgo`.`orders_id` IN (" . ShopgateWrapper::db_input(
                implode(", ", $orderIds)
            ) . ")"
        );

        $result = ShopgateWrapper::db_query($query);

        if (empty($result)) {
            return;
        }
        $configurations = array();
        while ($shopgateOrder = ShopgateWrapper::db_fetch_array($result)) {
            $shopNumber = !empty($shopgateOrder['shopgate_shop_number'])
                ? $shopgateOrder['shopgate_shop_number'] : 'global';

            if (empty($merchantApis[$shopNumber])) {
                try {
                    $config = new ShopgateConfigOsCommerce();

                    if ($shopNumber != 'global') {
                        $config->loadByShopNumber($shopNumber);
                    }

                    $builder                     = new ShopgateBuilder($config);
                    $merchantApis[$shopNumber]   = $builder->buildMerchantApi();
                    $configurations[$shopNumber] = $config;
                } catch (ShopgateLibraryException $e) {
                    $this->log(
                        'error in update orders status: ' . $e,
                        ShopgateLogger::LOGTYPE_ERROR
                    );
                }

                if ($status == $configurations[$shopNumber]->getOrderStatusCanceled()) {
                    $this->setOrderCanceled(
                        $shopgateOrder, $shopgateOrder['orders_id'],
                        $merchantApis[$shopNumber], $configurations[$shopNumber]
                    );
                } elseif ($status == $configurations[$shopNumber]->getOrderStatusShipped()) {
                    $this->setOrderShippingCompleted(
                        $shopgateOrder, $shopgateOrder['orders_id'],
                        $merchantApis[$shopNumber], $configurations[$shopNumber]
                    );
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function createItems($limit = null, $offset = null, array $uids = array())
    {
        $itemObject   = $this->getItemModelInstance();
        $sqlQuery    = $itemObject->buildItemQuery($limit, $offset, $uids);
        if (!empty($this->swisscartVersion)) {
            $this->swisscartImageCount = $itemObject->getSwisscartImageCount();
        }

        $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);

        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting Items.", true
            );
        } else {
            /** @var Shopgate_Helpers_ItemXml $itemXmlObj */
            while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
                $itemXmlObj = $this->di->make('Shopgate_Helpers_ItemXml');
                $itemXmlObj->setItem($row);
                $itemXmlObj->setLanguageId($this->langID);
                $itemXmlObj->setStoreCurrency($this->config->getCurrency());
                $itemXmlObj->setStoreLanguage($this->config->getLanguage());
                $itemXmlObj->setStoreExchangeRate($this->exchangeRate);
                $itemXmlObj->setIsUsaPlugin($this->config->getIsUsaPlugin());
                $itemXmlObj->setSwisscartImageCount($this->swisscartImageCount);
                $itemXmlObj->setSwisscartVersion($this->swisscartVersion);
                $this->addItemModel($itemXmlObj->generateData());
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function createItemsCsv()
    {
        $itemModel = $this->getItemModelInstance();
        $sqlQuery  = $itemModel->buildItemQuery(
            $this->exportLimit, $this->exportOffset, $_REQUEST['item_numbers']
        );
        $this->buildCsv($sqlQuery, 'item', array('buildItemOptions'));
    }

    /**
     * Fill the item_number field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportItemNumber($aItem, $aArticle)
    {
        $aItem['item_number'] = $aArticle['products_id'];

        return $aItem;
    }

    /**
     * Fill the item_number field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportItemNumberPublic($aItem, $aArticle)
    {
        $aItem['item_number_public'] = $aArticle['products_model'];

        return $aItem;
    }

    /**
     * Fill the item_name field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportItemName($aItem, $aArticle)
    {
        $itemModel          = $this->getItemModelInstance();
        $aItem['item_name'] = $itemModel->buildName($aArticle['products_name']);

        return $aItem;
    }

    /**
     * Fill the unit_amount field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     *
     */
    protected function itemExportUnitAmount($aItem, $aArticle)
    {
        $aItem['unit_amount'] = $this->getPrice($aArticle, $aArticle['tax_rate'], $this->exchangeRate);

        return $aItem;
    }

    /**
     * Fill the currency field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportCurrency($aItem, $aArticle)
    {
        $aItem['currency'] = $this->currency;

        return $aItem;
    }

    /**
     * Fill the tax_percent field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     *
     */
    protected function itemExportTaxPercent($aItem, $aArticle)
    {
        $itemModel            = $this->getItemModelInstance();
        $aItem['tax_percent'] = $itemModel->buildTaxRate($aArticle['tax_rate']);

        return $aItem;
    }

    /**
     * Fill the description field in the given array
     *
     * @param $aItem
     * @param $aArticle
     *
     * @return array
     */
    protected function itemExportDescription($aItem, $aArticle)
    {
        $itemModel            = $this->getItemModelInstance();
        $aItem['description'] = $itemModel->buildDescription($aArticle['products_description']);

        return $aItem;
    }

    /**
     * Fill the urls_images field in the given array
     *
     * @param $aItem
     * @param $aArticle
     *
     * @return array
     */
    protected function itemExportUrlsImages($aItem, $aArticle)
    {
        $itemModel            = $this->getItemModelInstance();
        $aItem['urls_images'] = implode('||', $itemModel->getProductImages($aArticle));

        return $aItem;
    }

    /**
     * Fill the categories field in the given array
     *
     * @param $aItem
     * @param $aArticle
     *
     * @return array
     */
    protected function itemExportCategories($aItem, $aArticle)
    {
        $aItem["categories"] = $this->getCategories($aArticle);

        return $aItem;
    }

    /**
     * Takes all categories where the item is mapped to and creates the paths with the actual category-names set
     *
     * @param array $aArticle
     *
     * @throws ShopgateLibraryException
     * @return string
     */
    protected function getCategories($aArticle)
    {
        $categories = "";

        // Make optimized queries possible (get every name only once)
        $categoryIdToNameMapping = array();

        $categoryIds   = $this->getCategoryNumbers($aArticle, false);
        $categoryPaths = array();
        foreach ($categoryIds as $categoryId) {
            $categoryPath = $this->getCategoryPath($categoryId);
            foreach ($categoryPath as $categoryIdInPath) {
                // Saves a list of all used category ids
                $categoryIdToNameMapping[$categoryIdInPath] = $categoryIdInPath;
            }
            $categoryPaths[] = $categoryPath;
        }

        if (!empty($categoryIdToNameMapping)) {
            // Now get all needed category names
            // -> make a list of needed names
            $categoryIdListString = "";
            foreach ($categoryIdToNameMapping as $categoryId) {
                $categoryIdListString .= ',' . $categoryId;
            }
            // -> skip first comma in that list
            $categoryIdListString = substr($categoryIdListString, 1);
            // -> get the names from database
            $sqlQuery = "SELECT DISTINCT " . "tbl_cd.categories_id, "
                . "tbl_cd.categories_name " . "FROM "
                . TABLE_CATEGORIES_DESCRIPTION . " tbl_cd " . "WHERE "
                . "tbl_cd.categories_id IN (" . $categoryIdListString . ") "
                . "AND tbl_cd.language_id = " . $this->langID;

            $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);
            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error selecting category description.",
                    true
                );
            } else {
                // Map the names
                while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
                    $categoryIdToNameMapping[$row["categories_id"]] = $row["categories_name"];
                }
                if (strtoupper($this->config->getLanguage()) == 'GE' || strtoupper($this->config->getLanguage()) == "DE"
                ) {
                    $categoryIdToNameMapping[0] = "Hauptkategorie";
                } else {
                    $categoryIdToNameMapping[0] = "main category";
                }

                // Now build the category string
                if (!empty($categoryPaths)) {
                    foreach ($categoryPaths as $categoryPath) {
                        if (!empty($categoryPath)) {
                            $categories .= '||';
                            foreach ($categoryPath as $categoryIdInPath) {
                                // Use the name instead of an id and append the "=>" that will be removed after the actual path is finished
                                $categories .= $categoryIdToNameMapping[$categoryIdInPath] . "=>";
                            }
                            // skip the lately appended "=>" because there is no more id to be appended to the actual path
                            $categories = substr($categories, 0, -2);
                        }
                    }
                    // skip the first ||
                    $categories = substr($categories, 2);
                }
            }
        }

        return $categories;
    }

    /**
     * Does a query on all categories that the actual product is put in and returns all category numbers
     * as a single string or as an array (if the second param is set to false)
     *
     * @param array   $aArticle
     * @param boolean $returnString
     *
     * @throws ShopgateLibraryException
     * @return string|array
     */
    protected function getCategoryNumbers($aArticle, $returnString = true)
    {
        $numbers     = array();
        $sqlQuery    = "SELECT DISTINCT " . "tbl_ptc.categories_id " . "FROM "
            . TABLE_PRODUCTS_TO_CATEGORIES . " tbl_ptc " . "LEFT JOIN "
            . TABLE_CATEGORIES
            . " tbl_c ON tbl_ptc.categories_id = tbl_c.categories_id "
            . "WHERE tbl_ptc.products_id = '" . $aArticle["products_id"]
            . "' AND tbl_c.categories_id != 0";
        $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);

        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting category numbers.", true
            );
        } else {
            while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
                $numbers[] = $row["categories_id"];
            }
        }
        // Check if the call er requested the return-value as string
        if ($returnString) {
            // Return all category numbers
            if (!empty($numbers)) {
                return implode('||', $numbers);
            }

            return '';
        }

        // Return an array as told by $returnString
        return $numbers;
    }

    /**
     * Takes a category id and creates a path-array from the the top category down to the actual one
     *
     * @param integer $categoryId
     *
     * @return array
     * @throws ShopgateLibraryException
     */
    protected function getCategoryPath($categoryId)
    {
        $categoryPath = array();

        if (!empty($categoryId)) {
            // Get parent
            $sqlQuery    = "SELECT " . "tbl_c.parent_id " . "FROM " . TABLE_CATEGORIES
                . " tbl_c " . "WHERE " . "tbl_c.categories_id = $categoryId "
                . "LIMIT 1";
            $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);
            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error selecting category.", true
                );
            } else {
                $row = ShopgateWrapper::db_fetch_array($queryResult);
                if (!empty($row)) {
                    if (($row['parent_id'] == 0) || ($row['parent_id'] == $categoryId)) {
                        return array($categoryId); // Ignore the main category
                    } else {
                        // Get all upper categories
                        $categoryPath = $this->getCategoryPath($row['parent_id']);
                        // append the actual category afterwards
                        $categoryPath[] = $categoryId;
                    }
                }
            }
        }

        return $categoryPath;
    }

    /**
     * Fill the category_numbers field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportCategoryNumbers($aItem, $aArticle)
    {
        $aItem['category_numbers'] = $this->getCategoryNumbers($aArticle);

        return $aItem;
    }

    /**
     * Fill the is_available field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportIsAvailable($aItem, $aArticle)
    {
        $itemModel             = $this->getItemModelInstance();
        $aItem['is_available'] =
            intval($itemModel->getIsAvailable($aArticle['products_quantity'], $aArticle['products_status']));

        return $aItem;
    }

    /**
     * Fill the available_text field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportAvailableText($aItem, $aArticle)
    {
        $itemModel               = $this->getItemModelInstance();
        $aItem['available_text'] = $itemModel->getAvailableText(
            $aArticle['products_quantity'], $aArticle['products_date_available'], $aArticle['products_status']
        );

        return $aItem;
    }

    /**
     * Fill the manufacturer field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportManufacturer($aItem, $aArticle)
    {
        $aItem['manufacturer'] = $aArticle['manufacturers_name'];

        return $aItem;
    }

    /**
     * Fill the url_deeplink field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportUrlDeeplink($aItem, $aArticle)
    {
        $itemModel             = $this->getItemModelInstance();
        $aItem['url_deeplink'] = $itemModel->buildDeeplink($aArticle['products_id']);

        return $aItem;
    }

    /**
     * Fill the old_unit_amount field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportOldUnitAmount($aItem, $aArticle)
    {
        $aItem['old_unit_amount'] = $this->getOldPrice($aArticle, $aArticle['tax_rate'], $this->exchangeRate);

        return $aItem;
    }

    /**
     * Checks if there is a special price and returns the non-special price with tax, if given and adds up exchange if given
     *
     * @param array $aArticle
     * @param null  $taxPercent
     * @param int   $exchangeValue
     *
     * @return float|string empty string if there is no special price or a float value with the old price [with tax and exchange]
     */
    protected function getOldPrice($aArticle, $taxPercent = null, $exchangeValue = 1)
    {
        if ($this->isSpecialPrice($aArticle)) {
            $price = $aArticle['products_price'];
        } else {
            $price = '';
        }

        if (!empty($price)) {
            if (!empty($taxPercent)) {
                $price *= 1 + ($taxPercent / 100);
            }
            if (!empty($exchangeValue)) {
                $price *= $exchangeValue;
            }

            $price = $this->formatPriceNumber($price, 2);
        }

        return $price;
    }

    /**
     * Fill the use_stock field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportUseStock($aItem, $aArticle)
    {
        $itemModel          = $this->getItemModelInstance();
        $aItem['use_stock'] = $itemModel->buildUseStock();

        return $aItem;
    }

    /**
     * Fill the stock_quantity field in the given array
     *
     * @param $aItem
     * @param $aArticle
     *
     * @return array
     */
    protected function itemExportStockQuantity($aItem, $aArticle)
    {
        $aItem["stock_quantity"] = $aArticle['products_quantity'];

        return $aItem;
    }

    /**
     * Fill the last_update field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportLastUpdate($aItem, $aArticle)
    {
        $itemModel            = $this->getItemModelInstance();
        $aItem['last_update'] = $itemModel->buildLastUpdate($aArticle['products_last_modified']);

        return $aItem;
    }

    /**
     * Fill the sort_order field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportSortOrder($aItem, $aArticle)
    {
        $aItem['sort_order'] = $aArticle['products_id'];

        return $aItem;
    }

    /**
     * Fill the is_highlight field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportIsHighlight($aItem, $aArticle)
    {
        $aItem['is_highlight'] = !empty($aArticle['feature_status']) ? 1 : 0;

        return $aItem;
    }

    /**
     * Fill the is_highlight field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportHighlightOrderIndex($aItem, $aArticle)
    {
        if (!empty($aArticle['feature_status'])) {
            if (empty($this->itemHighlightOrderIndex)) {
                $this->itemHighlightOrderIndex = 0;
            }
            $aItem['highlight_order_index'] = $this->itemHighlightOrderIndex++;
        } else {
            $aItem['highlight_order_index'] = 0;
        }

        return $aItem;
    }

    /**
     * Fill the marketplace field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return array
     */
    protected function itemExportMarketplace($aItem, $aArticle)
    {
        $aItem['marketplace'] = 1;

        return $aItem;
    }

    /**
     * Fill the weight field in the given array
     *
     * @param array $aItem
     * @param array $aArticle
     *
     * @return mixed
     */
    protected function itemExportWeight($aItem, $aArticle)
    {
        $aItem['weight'] = $aArticle['products_weight'] * 1000;

        return $aItem;
    }

    /**
     * Takes a list of items and adds option/option_value pairs depending on the options saved for the product
     *
     * @param array $itemList
     * @param array $aArticle
     *
     * @return array
     */
    protected function buildItemOptions($itemList, $aArticle)
    {
        // Accounts only for the first entry (there should also never be passed more than a single item)
        $sgItem    = $itemList[0];
        $itemModel = $this->getItemModelInstance();
        // Get all possible options for the current item
        $sqlQuery = "SELECT\n"
            . "\ttbl_po.products_options_id,\n"
            . "\ttbl_po.products_options_name,\n"
            . "\ttbl_pov.products_options_values_id,\n"
            . "\ttbl_pov.products_options_values_name,\n"
            . "\ttbl_pa.price_prefix,\n" . "\ttbl_pa.options_values_price\n"
            . "FROM " . TABLE_PRODUCTS_ATTRIBUTES . " AS tbl_pa\n"
            . "\tLEFT JOIN " . TABLE_PRODUCTS_OPTIONS
            . " AS tbl_po ON (tbl_pa.options_id = tbl_po.products_options_id)\n"
            . "\tLEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES
            . " AS tbl_pov ON (tbl_pa.options_values_id = tbl_pov.products_options_values_id)\n"
            . "WHERE\n" . "\ttbl_po.language_id = " . ((int)$this->langID) . "\n"
            . "\t\tAND\n" . "\ttbl_pov.language_id = $this->langID\n"
            . "\t\tAND\n" . "\ttbl_pa.products_id = $aArticle[products_id];\n";

        $queryResult   = ShopgateWrapper::db_query_print_on_err($sqlQuery);
        $options       = array();
        $optionNValues = array();
        while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
            $optionId             = $row['products_options_id'];
            $optionIdentifierName = $optionId . '=' . $row['products_options_name'];
            if (!in_array($optionIdentifierName, $options)) {
                $options[]                            = $optionIdentifierName;
                $optionNValues[$optionIdentifierName] = array();
            }
            $row['options_values_price'] = trim($row['options_values_price']);
            $prefixModificator           = $row['price_prefix'] == '+' ? 1 : -1;

            // Add taxes and calculate to integer value!
            $valueId      = $row['products_options_values_id'];
            $valueName    = $row['products_options_values_name'];
            $optionsPrice = $itemModel->getOptionsValuesPrice(
                    $prefixModificator, $row['options_values_price'],
                    $aArticle['tax_rate']
                ) * 100;

            $optionNValues[$optionId . '=' . $row['products_options_name']][]
                = $valueId . '=' . $valueName . '=>' . $optionsPrice;
        }

        if (!empty($options)) {
            // create a string with valid values for each available option
            foreach ($optionNValues as $optionName => $optionValues) {
                $optionNValues[$optionName] = implode('||', $optionValues);
            }

            // build options for the current product
            foreach ($options as $optionIndex => $optionName) {
                // only options_1 to options_10 allowed
                $optionNumber = $optionIndex + 1;
                if ($optionNumber > 10) {
                    break;
                }
                $sgItem['option_' . $optionNumber]             = $optionName;
                $sgItem['option_' . $optionNumber . '_values'] = $optionNValues[$optionName];
            }

            $sgItem['has_options'] = 1;
        }

        $itemList[0] = $sgItem;

        return $itemList;
    }

    /**
     * @inheritdoc
     * @throws ShopgateLibraryException
     */
    protected function createCategories($limit = null, $offset = null, array $uids = array())
    {
        /** @var Shopgate_Helpers_Category $categoryObject */
        $categoryObject = $this->di->make('Shopgate_Helpers_Category');
        $sqlQuery       = $categoryObject->buildCategoryQuery($this->langID, $limit, $offset, $uids);
        $queryResult   = ShopgateWrapper::db_query_print_on_err($sqlQuery);

        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting Categories.", true
            );
        } else {
            while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
                /** @var Shopgate_Helpers_CategoryXml $categoryXmlObj */
                $categoryXmlObj = $this->di->make('Shopgate_Helpers_CategoryXml');
                $categoryXmlObj->setItem($row);
                $this->addCategoryModel($categoryXmlObj->generateData());
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function createCategoriesCsv()
    {
        /** @var Shopgate_Helpers_Category $categoryObject */
        $categoryObject = $this->di->make('Shopgate_Helpers_Category');
        $sqlQuery       = $categoryObject->buildCategoryQuery($this->langID);
        $this->buildCsv($sqlQuery, 'category');
    }

    /**
     * Fill the category_number field in the given array
     *
     * @param array $aCategory
     * @param array $aCategoryRow
     *
     * @return array
     */
    protected function categoryExportCategoryNumber($aCategory, $aCategoryRow)
    {
        $aCategory['category_number'] = $aCategoryRow['categories_id'];

        return $aCategory;
    }

    /**
     * Fill the category_name field in the given array
     *
     * @param array $aCategory
     * @param array $aCategoryRow
     *
     * @return array
     */
    protected function categoryExportCategoryName($aCategory, $aCategoryRow)
    {
        $aCategory['category_name'] = $aCategoryRow['categories_name'];

        return $aCategory;
    }

    /**
     * Fill the parent_id field in the given array
     *
     * @param array $aCategory
     * @param array $aCategoryRow
     *
     * @return array
     */
    protected function categoryExportParentId($aCategory, $aCategoryRow)
    {
        /** @var Shopgate_Helpers_Category $categoryObject */
        $categoryObject         = $this->di->make('Shopgate_Helpers_Category');
        $aCategory['parent_id'] = $categoryObject->buildParentUid(
            $aCategoryRow['parent_id'], $aCategoryRow['categories_id']
        );

        return $aCategory;
    }

    /**
     * Fill the url_image field in the given array
     *
     * @param array $aCategory
     * @param array $aCategoryRow
     *
     * @return array
     */
    protected function categoryExportUrlImage($aCategory, $aCategoryRow)
    {
        /** @var Shopgate_Helpers_Category $categoryObject */
        $categoryObject         = $this->di->make('Shopgate_Helpers_Category');
        $aCategory['url_image'] = $categoryObject->buildCategoryImageUrl($aCategoryRow['categories_image']);

        return $aCategory;
    }

    /**
     * Fill the order_index field in the given array
     *
     * @param array $aCategory
     * @param array $aCategoryRow
     *
     * @return array
     */
    protected function categoryExportOrderIndex($aCategory, $aCategoryRow)
    {
        /** @var Shopgate_Helpers_Category $categoryObject */
        $categoryObject           = $this->di->make('Shopgate_Helpers_Category');
        $aCategory['order_index'] = $categoryObject->buildSortOrder($aCategoryRow['sort_order']);

        return $aCategory;
    }

    /**
     * Fill the is_active field in the given array
     *
     * @param array $aCategory
     * @param array $aCategoryRow
     *
     * @return array
     */
    protected function categoryExportIsActive($aCategory, $aCategoryRow)
    {
        // There is no "active" flag for categories inside the shopping-system, so just set it active
        $aCategory['is_active'] = 1;

        return $aCategory;
    }

    /**
     * Fill the url_deeplink field in the given array
     *
     * @param array $aCategory
     * @param array $aCategoryRow
     *
     * @return array
     */
    protected function categoryExportUrlDeeplink($aCategory, $aCategoryRow)
    {
        /** @var Shopgate_Helpers_Category $categoryObject */
        $categoryObject = $this->di->make('Shopgate_Helpers_Category');
        $aCategory["url_deeplink"] = $categoryObject->buildDeeplink($aCategoryRow["categories_id"]);

        return $aCategory;
    }

    /**
     * @inheritdoc
     */
    protected function createReviewsCsv()
    {
        if (defined('PROJECT_VERSION') && strpos(PROJECT_VERSION, '2.2-LC')) {
            return;
        }
        /** @var Shopgate_Helpers_Reviews $reviewObject */
        $reviewObject = $this->di->make('Shopgate_Helpers_Reviews');
        $sqlQuery     = $reviewObject->buildReviewQuery();
        $this->buildCsv($sqlQuery, 'review');
    }

    /**
     * @inheritdoc
     */
    protected function createReviews($limit = null, $offset = null, array $uids = array())
    {
        if (defined('PROJECT_VERSION') && strpos(PROJECT_VERSION, '2.2-LC')) {
            return;
        }
        /** @var Shopgate_Helpers_Reviews $reviewObject */
        $reviewObject = $this->di->make('Shopgate_Helpers_Reviews');
        $sqlQuery     = $reviewObject->buildReviewQuery($limit, $offset, $uids);
        $queryResult  = ShopgateWrapper::db_query_print_on_err($sqlQuery);

        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting Reviews.", true
            );
        } else {
            while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
                /** @var Shopgate_Helpers_ReviewsXml $reviewXmlObj */
                $reviewXmlObj = $this->di->make('Shopgate_Helpers_ReviewsXml');
                $reviewXmlObj->setItem($row);
                $this->addReviewModel($reviewXmlObj->generateData());
            }
        }
    }

    /**
     * Fill the item_number field in the given array
     *
     * @param array $aReview
     * @param array $aReviewRow
     *
     * @return array
     */
    protected function reviewExportItemNumber($aReview, $aReviewRow)
    {
        $aReview['item_number'] = $aReviewRow['products_id'];

        return $aReview;
    }

    /**
     * Fill the update_review_id field in the given array
     *
     * @param array $aReview
     * @param array $aReviewRow
     *
     * @return array
     */
    protected function reviewExportUpdateReviewId($aReview, $aReviewRow)
    {
        $aReview['update_review_id'] = $aReviewRow['reviews_id'];

        return $aReview;
    }

    /**
     * Fill the score field in the given array
     *
     * @param array $aReview
     * @param array $aReviewRow
     *
     * @return array
     */
    protected function reviewExportScore($aReview, $aReviewRow)
    {
        /** @var Shopgate_Helpers_Reviews $reviewObject */
        $reviewObject     = $this->di->make('Shopgate_Helpers_Reviews');
        $aReview['score'] = $reviewObject->buildScore($aReviewRow['reviews_rating']);

        return $aReview;
    }

    /**
     * Fill the name field in the given array
     *
     * @param array $aReview
     * @param array $aReviewRow
     *
     * @return array
     */
    protected function reviewExportName($aReview, $aReviewRow)
    {
        $aReview['name'] = $aReviewRow['customers_name'];

        return $aReview;
    }

    /**
     * Fill the date field in the given array
     *
     * @param array $aReview
     * @param array $aReviewRow
     *
     * @return array
     */
    protected function reviewExportDate($aReview, $aReviewRow)
    {
        /** @var Shopgate_Helpers_Reviews $reviewObject */
        $reviewObject    = $this->di->make('Shopgate_Helpers_Reviews');
        $aReview['date'] = $reviewObject->buildDate($aReviewRow['date_added']);

        return $aReview;
    }

    /**
     * Fill the title field in the given array
     *
     * @param array $aReview
     * @param array $aReviewRow
     *
     * @return array
     */
    protected function reviewExportTitle($aReview, $aReviewRow)
    {
        /** @var Shopgate_Helpers_Reviews $reviewObject */
        $reviewObject     = $this->di->make('Shopgate_Helpers_Reviews');
        $aReview['title'] = $reviewObject->buildTitle($aReviewRow['reviews_text']);

        return $aReview;
    }

    /**
     * Fill the text field in the given array
     *
     * @param array $aReview
     * @param array $aReviewRow
     *
     * @return array
     */
    protected function reviewExportText($aReview, $aReviewRow)
    {
        $aReview['text'] = $aReviewRow['reviews_text'];

        return $aReview;
    }
}

/**
 * Custom rewrites for US version of OSC
 */
class ShopgatePluginOsCommerceUsa extends ShopgatePluginOsCommerce
{
    /**
     * @inheritdoc
     */
    public function startup()
    {
        parent::startup();
        $this->useTaxClasses();
    }

    /**
     * @inheritdoc
     */
    protected function itemExportWeight($aItem, $aArticle)
    {
        $aItem['weight'] = $aArticle['products_weight'] * self::CONVERT_POUNDS_TO_GRAM_FACTOR;

        return $aItem;
    }

    /**
     * Fill the unit_amount_net field in the given array
     *
     * @param $aItem
     * @param $aArticle
     *
     * @return array
     */
    protected function itemExportUnitAmountNet($aItem, $aArticle)
    {
        $aItem['unit_amount_net'] = $this->getPrice($aArticle, null, $this->exchangeRate);

        return $aItem;
    }

    /**
     * Fill the old_unit_amount_net field in the given array
     *
     * @param $aItem
     * @param $aArticle
     *
     * @return mixed
     */
    protected function itemExportOldUnitAmountNet($aItem, $aArticle)
    {
        $aItem['old_unit_amount_net'] = $this->getOldPrice($aArticle, null, $this->exchangeRate);

        return $aItem;
    }

    /**
     * Fill the tax_class field in the given array
     *
     * @param $aItem
     * @param $aArticle
     *
     * @return mixed
     */
    protected function itemExportTaxClass($aItem, $aArticle)
    {
        $itemModel          = $this->getItemModelInstance();
        $aItem['tax_class'] = $itemModel->buildTaxClass($aArticle);

        return $aItem;
    }
}

/**
 * helps with mapping state codes
 */
class ShopgateMapper
{
    /**
     * The countries with non-ISO-3166-2 state codes in the shopping system are mapped here.
     *
     * @var string[][]
     */
    protected static $stateCodesByCountryCode
        = array(
            'DE' => array(
                "BW" => "BAW",
                "BY" => "BAY",
                "BE" => "BER",
                "BB" => "BRG",
                "HB" => "BRE",
                "HH" => "HAM",
                "HE" => "HES",
                "MV" => "MEC",
                "NI" => "NDS",
                "NW" => "NRW",
                "RP" => "RHE",
                "SL" => "SAR",
                "SN" => "SAS",
                "ST" => "SAC",
                "SH" => "SCN",
                "TH" => "THE",
            ),
            "AT" => array(
                "1" => "BL",
                "2" => "KN",
                "3" => "NO",
                "4" => "OO",
                "5" => "SB",
                "6" => "ST",
                "7" => "TI",
                "8" => "VB",
                "9" => "WI",
            ),
            // "CH" => already correct
            // "US" => already correct
        );

    /**
     * Finds the corresponding Shopgate state code for a given shoppingsystem state code (zone_code).
     *
     * @param string $countryCode The code of the country to which the state belongs
     * @param string $stateCode   The code of the state / zone as found in the default "zones" table of ZenCart
     *
     * @return string The state code as defined at Shopgate Wiki
     *
     * @throws ShopgateLibraryException if one of the given codes is unknown
     */
    public static function getShopgateStateCode($countryCode, $stateCode)
    {
        $countryCode = strtoupper($countryCode);
        $stateCode   = strtoupper($stateCode);

        if (!isset(self::$stateCodesByCountryCode[$countryCode])) {
            return $countryCode . '-' . $stateCode;
        }
        $codes = array_flip(self::$stateCodesByCountryCode[$countryCode]);

        if (!isset($codes[$stateCode])) {
            return $countryCode . '-' . $stateCode;
        }

        $returnStateCode = $codes[$stateCode];

        return $countryCode . '-' . $returnStateCode;
    }

    /**
     * Finds the corresponding shoppingsystem state code (zone_code) for a given Shopgate state code
     *
     * @param string $shopgateStateCode The Shopgate state code as defined at Shopgate Wiki
     *
     * @return null | string The zone code for xt:Commerce 3
     *
     * @throws ShopgateLibraryException if the given code is unknown
     */
    public static function getShoppingsystemStateCode($shopgateStateCode)
    {
        $splitCodes = null;
        preg_match('/^([A-Z]{2})\-([A-Z]{2})$/', $shopgateStateCode, $splitCodes);

        if (empty($splitCodes) || empty($splitCodes[1]) || empty($splitCodes[2])) {
            return null;
        }

        if (!isset(self::$stateCodesByCountryCode[$splitCodes[1]])
            || !isset(self::$stateCodesByCountryCode[$splitCodes[1]][$splitCodes[2]])
        ) {
            return $splitCodes[2];
        } else {
            return self::$stateCodesByCountryCode[$splitCodes[1]][$splitCodes[2]];
        }
    }
}
