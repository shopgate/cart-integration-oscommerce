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
 * @method int getCustomersInfoId()
 * @method $this setCustomersInfoId($id_int)
 * @method string getCustomersInfoDateOfLastLogon()
 * @method $this setCustomersInfoDateOfLastLogon($date_str)
 * @method int getCustomersInfoNumberOfLogons()
 * @method $this setCustomersInfoNumberOfLogons($count_int)
 * @method string getCustomersInfoDateAccountCreated()
 * @method $this setCustomersInfoDateAccountCreated($date_str)
 * @method string getCustomersInfoDateAccountLastModified()
 * @method $this setCustomersInfoDateAccountLastModified($date_str)
 * @method int getGlobalProductNotifications()
 * @method $this setGlobalProductNotifications($count_int)
 * @method string getPasswordResetKey()
 * @method $this setPasswordResetKey($key_str)
 * @method string getPasswordResetDate()
 * @method $this setPasswordResetDate($date_str)
 */
class Shopgate_Models_Customers_Info extends Shopgate_Models_Abstract_Db_Collection
{
    protected $tableName = TABLE_CUSTOMERS_INFO;
}