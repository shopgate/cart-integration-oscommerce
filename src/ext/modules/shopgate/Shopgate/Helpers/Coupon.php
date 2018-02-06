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
$file = rtrim(DIR_FS_CATALOG, "/") . "/" . rtrim(DIR_WS_CLASSES, "/") . "/discount_coupon.php";

if (file_exists($file)) {
    include_once($file);
}

/**
 * Handles coupon manipulation
 */
class Shopgate_Helpers_Coupon
{
    /** @var ShopgateConfigOsCommerce */
    protected $config;
    /** @var Shopgate_Helpers_Products */
    protected $productHelper;
    /** @var Shopgate_Models_Customers_Shopgate */
    protected $customersModel;
    /** @var string */
    protected $langID;


    /**
     * @param ShopgateConfigOsCommerce           $config
     * @param Shopgate_Models_Customers_Shopgate $customers
     * @param Shopgate_Helpers_Products          $products
     * @param string                             $langID
     */
    public function __construct(
        ShopgateConfigOsCommerce $config,
        Shopgate_Models_Customers_Shopgate $customers,
        Shopgate_Helpers_Products $products,
        $langID
    ) {
        $this->config         = $config;
        $this->langID         = $langID;
        $this->productHelper  = $products;
        $this->customersModel = $customers;
    }

    /**
     * Check if a coupon date is valid
     *
     * @param array $shopCoupon
     *
     * @return bool
     */
    private function isCouponInsideOfATimeFrame($shopCoupon)
    {
        $now   = strtotime(date("Y-m-d H:i:s"));
        $begin = strtotime($shopCoupon["coupons_date_start"]);
        $end   = strtotime($shopCoupon["coupons_date_end"]);

        return ($now < $begin && $now > $end) ? false : true;
    }

    /**
     * Check if product is in an category which is excluded from the coupon
     *
     * @param $pId
     * @param $code
     *
     * @return bool
     */
    private function isProductExcludedFromCategory($pId, $code)
    {
        $categoryQuery =
            "SELECT count(*) AS cnt 
            FROM " . TABLE_DISCOUNT_COUPONS . " AS dc 
                JOIN " . TABLE_DISCOUNT_COUPONS_TO_CATEGORIES . " AS dctca ON dctca.coupons_id = dc.coupons_id 
                JOIN " . TABLE_CATEGORIES . " AS c ON c.categories_id = dctca.categories_id
                JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " AS ptc ON ptc.categories_id = c.categories_id
                JOIN " . TABLE_PRODUCTS . " AS p ON p.products_id = ptc.products_id 
            WHERE dc.coupons_id = \"{$code}\" AND dc.coupons_max_use > 0 AND p.products_id = {$pId};";
        $result        = ShopgateWrapper::db_query($categoryQuery);
        $categoryCount = ShopgateWrapper::db_fetch_array($result);

        return (!empty($categoryCount) && !empty($categoryCount["cnt"])
            && $categoryCount["cnt"] > 0) ? true : false;
    }

    /**
     * Check if product is excluded for the coupon
     *
     * @param $pId
     * @param $code
     *
     * @return bool
     */
    private function isProductExcluded($pId, $code)
    {
        $productQuery =
            "SELECT count(*) AS cnt 
            FROM " . TABLE_DISCOUNT_COUPONS . " AS dc 
                LEFT JOIN " . TABLE_DISCOUNT_COUPONS_TO_PRODUCTS . " AS dctpr ON dctpr.coupons_id = dc.coupons_id
                LEFT JOIN " . TABLE_PRODUCTS . " AS p ON p.products_id = dctpr.products_id
            WHERE dc.coupons_id = \"{$code}\" AND dc.coupons_max_use > 0 AND p.products_id = {$pId};";
        $result       = ShopgateWrapper::db_query($productQuery);
        $productCount = ShopgateWrapper::db_fetch_array($result);

        return (!empty($productCount) && !empty($productCount["cnt"])
            && $productCount["cnt"] > 0) ? true : false;
    }

