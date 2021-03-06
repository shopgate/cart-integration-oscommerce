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
class Shopgate_Models_Orders_Products_Attributes extends Shopgate_Models_Orders_Products
{
    protected $tableName = TABLE_ORDERS_PRODUCTS_ATTRIBUTES;

    /**
     * @param Shopgate_Helpers_Products $productHelper
     * @param ShopgateConfigOsCommerce  $shopgateConfig
     */
    public function __construct(
        Shopgate_Helpers_Products $productHelper,
        ShopgateConfigOsCommerce $shopgateConfig
    ) {
        $this->productHelper  = $productHelper;
        $this->shopgateConfig = $shopgateConfig;
        parent::__construct($productHelper, $shopgateConfig);
    }
}
