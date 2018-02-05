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
class Shopgate_Helpers_Zones
{
    /** @var Shopgate_Models_Countries_Native */
    protected $countryModel;
    /** @var Shopgate_Models_Zones_Native */
    protected $zoneModel;

    /**
     * @param Shopgate_Models_Countries_Native $countryModel
     * @param Shopgate_Models_Zones_Native     $zoneModel
     */
    public function __construct(Shopgate_Models_Countries_Native $countryModel, Shopgate_Models_Zones_Native $zoneModel)
    {
        $this->countryModel = $countryModel;
        $this->zoneModel    = $zoneModel;
    }

    /**
     * Read the country id from the database by iso-2 code
     *
     * @param string $name
     *
     * @return mixed
     * @deprecated
     */
    public function getCountryIdByName($name)
    {
        $query         =
            "SELECT c.countries_id AS id FROM " . TABLE_COUNTRIES . " AS c WHERE c.countries_iso_code_2 = \"{$name}\"";
        $result        = ShopgateWrapper::db_query($query);
        $countryResult = ShopgateWrapper::db_fetch_array($result);

        return $countryResult['id'];
    }

    /**
     * Retrieves country object from database by iso-2 code
     *
     * @param string $name
     *
     * @return Shopgate_Models_Countries_Native
     */
    public function getCountryByIso2Name($name)
    {
        $this->countryModel->load($name, 'countries_iso_code_2');

        return $this->countryModel;
    }

    /**
     * read the zone data from database by the zone country id
     *
     * @param string | int $zoneCountryId
     *
     * @return array
     */
    public function getZoneByCountryId($zoneCountryId)
    {
        $query         = "SELECT * FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE zone_country_id = '" . $zoneCountryId
            . "' ORDER BY zone_id";
        $result        = ShopgateWrapper::db_query($query);
        $countryResult = ShopgateWrapper::db_fetch_array($result);

        return $countryResult;
    }

    /**
     * reads the country data for the shipping module "flat" from the database
     *
     * @param string | int $zoneCountryId
     *
     * @return array
     */
    public function getZoneByCountryIdModuleShippingFlat($zoneCountryId)
    {
        $query         =
            "SELECT * FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . MODULE_SHIPPING_FLAT_ZONE
            . "' AND zone_country_id = '" . $zoneCountryId . "' ORDER BY zone_id";
        $result        = ShopgateWrapper::db_query($query);
        $countryResult = ShopgateWrapper::db_fetch_array($result);

        return $countryResult;
    }

    /**
     * read the zone id from database by the zone code and country id
     *
     * @param string | int $countryId
     * @param string       $zoneCode
     *
     * @return mixed
     */
    public function getCustomerZoneId($countryId, $zoneCode)
    {
        $query      =
            "select z.zone_id from " . TABLE_ZONES
            . " as z where z.zone_country_id = {$countryId} AND z.zone_code = '{$zoneCode}'";
        $result     = ShopgateWrapper::db_query($query);
        $zoneResult = ShopgateWrapper::db_fetch_array($result);

        return $zoneResult['zone_id'];
    }
}
