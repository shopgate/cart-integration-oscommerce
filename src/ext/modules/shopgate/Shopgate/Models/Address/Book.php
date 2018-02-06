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
 * @method int getAddressBookId()
 * @method $this setAddressBookId($id_int)
 * @method int getCustomersId()
 * @method $this setCustomersId($id_int)
 * @method string getEntryGender()
 * @method $this setEntryGender($gender_str)
 * @method string getEntryCompany()
 * @method $this setEntryCompany($company_str)
 * @method string getEntryFirstname()
 * @method $this setEntryFirstname($firstName_str)
 * @method string getEntryLastname()
 * @method $this setEntryLastname($lastName_str)
 * @method string getEntryStreetAddress()
 * @method $this setEntryStreetAddress($address_str)
 * @method string getEntrySuburb()
 * @method $this setEntrySuburb($suburb_str)
 * @method string getEntryPostcode()
 * @method $this setEntryPostcode($zip_str)
 * @method string getEntryCity()
 * @method $this setEntryCity($city_str)
 * @method string getEntryState()
 * @method $this setEntryState($state_str)
 * @method int getEntryCountryId()
 * @method $this setEntryCountryId($countryId_int)
 * @method int getEntryZoneId()
 * @method $this setEntryZoneId($zoneId_int)
 */
class Shopgate_Models_Address_Book extends Shopgate_Models_Abstract_Db_Collection
{
    protected $tableName = TABLE_ADDRESS_BOOK;
}