    /**
     * Check if manufacturer is excluded for the coupon
     *
     * @param $pId
     * @param $code
     *
     * @return bool
     */
    private function isManufacturerExcluded($pId, $code)
    {
        $manufacturersQuery =
            "SELECT count(*) AS cnt 
            FROM " . TABLE_DISCOUNT_COUPONS . " AS dc 
                JOIN " . TABLE_DISCOUNT_COUPONS_TO_MANUFACTURERS . " AS dctm ON dctm.coupons_id = dc.coupons_id
                JOIN " . TABLE_MANUFACTURERS . " AS m ON m.manufacturers_id = dctm.manufacturers_id
                JOIN " . TABLE_PRODUCTS . " AS p ON p.manufacturers_id = m.manufacturers_id
            WHERE dc.coupons_id = \"{$code}\" AND dc.coupons_max_use > 0 AND p.products_id = {$pId};";
        $result             = ShopgateWrapper::db_query($manufacturersQuery);
        $manufacturerCount  = ShopgateWrapper::db_fetch_array($result);

        return (!empty($manufacturerCount) && !empty($manufacturerCount["cnt"])
            && $manufacturerCount["cnt"] > 0) ? true : false;
    }

    /**
     * Check if customer is excluded for the coupon
     *
     * @param $code
     * @param $customerId
     *
     * @return bool
     */
    private function isCustomerExcluded($code, $customerId)
    {
        $customerQuery =
            "SELECT count(*) AS cnt 
            FROM " . TABLE_DISCOUNT_COUPONS . " AS dc 
                LEFT JOIN " . TABLE_DISCOUNT_COUPONS_TO_CUSTOMERS . " AS dctc ON dctc.coupons_id = dc.coupons_id
                LEFT JOIN " . TABLE_CUSTOMERS . " AS c ON c.customers_id = dctc.customers_id
            WHERE dc.coupons_id = \"{$code}\" AND dc.coupons_max_use > 0 AND c.customers_id = {$customerId};";

        $result        = ShopgateWrapper::db_query($customerQuery);
        $customerCount = ShopgateWrapper::db_fetch_array($result);

        return (!empty($customerCount) && !empty($customerCount["cnt"])
            && $customerCount["cnt"] > 0) ? true : false;
    }

    /**
     * Check if customer has orders
     *
     * @param $code
     * @param $customerId
     *
     * @return bool
     */
    private function customerHasOrders($code, $customerId)
    {
        $orderQuery =
            "SELECT count(*) AS cnt 
            FROM " . TABLE_DISCOUNT_COUPONS . " AS dc 
                LEFT JOIN " . TABLE_DISCOUNT_COUPONS_TO_ORDERS . " AS dcto ON dcto.coupons_id = dc.coupons_id
                LEFT JOIN " . TABLE_ORDERS . " AS o ON o.orders_id = dcto.orders_id
            WHERE dc.coupons_id = \"{$code}\" AND dc.coupons_max_use > 0 AND o.customers_id = {$customerId};";

        $result     = ShopgateWrapper::db_query($orderQuery);
        $orderCount = ShopgateWrapper::db_fetch_array($result);

        return (!empty($orderCount) && !empty($orderCount["cnt"]) && $orderCount["cnt"] > 0) ? true : false;
    }

    /**
     * redeem the coupon by code
     *
     * @param $code
     *
     * @return ressource
     */
    public function redeemValidCouponByCode($code)
    {
        $query = "UPDATE discount_coupons AS dc 
                        SET dc.coupons_number_available = dc.coupons_number_available - 1 
                      WHERE dc.coupons_id = \"{$code}\" 
                          AND dc.coupons_number_available > 0;";

        return ShopgateWrapper::db_query($query);
    }

