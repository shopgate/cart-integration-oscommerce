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
 * @method int getZoneId()
 * @method $this setZoneId($id_int)
 * @method int getZoneCountryId()
 * @method $this setZoneCountryId($id_int)
 * @method int getZoneCode()
 * @method $this setZoneCode($code_str)
 * @method int getZoneName()
 * @method $this setZoneName($name_str)
 */
class Shopgate_Models_Zones_Native extends Shopgate_Models_Abstract_Db_Collection
{
    protected $tableName   = TABLE_ZONES;
    protected $idFieldName = 'zone_id';
}