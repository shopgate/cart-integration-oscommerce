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
 * Native class responsible for 'customers' table
 * @method int getCustomersId()
 * @method $this setCustomersId($id_int)
 * @method string getCustomersGender()
 * @method $this setCustomersGender($gender_str)
 * @method string getCustomersFirstname()
 * @method $this setCustomersFirstname($name_str)
 * @method string getCustomersLastname()
 * @method $this setCustomersLastname($surname_str)
 * @method string getCustomersDob()
 * @method $this setCustomersDob($dob_str)
 * @method string getCustomersEmailAddress()
 * @method $this setCustomersEmailAddress($address_str)
 * @method int getCustomersDefaultAddressId()
 * @method $this setCustomersDefaultAddressId($id_int)
 * @method string getCustomersTelephone()
 * @method $this setCustomersTelephone($phone_str)
 * @method string getCustomersFax()
 * @method $this setCustomersFax($fax_str)
 * @method string getCustomersPassword()
 * @method $this setCustomersPassword($password_str)
 * @method int getCustomersNewsletter()
 * @method $this setCustomersNewsletter($flag_int)
 */
class Shopgate_Models_Customers_Native extends Shopgate_Models_Abstract_Db_Collection
{
    protected $tableName = TABLE_CUSTOMERS;

    /**
     * Returns Shopgate export ready date of birth
     *
     * @return null|string
     */
    public function getShopgateReadyBirthday()
    {
        $dob = date("Y-m-d", strtotime($this->getCustomersDob()));
        if ($dob && $this->getCustomersDob() !== '0000-00-00 00:00:00') {
            return $dob;
        }

        return null;
    }
}
