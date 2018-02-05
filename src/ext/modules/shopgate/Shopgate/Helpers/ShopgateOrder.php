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
 * Manipulates shopgate order beyond the regular functionality
 */
class Shopgate_Helpers_ShopgateOrder
{
    /** @var ShopgateOrder */
    protected $shopgateOrder;
    /** @var Shopgate_Models_Products_Native */
    protected $productModel;

    /**
     * @param ShopgateOrder                   $shopgateOrder
     * @param Shopgate_Models_Products_Native $productModel
     */
    public function __construct(ShopgateOrder $shopgateOrder, Shopgate_Models_Products_Native $productModel)
    {
        $this->shopgateOrder = $shopgateOrder;
        $this->productModel  = $productModel;
    }

    /**
     * Checks whether cart items exist in the system,
     * throws an exception if they do not.
     *
     * @return bool
     * @throws ShopgateLibraryException
     */
    public function validateItemsExist()
    {
        $itemIds     = $this->getItemIds();
        $implodedIds = implode(',', $itemIds);
        $this->productModel->getSelect()
                           ->where('products_id IN (' . $implodedIds . ')');
        $this->productModel->getCollection();

        if ($this->productModel->getSize() !== count($itemIds)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_ORDER_ITEM_NOT_FOUND,
                "Collection ids expected: '{$implodedIds}' returned: '{$this->productModel->getAllIds()}'", true, true
            );
        }

        return true;
    }

    /**
     * Gets all item id's in the cart. See coupon skip flag.
     *
     * @param bool $removeCoupons - whether to remove coupon items & payment fees
     *
     * @return int[]
     */
    public function getItemIds($removeCoupons = true)
    {
        $itemIds = array();

        /**
         * @var ShopgateOrderItem $item
         */
        foreach ($this->shopgateOrder->getItems() as $item) {
            if ($removeCoupons && ($item->isSgCoupon() || $item->isPayment())) {
                continue;
            }
            $itemIds[] = (int)$item->getItemNumber();
        }

        return array_unique($itemIds);
    }
}
