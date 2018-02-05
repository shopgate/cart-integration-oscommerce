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
 * Adding support to older versions of OsCommerce non-existent
 * methods
 */
class Shopgate_Helpers_NativeCurrencies extends currencies
{

    /**
     * Method didn't exist in osCommerce < 2.2 RC1
     *
     * @param     $products_price
     * @param     $products_tax
     * @param int $quantity
     *
     * @return float|string
     */
    function calculate_price($products_price, $products_tax, $quantity = 1)
    {
        if (method_exists('currencies', __FUNCTION__)) {
            return parent::calculate_price($products_price, $products_tax, $quantity);
        }

        global $currency;

        return
            tep_round(tep_add_tax($products_price, $products_tax), $this->currencies[$currency]['decimal_places'])
            * $quantity;
    }
}
