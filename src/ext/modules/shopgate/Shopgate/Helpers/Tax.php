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
class Shopgate_Helpers_Tax
{
    /**
     * Read the tax class title from the database by the tax value
     *
     * @param $taxValue
     *
     * @return string
     */
    public function getTaxClassByValue($taxValue)
    {
        $query          =
            "SELECT tc.tax_class_title AS title FROM " . TABLE_TAX_RATES . " AS tr
					JOIN " . TABLE_TAX_CLASS . " AS tc ON tc.tax_class_id = tr.tax_class_id
					WHERE tr.tax_rate = {$taxValue}";
        $result         = ShopgateWrapper::db_query($query);
        $taxClassResult = ShopgateWrapper::db_fetch_array($result);

        return $taxClassResult['title'];
    }

    /**
     * Read the tax class uid from the database by product id
     *
     * @param $productsId
     *
     * @return mixed
     */
    public function getProductsTaxClass($productsId)
    {
        $query          = "SELECT products_tax_class_id AS `tax_class_id` FROM "
            . TABLE_PRODUCTS . " AS p WHERE p.products_id = {$productsId}";
        $result         = ShopgateWrapper::db_query($query);
        $taxClassResult = ShopgateWrapper::db_fetch_array($result);

        return $taxClassResult['tax_class_id'];
    }
}
