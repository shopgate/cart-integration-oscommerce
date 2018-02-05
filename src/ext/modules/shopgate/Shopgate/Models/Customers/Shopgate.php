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
 * Shopgate customer table class
 *
 * @method $this setCustomerId($id_int)
 * @method int getCustomerId()
 * @method $this setCustomerToken($token_str)
 * @method string getCustomerToken()
 */
class Shopgate_Models_Customers_Shopgate extends Shopgate_Models_Abstract_Db_Collection
{
    protected $tableName   = TABLE_CUSTOMERS_SHOPGATE_CUSTOMER;
    protected $idFieldName = 'customer_id';
    /** @var Shopgate_Helpers_Zones */
    protected $zoneHelper;
    /** @var Shopgate_Models_Customers_Native */
    protected $nativeCustomerModel;

    /**
     * @inheritdoc
     */
    public function __construct(
        Shopgate_Helpers_Zones $zoneHelper,
        Shopgate_Models_Customers_Native $customerModel
    ) {
        $this->zoneHelper          = $zoneHelper;
        $this->nativeCustomerModel = $customerModel;
        parent::__construct();
    }

    /**
     * Add zone and country uid to existing cart address
     *
     * @param ShopgateCart $cart
     *
     * @return array
     */
    public function getAddress(ShopgateCart $cart)
    {
        $deliveryAddress = $cart->getDeliveryAddress();
        $address         = empty($deliveryAddress)
            ? $cart->getInvoiceAddress()
            : $deliveryAddress;

        $delivery = array();

        if (!empty($address)) {
            $delivery['zone_id']    =
                $this->getStateId(ShopgateMapper::getShoppingsystemStateCode($address->getState()));
            $delivery['country_id'] =
                $this->getCountryId($address->getCountry());
        }

        return $delivery;
    }

    /**
     * Read the state id from the database by the zone code
     *
     * @param $currentState
     *
     * @return mixed
     */
    private function getStateId($currentState)
    {
        $qry         = "SELECT z.zone_id FROM " . TABLE_ZONES . " AS z where z.zone_code = \"{$currentState}\" ;";
        $result      = ShopgateWrapper::db_query_print_on_err($qry);
        $resultArray = ShopgateWrapper::db_fetch_array($result);

        return $resultArray['zone_id'];
    }

    /**
     * Read the country id from the database by the country iso-2 code
     *
     * @param string $country
     *
     * @return int|null
     */
    private function getCountryId($country)
    {
        return $this->zoneHelper->getCountryByIso2Name($country)->getId();
    }

    /**
     * Generates a token to a customer if no exist
     *
     * Gets token or creates one if needed
     *
     * @param ShopgateCustomer $customer
     *
     * @return null|string
     */
    public function getTokenForCustomer(ShopgateCustomer $customer)
    {
        $token = $this->getToken($customer);
        if (!$token) {
            $id = $this->getIdFromEmail($customer->getMail());
            if ($id) {
                $token = $this->createToken($customer);
                $customer->setCustomerToken($token);
                $this->saveToken($customer);
            }
        }

        return $token;
    }

    /**
     * Get generated token by customer
     *
     * @param ShopgateCustomer $customer
     *
     * @return string|null
     */
    private function getToken(ShopgateCustomer $customer)
    {
        $this->load($customer->getCustomerId());

        return $this->getData('customer_token');
    }

    /**
     * Read the customers database id by his email
     *
     * Simple ID retrieval
     *
     * @param $email - customer's email address
     *
     * @return int|null
     */
    private function getIdFromEmail($email)
    {
        return $this->nativeCustomerModel->load($email, 'customers_email_address')->getCustomersId();
    }

    /**
     * Returns customer token based on
     *
     * @param ShopgateCustomer $customer
     *
     * @return string
     */
    private function createToken(ShopgateCustomer $customer)
    {
        return md5($customer->getMail() . $customer->getCustomerId());
    }

    /**
     * stores a token to an customer in the database
     *
     * @param ShopgateCustomer $customer
     *
     * @return $this
     */
    private function saveToken(ShopgateCustomer $customer)
    {
        $this
            ->setCustomerId($customer->getCustomerId())
            ->setCustomerToken($customer->getCustomerToken());

        return $this->save();
    }

    /**
     * Get the customers database id by the customers token
     *
     * @param string $token
     *
     * @return int|null
     */
    public function getIdFromToken($token)
    {
        $this->load($token, 'customer_token');

        return $this->getId();
    }
}
