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
require_once(rtrim(DIR_FS_CATALOG, "/") . '/ext/modules/shopgate/base/shopgate_config.php');
require_once(rtrim(DIR_FS_CATALOG, "/") . '/ext/modules/shopgate/plugin.php');

/**
 * Wrapper for setShopgateOrderlistStatus() with only one order.
 *
 * For compatibility reasons.
 *
 * @param int $orderId The ID of the order in the shop system.
 * @param int $status  The ID of the order status that has been set in the shopping system.
 */
function onUpdateOrdersStatus($orderId, $status)
{
    if (empty($orderId)) {
        return;
    }

    setShopgateOrderlistStatus(array($orderId), $status);
}

/**
 * Wrapper for ShopgatePluginOsCommerce::updateOrdersStatus(). Set the shipping status for a list of order IDs.
 *
 * @param int[] $orderIds The IDs of the orders in the shop system.
 * @param int   $status   The ID of the order status that has been set in the shopping system.
 */
function setShopgateOrderlistStatus($orderIds, $status)
{
    if (empty($orderIds) || !is_array($orderIds)) {
        return;
    }

    try {
        $plugin = new ShopgatePluginOsCommerce();
        $plugin->updateOrdersStatus($orderIds, $status);
    } catch (Exception $e) {
        // Make sure code flow does not stop here by catching all remaining exceptions
        return;
    }
}