    /**
     * Check the validity of all coupons which the ShopgateCart object contains
     *
     * @param ShopgateCart $cart
     *
     * @return array | ShopgateExternalCoupon[]
     */
    public function checkCoupon(ShopgateCart $cart)
    {
        //Check if ot_discount module is installed
        if (!defined("MODULE_ORDER_TOTAL_INSTALLED")
            || strpos(MODULE_ORDER_TOTAL_INSTALLED, "ot_discount_coupon.php") === false
        ) {
            return array();
        }

        $file = (rtrim(DIR_FS_CATALOG, "/") . "/" . rtrim(DIR_WS_CLASSES, "/") . "/discount_coupon.php");

        if (!file_exists($file)) {
            return array();
        }

        include_once($file);

        $coupons              = $cart->getExternalCoupons();
        $productAmountWithTax = $this->productHelper->getProductAmountWithTax($cart);
        $hasValidCoupon       = false;

        foreach ($coupons as &$coupon) {
            $code       = $coupon->getCode();
            $osCoupon   = new discount_coupon($code, $this->customersModel->getAddress($cart));
            $shopCoupon = $osCoupon->coupon;

            $coupon->setCurrency($this->config->getCurrency());
            $coupon->setName($shopCoupon["coupons_description"]);

            if (empty($customer_id)) {
                $coupon->setIsValid(false);
                $coupon->setNotValidMessage("Customer not registered in shop system");
                continue;
            }

            /* Coupon exist */
            if (empty($shopCoupon)) {
                $coupon->setIsValid(false);
                $coupon->setNotValidMessage("Coupon not found");
                continue;
            }

            /* Coupon in time windows */
            if (!$this->isCouponInsideOfATimeFrame($shopCoupon)) {
                $coupon->setIsValid(false);
                $coupon->setNotValidMessage("Coupon out of date");
                continue;
            }

            $minOrderType = $shopCoupon["coupons_min_order_type"];

            if ($minOrderType == "price") {
                if ($productAmountWithTax < $shopCoupon["coupons_min_order"]) {
                    $coupon->setIsValid(false);
                    $coupon->setNotValidMessage(
                        "Minimum order value not reached"
                    );
                } else {
                    $coupon->setIsValid(true);
                }
            } elseif ($minOrderType == "quantity") {
                if (count($cart->getItems()) < $shopCoupon["coupons_min_order"]) {
                    $coupon->setIsValid(false);
                    $coupon->setNotValidMessage("Coupon out of date");
                    continue;
                } else {
                    $coupon->setIsValid(true);
                }
            }

            switch ($shopCoupon["coupons_discount_type"]) {
                case "percent":
                    $coupon->setAmountGross($productAmountWithTax * $shopCoupon["coupons_discount_amount"]);
                    break;
                case "fixed":
                    $coupon->setAmountGross($shopCoupon["coupons_discount_amount"]);
                    break;
                case "shipping":
                    $coupon->setAmountGross($cart->getAmountShipping() * $shopCoupon["coupons_discount_amount"]);
                    break;
                default:
                    break;
            }

            $products = $cart->getItems();

            if (count($products) == 0) {
                $coupon->setIsValid(false);
                $coupon->setNotValidMessage('No products');
                break;
            }

            foreach ($products as $product) {
                $pId = $product->getItemNumber();

                /* excluded categories    */
                if ($this->isProductExcludedFromCategory($pId, $code)) {
                    $coupon->setIsValid(false);
                    $coupon->setNotValidMessage('Some or all of the products are in an excluded category');
                    continue(2);
                }

                /* excluded products */
                if ($this->isProductExcluded($pId, $code)) {
                    $coupon->setIsValid(false);
                    $coupon->setNotValidMessage(
                        'Some or all of the products in your cart are excluded from this coupon'
                    );
                    continue(2);
                }

                /*    excluded manufacturers    */
                if ($this->isManufacturerExcluded($pId, $code)) {
                    $coupon->setIsValid(false);
                    $coupon->setNotValidMessage('Some or all of the manufacturers in your cart are excluded');
                    continue(2);
                }
            } // EOF product iteration

            /* excluded customers*/
            if ($this->isCustomerExcluded($code, $customer_id)) {
                $coupon->setIsValid(false);
                $coupon->setNotValidMessage('customer is excluded for this coupon');
                continue;
            }

            /* customer never ordered in this shop */
            if ($this->customerHasOrders($code, $customer_id)) {
                $coupon->setIsValid(false);
                $coupon->setNotValidMessage('Customer never ordered something in this shop.');
                continue;
            }

            if ($coupon->getIsValid() && !$hasValidCoupon) {
                $hasValidCoupon = true;
            } elseif ($coupon->getIsValid() && $hasValidCoupon) {
                $coupon->setIsValid(false);
                $coupon->setNotValidMessage('This coupon code can\'t be used with others at the same time.');
            }
        }

        return $coupons;
    }
}
