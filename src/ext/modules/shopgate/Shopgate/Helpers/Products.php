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
class Shopgate_Helpers_Products extends ShopgateObject
{
    /**
     * @var int
     */
    protected $langID;

    /**
     * @param int $langID
     */
    public function __construct($langID)
    {
        $this->langID = $langID;
    }

    /**
     * Uses ShopgateOrderItem data to query products data from the shop systems database.
     * Furthermore the products unique id and the stock quantity will be set in an ShopgateCartItems
     * object.
     *
     * @param ShopgateOrderItem $item
     * @param ShopgateCartItem  $sgProduct
     *
     * @return ShopgateCartItem mixed
     */
    public function generateCartItemProduct($item, $sgProduct)
    {
        $query     = "SELECT * FROM " . TABLE_PRODUCTS . " AS p
                    JOIN " . TABLE_PRODUCTS_DESCRIPTION . " AS pd ON p.products_id = pd.products_id
                    WHERE p.products_id = {$this->getProductIdFromCartItems($item)} AND pd.language_id = {$this->langID}";
        $dbProduct = ShopgateWrapper::db_fetch_array(ShopgateWrapper::db_query($query));
        $sgProduct->setItemNumber($dbProduct["products_id"]);
        $sgProduct->setStockQuantity($dbProduct["products_quantity"]);

        return $sgProduct;
    }

    /**
     * get the real item id as it is used by the shop system
     *
     * @param ShopgateOrderItem $cartItem
     *
     * @return mixed
     */
    public function getProductIdFromCartItems($cartItem)
    {
        $productId = $cartItem->getParentItemNumber();

        return (empty($productId)) ? $cartItem->getItemNumber() : $productId;
    }

    /**
     * generate input fields from cart item
     *
     * @param ShopgateOrderItem $item
     * @param ShopgateCartItem  $sgProduct
     *
     * @return ShopgateCartItem mixed
     */
    public function getCartItemInputFields($item, $sgProduct)
    {
        // todo-sg: not supported in osc yet
        return array();
    }

    /**
     * read the whole attribute data to an product from the database
     *
     * @param ShopgateOrderItem $item
     * @param ShopgateCartItem  $sgProduct
     *
     * @return ShopgateCartItem mixed
     */
    public function getCartItemAttributes($item, $sgProduct)
    {
        $attributes   = $sgProduct->getAttributes();
        $dbAttributes = array();
        foreach ($attributes as $attribute) {
            $query  = "SELECT 
                    po.products_options_name AS `name`,
                    pov.products_options_values_name AS `value`
                    FROM " . TABLE_PRODUCTS . " AS p
                    LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " AS pa ON p.products_id = pa.products_id
                    LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " AS po ON (pa.options_id = po.products_options_id AND po.language_id = 1)
                    LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " AS pov ON (pa.options_values_id = pov.products_options_values_id AND po.language_id = pov.language_id)
                    WHERE pa.products_id = {$this->getProductIdFromCartItems($item)} 
                    AND po.products_options_name = '{$attribute->getName()}' 
                    AND pov.products_options_values_name = '{$attribute->getValue()}'";
            $result = ShopgateWrapper::db_query($query);

            while ($dbProductAttributes =
                ShopgateWrapper::db_fetch_array($result)) {
                $sgAttribute = new ShopgateOrderItemAttribute();
                $sgAttribute->setName($dbProductAttributes["option_name"]);
                $sgAttribute->setValue($dbProductAttributes["value"]);
                $dbAttributes[] = $sgAttribute;
            }
        }
        $sgProduct->setAttributes($dbAttributes);

        return $sgProduct;
    }

    /**
     * read the whole options data to an product from the database
     *
     * @param ShopgateOrderItem $item
     * @param ShopgateCartItem  $sgProduct
     * @param                   $taxRate
     *
     * @return ShopgateCartItem mixed
     */
    public function getCartItemOptions($item, $sgProduct, $taxRate)
    {
        $options   = $item->getOptions();
        $dbOptions = array();
        foreach ($options as $option) {
            $query = "SELECT 
                    po.products_options_name AS `name`,
                    po.products_options_id AS `option_id`,
                    pov.products_options_values_id AS `value_id`,
                    pov.products_options_values_name AS `value`,
                    pa.price_prefix AS `prefix`,
                    pa.options_values_price AS `price`
                    FROM " . TABLE_PRODUCTS . " AS p
                    LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " AS pa ON p.products_id = pa.products_id
                    LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " AS po ON (pa.options_id = po.products_options_id AND po.language_id = 1)
                    LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " AS pov ON (pa.options_values_id = pov.products_options_values_id AND po.language_id = pov.language_id)
                    WHERE pa.products_id = {$this->getProductIdFromCartItems($item)} 
                    AND pa.options_id = {$option->getOptionNumber()} 
                    AND pov.products_options_values_id = {$option->getValueNumber()}";

            $result = ShopgateWrapper::db_query($query);

            while ($dbProductOptions =
                ShopgateWrapper::db_fetch_array($result)) {
                $sgOption = new ShopgateOrderItemOption();
                $sgOption->setName($dbProductOptions["option_name"]);
                $sgOption->setValue($dbProductOptions["value"]);
                $sgOption->setOptionNumber($dbProductOptions["option_id"]);
                $sgOption->setValueNumber($dbProductOptions["value_id"]);
                $optionPrice = (!empty($taxRate)) ?
                    $dbProductOptions["price"] * (1 + ($taxRate / 100))
                    : $dbProductOptions["price"];
                $sgOption->setAdditionalAmountWithTax($optionPrice);
                $dbOptions[] = $sgOption;
            }
        }
        $sgProduct->setOptions($dbOptions);

        return $sgProduct;
    }

    /**
     * calculate the complete amount (incl. vat) of a Shopgate cart
     *
     * @param ShopgateCart $cart
     *
     * @return float|int
     */
    public function getProductAmountWithTax(ShopgateCart $cart)
    {
        $products = $cart->getItems();
        $amount   = 0;
        foreach ($products as $product) {
            $amount += ($product->getUnitAmountWithTax()
                * $product->getQuantity());
        }

        return $amount;
    }

    /**
     * calculate the products weight
     *
     * @param ShopgateOrderItem[] $products
     *
     * @return mixed
     */
    public function getProductsWeight($products)
    {
        $weight = 0;
        foreach ($products as $product) {
            $query         =
                "SELECT p.products_weight AS weight FROM " . TABLE_PRODUCTS
                . " AS p WHERE p.products_id = " . $this->getProductIdFromCartItems($product);
            $result        = ShopgateWrapper::db_query($query);
            $productweight = ShopgateWrapper::db_fetch_array($result);
            $weight += $productweight['weight'] * $product->getQuantity();
        }

        return $weight;
    }
}
