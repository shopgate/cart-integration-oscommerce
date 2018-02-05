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
 * @method int getCustomersId()
 * @method $this setCustomersId($id_int)
 * @method string getCustomersName()
 * @method $this setCustomersName($name_str)
 * @method string getCustomersCompany()
 * @method $this setCustomersCompany($company_str)
 * @method string getCustomersStreetAddress()
 * @method $this setCustomersStreetAddress($add_str)
 * @method string getCustomersSuburb()
 * @method $this setCustomersSuburb($suburb_str)
 * @method string getCustomersCity()
 * @method $this setCustomersCity($city_str)
 * @method string getCustomersPostcode()
 * @method $this setCustomersPostcode($zip_str)
 * @method string getCustomersState()
 * @method $this setCustomersState($state_str)
 * @method string getCustomersCountry()
 * @method $this setCustomersCountry($country_str)
 * @method string getCustomersTelephone()
 * @method $this setCustomersTelephone($phone_str)
 * @method string getCustomersEmailAddress()
 * @method $this setCustomersEmailAddress($email_str)
 * @method int getCustomersAddressFormatId()
 * @method $this setCustomersAddressFormatId($id_int)
 *
 * @method string getDeliveryName()
 * @method $this setDeliveryName($name_str)
 * @method string getDeliveryCompany()
 * @method $this setDeliveryCompany($company_str)
 * @method string getDeliveryStreetAddress()
 * @method $this setDeliveryStreetAddress($add_str)
 * @method string getDeliverySuburb()
 * @method $this setDeliverySuburb($suburb_str)
 * @method string getDeliveryCity()
 * @method $this setDeliveryCity($city_str)
 * @method string getDeliveryPostcode()
 * @method $this setDeliveryPostcode($zip_str)
 * @method string getDeliveryState()
 * @method $this setDeliveryState($state_str)
 * @method string getDeliveryCountry()
 * @method $this setDeliveryCountry($country_str)
 * @method int getDeliveryAddressFormatId()
 * @method $this setDeliveryAddressFormatId($id_int)
 *
 * @method string getBillingName()
 * @method $this setBillingName($name_str)
 * @method string getBillingCompany()
 * @method $this setBillingCompany($company_str)
 * @method string getBillingStreetAddress()
 * @method $this setBillingStreetAddress($add_str)
 * @method string getBillingSuburb()
 * @method $this setBillingSuburb($suburb_str)
 * @method string getBillingCity()
 * @method $this setBillingCity($city_str)
 * @method string getBillingPostcode()
 * @method $this setBillingPostcode($zip_str)
 * @method string getBillingState()
 * @method $this setBillingState($state_str)
 * @method string getBillingCountry()
 * @method $this setBillingCountry($country_str)
 * @method int getBillingAddressFormatId()
 * @method $this setBillingAddressFormatId($id_int)
 *
 * @method string getPaymentMethod()
 * @method $this setPaymentMethod($method_str)
 * @method string getCcType()
 * @method $this setCcType($cc_type_str)
 * @method string getCcOwner()
 * @method $this setCcOwner($owner_str)
 * @method string getCcNumber()
 * @method $this setCcNumber($cc_num_str)
 * @method string getCcExpires()
 * @method $this setCcExpires($expires_str)
 * @method string getLastModified()
 * @method $this setLastModified($date_str)
 * @method string getDatePurchased()
 * @method $this setDatePurchased($date_str)
 * @method int getOrdersStatus()
 * @method $this setOrdersStatus($status_int)
 * @method string getOrdersDateFinished()
 * @method $this setOrdersDateFinished($date_str)
 * @method string getCurrency()
 * @method $this setCurrency($currency_str)
 * @method float getCurrencyValue()
 * @method $this setCurrencyValue($currency_val_str)
 */
class Shopgate_Models_Orders_Native extends Shopgate_Models_Abstract_Db_Collection
{
    protected $tableName = TABLE_ORDERS;
}
