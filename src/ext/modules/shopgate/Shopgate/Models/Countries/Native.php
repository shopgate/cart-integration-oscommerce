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
 * @method int getCountriesId()
 * @method $this setCountriesId($id_int)
 * @method string getCountriesName()
 * @method $this setCountriesName($name_str)
 * @method string getCountriesIsoCode2()
 * @method $this setCountriesIsoCode2($code_str)
 * @method string getCountriesIsoCode3()
 * @method $this setCountriesIsoCode3($code_str)
 * @method int getAddressFormatId()
 * @method $this setAddressFormatId($id_int)
 */
class Shopgate_Models_Countries_Native extends Shopgate_Models_Abstract_Db_Collection
{
    protected $tableName = TABLE_COUNTRIES;
}