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
class Shopgate_Helpers_Shipping
{
    /** @var Shopgate_Helpers_Tax */
    protected $taxHelper;
    /** @var Shopgate_Helper_Pricing */
    protected $pricingHelper;
    /** @var ShopgateShippingMethod */
    protected $shippingMethod;

    /**
     * @param Shopgate_Helpers_Tax    $taxHelper
     * @param Shopgate_Helper_Pricing $pricingHelper
     * @param ShopgateShippingMethod  $shippingMethod
     */
    public function __construct(
        Shopgate_Helpers_Tax $taxHelper,
        Shopgate_Helper_Pricing $pricingHelper,
        ShopgateShippingMethod $shippingMethod
    ) {
        $this->taxHelper      = $taxHelper;
        $this->pricingHelper  = $pricingHelper;
        $this->shippingMethod = $shippingMethod;
    }

    /**
     * Parses the passed shipping modules
     * and pulls the shipping methods
     * into exportable methods
     *
     * @param array $shippingModules
     *
     * @return ShopgateShippingMethod[]
     */
    public function getShippingMethodExport(array $shippingModules)
    {
        $exportMethods = array();
        foreach ($shippingModules as $module) {
            //we don`t support usps as shopgate plugin shipping method
            //also on error continue
            if (strpos($module['module'], 'United States Postal Service') !== false
                || !empty($module['error'])
                || !is_array($module['methods'])
            ) {
                continue;
            }

            foreach ($module['methods'] as $method) {
                /** @var ShopgateShippingMethod $exportMethod */
                $exportMethod = new $this->shippingMethod;
                $cost         = $method['cost'];
                $exportMethod->setId(empty($method['id']) ? $module['id'] : $method['id']);
                $exportMethod->setTitle(empty($method['title']) ? $module['module'] : $method['title']);

                if (!empty($module['tax'])) {
                    $exportMethod->setTaxClass($this->taxHelper->getTaxClassByValue($module['tax']));
                    $exportMethod->setTaxPercent($module['tax']);
                    $costWithTax =
                        $this->pricingHelper->formatPriceNumber($cost * (1 + ($module['tax'] / 100)), 2);
                    $exportMethod->setAmountWithTax($costWithTax);
                    $exportMethod->setAmount($cost);
                } else {
                    $exportMethod->setTaxPercent(0);
                    $exportMethod->setAmountWithTax($cost);
                    $exportMethod->setAmount($cost);
                }

                $exportMethods[] = $exportMethod;
            }
        }

        return $exportMethods;
    }
}
