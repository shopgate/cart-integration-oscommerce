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
class Shopgate_Di extends tad_DI52_ServiceProvider
{

    /**
     * Binds and sets up implementations.
     */
    public function register()
    {
        $this->container->singleton('ShopgateConfigInterface', 'ShopgateConfigOsCommerce');
        $this->container->singleton('ShopgateOrder', 'ShopgateOrder');
        $this->container->bind('Shopgate_Models_Customers_Info', 'Shopgate_Models_Customers_Info');
        $this->container->setCtor(
            'Shopgate_Helpers_Coupon',
            'Shopgate_Helpers_Coupon',
            '@ShopgateConfigInterface',
            '@Shopgate_Models_Customers_Shopgate',
            '@Shopgate_Helpers_Products',
            '#language_id'
        );
        $this->container->setCtor('Shopgate_Helpers_Products', 'Shopgate_Helpers_Products', '#language_id');
        $this->container->setCtor(
            'Shopgate_Models_Orders_Products',
            'Shopgate_Models_Orders_Products',
            '@Shopgate_Helpers_Products',
            '@ShopgateConfigOsCommerce'
        );
        $this->container->setCtor(
            'Shopgate_Models_Orders_Products_Attributes',
            'Shopgate_Models_Orders_Products_Attributes',
            '@Shopgate_Helpers_Products',
            '@ShopgateConfigOsCommerce'
        );

        $this->rewrites();
    }

    /**
     * Binds and sets up implementations at boot time.
     */
    public function boot()
    {
    }

    /**
     * Left over for adaption rewrites
     */
    public function rewrites()
    {
        //$this->container->bind('Shopgate_Models_Customers_Native', 'Shopgate_Rewrites_Models_Customers_Native');
    }
}
