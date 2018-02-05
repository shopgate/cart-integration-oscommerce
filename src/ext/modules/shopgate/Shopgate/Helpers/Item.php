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
class Shopgate_Helpers_Item extends Shopgate_Model_Catalog_Product
{
    /**
     * @var float
     */
    protected $_exchangeRate;

    /**
     * @var string
     */
    protected $_currency;

    /**
     * @var string
     */
    protected $_swisscartVersion;

    /**
     * @var string
     */
    protected $_swisscartImageCount;

    /**
     * @var string
     */
    protected $_language;

    /**
     * @var
     */
    protected $_taxZone;

    /**
     * @var int
     */
    protected $_isUsaPlugin = 0;

    /**
     * @var int
     */
    protected $_langId;

    /**
     * @param $value
     */
    public function setLanguageId($value)
    {
        $this->_langId = $value;
    }

    /**
     * @param $value
     */
    public function setIsUsaPlugin($value)
    {
        $this->_isUsaPlugin = $value;
    }

    /**
     * @param $value
     */
    public function setTaxZone($value)
    {
        $this->_taxZone = $value;
    }

    /**
     * @param string $value
     */
    public function setStoreCurrency($value)
    {
        $this->_currency = $value;
    }

    /**
     * @param string $value
     */
    public function setStoreLanguage($value)
    {
        $this->_language = $value;
    }

    /**
     * @param int $value
     */
    public function setSwisscartImageCount($value)
    {
        $this->_swisscartImageCount = $value;
    }

    /**
     * @return int $value
     */
    public function getSwisscartImageCount()
    {
        return $this->_swisscartImageCount;
    }

    /**
     * @param string $value
     */
    public function setSwisscartVersion($value)
    {
        $this->_swisscartVersion = $value;
    }

    /**
     * @param float $value
     */
    public function setStoreExchangeRate($value)
    {
        $this->_exchangeRate = $value;
    }

    /**
     * check if a product exist in the database
     *
     * @param $productsId
     *
     * @return bool
     */
    public function productExist($productsId)
    {
        $query  = "SELECT * FROM " . TABLE_PRODUCTS . " AS p WHERE p.products_id = {$productsId}";
        $result = ShopgateWrapper::db_query($query);

        return (ShopgateWrapper::db_num_rows($result) > 0) ? true : false;
    }

    /**
     * read the tax rate from the database by the product uid
     *
     * @param $productsId
     *
     * @return mixed
     */
    public function getTaxrateToProduct($productsId)
    {
        $query  = "SELECT p.products_tax_class_id AS `tax_class_id` FROM " . TABLE_PRODUCTS
            . " AS p WHERE p.products_id = {$productsId}";
        $result = ShopgateWrapper::db_query($query);
        $row    = ShopgateWrapper::db_fetch_array($result);

        return $row["tax_class_id"];
    }

    /**
     * read product data from the database by the product uid
     *
     * @param $productsId
     *
     * @return mixed[]
     */
    public function getProduct($productsId)
    {
        $query  = "SELECT * FROM " . TABLE_PRODUCTS . " AS p WHERE p.products_id = {$productsId}";
        $result = ShopgateWrapper::db_query($query);

        return ShopgateWrapper::db_fetch_array($result);
    }

    /**
     * validates the image url by:
     * - check if image file is locally reachable
     * - check if image is reachable through the url
     *
     * @param string $imgUrl
     *
     * @return string
     */
    public function validateImageUrl($imgUrl)
    {
        $result = '';
        if (empty($imgUrl)) {
            return $result;
        }

        if (preg_match("/^\S*http\S*/", $imgUrl)) {
            $result = $imgUrl;
        } elseif (defined('DIR_WS_IMAGES_PRODUCTS')) {
            $result = $this->getFullImagePath(DIR_WS_IMAGES_PRODUCTS . $imgUrl);
        } elseif (defined('DIR_WS_IMAGES')) {
            $result = $this->getFullImagePath(DIR_WS_IMAGES . $imgUrl);
        }

        return $result;
    }

    /**
     * Helps get the full path of the image
     *
     * @param string $imagePath - e.g. images/products/test.jpeg
     *
     * @return string
     */
    protected function getFullImagePath($imagePath)
    {
        $absoluteImagePath = DIR_FS_CATALOG . $imagePath;
        $webImageUrl       = HTTP_SERVER . DIR_WS_HTTP_CATALOG . $imagePath;
        if (file_exists($absoluteImagePath)) {
            return $webImageUrl;
        }

        $this->log('[validateImageUrl] image not found in: ' . $webImageUrl, ShopgateLogger::LOGTYPE_DEBUG);
        return '';
    }

    /**
     * read all category uids to an product from the database
     *
     * @param $productId
     *
     * @return array
     * @throws ShopgateLibraryException
     */
    public function getCategoryNumbers($productId)
    {
        $categoryIds = array();
        $sqlQuery    = "SELECT\n"
            . "\ttbl_ptc.categories_id "
            . "FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " tbl_ptc\n"
            . "LEFT JOIN " . TABLE_CATEGORIES . " AS tbl_c ON tbl_ptc.categories_id = tbl_c.categories_id\n"
            . "WHERE tbl_ptc.products_id = " . $productId
            . "\nAND tbl_c.categories_id != 0";
        $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);

        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error selecting category numbers.", true
            );
        } else {
            while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
                $categoryIds[] = $row["categories_id"];
            }
        }

        return $categoryIds;
    }

    /**
     * check if the stock for an product needs to be considered
     *
     * @param int $stockQty
     *
     * @return bool
     */
    public function getIsAvailable($stockQty, $productStatus)
    {
        return $productStatus && (STOCK_CHECK != 'true' || STOCK_ALLOW_CHECKOUT != 'false' || $stockQty > 0);
    }

    /**
     * reformat the available text to an product
     *
     * @param int    $productsQuantity
     * @param string $productsDateAvailable
     *
     * @return mixed|string
     */
    public function getAvailableText($productsQuantity, $productsDateAvailable, $productsStatus)
    {
        $availableText = ENTRY_AVAILABLE_TEXT_AVAILABLE;

        if (!$this->getIsAvailable($productsQuantity, $productsStatus)) {
            $availableText = ENTRY_AVAILABLE_TEXT_NOT_AVAILABLE;
        } else {
            if (STOCK_ALLOW_CHECKOUT == 'true' && $productsQuantity <= 0) {
                $availableText = ENTRY_AVAILABLE_TEXT_AVAILABLE_SHORTLY;
            } else {
                if (!empty($productsDateAvailable)) {
                    $availableOnTimestamp = strtotime(substr($productsDateAvailable, 0, 10) . ' 00:00:00');
                    if ($availableOnTimestamp - time() > 60 * 60 * 24) {
                        switch (strtolower($this->_langId)) {
                            case 'de':
                                $dateAvailableFormatted = date('d.m.Y', $availableOnTimestamp);
                                break;
                            case 'en':
                                $dateAvailableFormatted
                                    = ShopgateWrapper::date_long($productsDateAvailable);
                                break;
                            default:
                                $dateAvailableFormatted
                                    = ShopgateWrapper::date_short($productsDateAvailable);
                                break;
                        }
                        $availableText = str_replace(
                            '#DATE#', $dateAvailableFormatted, ENTRY_AVAILABLE_TEXT_AVAILABLE_ON_DATE
                        );
                    }
                }
            }
        }

        return $availableText;
    }

    /**
     * calculate an format an option price
     *
     * @param int   $prefixModificator
     * @param float $optionsValuesPrice
     * @param int   $taxRate
     *
     * @return mixed
     */
    public function getOptionsValuesPrice($prefixModificator, $optionsValuesPrice, $taxRate = 0)
    {
        return $this->formatPriceNumber(
            $prefixModificator * $optionsValuesPrice * $this->_exchangeRate * (($taxRate + 100) / 100)
        );
    }

    /**
     * format the price
     *
     * @param float  $price
     * @param int    $digits
     * @param string $decimalPoint
     * @param string $thousandPoints
     *
     * @return float|string
     */
    public function formatPriceNumber($price, $digits = 2, $decimalPoint = ".", $thousandPoints = "")
    {
        $price = round($price, $digits);

        return number_format($price, $digits, $decimalPoint, $thousandPoints);
    }

    /**
     * generate a query to read product data from the database
     *
     * @param int|null   $limit
     * @param int|null   $offset
     * @param array|null $uids
     *
     * @return string
     * @throws ShopgateLibraryException
     */
    public function buildItemQuery($limit = null, $offset = null, $uids = null)
    {
        // All sortings DESC, aka inverted because of our weird sorting in the XML.
        $order = 'ORDER BY tbl_d.products_name DESC';

        $swisscartSqlAddition = array('fields' => '', 'joins' => '', 'where' => '');
        if (!empty($this->_swisscartVersion)) {
            // Check how much image fields are possible
            $imagesColumns = array();
            $queryResult   = ShopgateWrapper::db_query_print_on_err(
                "SHOW FIELDS FROM " . TABLE_PRODUCTS
            );
            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error selecting product table fields.",
                    true
                );
            }
            while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
                $aKeys   = array_keys($row);
                $findPos = strpos($row[$aKeys[0]], 'products_image');
                if ($findPos !== false && $findPos == 0) {
                    // exclude 'products_image' column
                    if ($row[$aKeys[0]] != 'products_image') {
                        // string must end with a number after the text "products_image"
                        $partColumnName = substr($row[$aKeys[0]], strlen('products_image'));
                        if (!empty($partColumnName) && preg_match('/^[0-9]*$/', $partColumnName)) {
                            $imagesColumns[] = $row[$aKeys[0]];
                        }
                    }
                }
            }

            // add all possible image fields
            $swisscartSqlAddition['fields'] = '';
            if (!empty($imagesColumns)) {
                foreach ($imagesColumns as $columnName) {
                    $swisscartSqlAddition['fields'] .= "tbl_p.$columnName, ";
                }
                $this->_swisscartImageCount = count($imagesColumns) + 1;
            } else {
                $this->_swisscartImageCount = 1;
            }
            $swisscartSqlAddition['fields'] .= "tbl_f.status AS feature_status,";

            $swisscartSqlAddition['joins'] = "LEFT JOIN " . TABLE_FEATURED
                . " tbl_f ON (tbl_f.products_id = tbl_p.products_id AND (tbl_f.expires_date > now()"
                . " OR tbl_f.expires_date = '0000-00-00 00:00:00' "
                . " OR tbl_f.expires_date IS NULL) AND tbl_f.status = 1) ";

            $order = 'ORDER BY ' . $this->getProductsSortOrderFields();
        }

        $sqlQuery
            // Surrounding the "real" query with a select that counts the results and all fields from the real query's
            // result gives an absolute order index of all products found.
            // Starting value for @sort_order is set at the bottom of this query.
            = "SELECT @sort_order := @sort_order + 1 AS sort_order, products_result.* FROM (" .

            // Now the "real" query:
            "SELECT " . "tbl_p.products_id, " . "tbl_p.products_quantity, "
            . "tbl_p.products_model, " . "tbl_p.products_image, "
            . "{$swisscartSqlAddition['fields']} " . "tbl_p.products_price, "
            . "UNIX_TIMESTAMP(tbl_p.products_date_added) as products_date_added, "
            . "UNIX_TIMESTAMP(tbl_p.products_last_modified) as products_last_modified, "
            . "tbl_p.products_date_available, " . "tbl_p.products_weight, "
            . "tbl_p.products_status, " . "tbl_p.products_tax_class_id, "
            . "tbl_p.manufacturers_id, " . "tbl_p.products_ordered, "
            . "tbl_tc.tax_class_id, " . "tbl_tc.tax_class_title, "
            . "tbl_d.language_id, " . "tbl_d.products_name, "
            . "tbl_d.products_description, " . "tbl_d.products_url, "
            . "tbl_d.products_viewed, " . "tbl_m.manufacturers_name, "
            . "tbl_tr.tax_rate, "
            . "tbl_s.specials_new_products_price "
            . "FROM " . TABLE_PRODUCTS . " as tbl_p " . "LEFT JOIN "
            . TABLE_PRODUCTS_DESCRIPTION
            . " tbl_d ON tbl_d.products_id = tbl_p.products_id " . "LEFT JOIN "
            . TABLE_MANUFACTURERS
            . " tbl_m ON tbl_m.manufacturers_id = tbl_p.manufacturers_id "
            . "LEFT JOIN " . TABLE_TAX_RATES
            . " tbl_tr ON tbl_tr.tax_class_id = tbl_p.products_tax_class_id "
            . ((!empty($this->_taxZone)) ? "AND tbl_tr.tax_zone_id = $this->_taxZone " : "")
            . "LEFT JOIN " . TABLE_TAX_CLASS
            . " tbl_tc ON tbl_tc.tax_class_id = tbl_p.products_tax_class_id "
            . "LEFT JOIN " . TABLE_SPECIALS . " tbl_s ON ("
            . "tbl_s.products_id = tbl_p.products_id " . "AND tbl_s.status = 1 "
            . "AND (tbl_s.expires_date > now() "
            . "OR tbl_s.expires_date = '0000-00-00 00:00:00' "
            . "OR tbl_s.expires_date IS NULL)) "
            . "{$swisscartSqlAddition['joins']} "
            . "WHERE tbl_d.language_id = $this->_langId "
            . "AND tbl_p.products_status = 1 "
            . "{$swisscartSqlAddition['where']} "
            . (!empty($uids) ? "AND tbl_p.products_id in (" . implode(',', $uids) . ") " : "")
            . "GROUP BY tbl_p.products_id "
            . "{$order} "
            . ((is_numeric($limit) && is_numeric($offset)) ? "LIMIT " . $offset . "," . $limit : "")

            // Give the real query the name "products_result", as it's referenced to above.
            . ") AS products_result, "

            // Set the initial @sort_order value.
            . "(SELECT @sort_order := " . (int)$offset . ") AS tmp;";

        $this->log('get items query: ' . $sqlQuery, ShopgateLogger::LOGTYPE_DEBUG);

        return $sqlQuery;
    }

    /**
     * cleans the products name:
     *  - remove html entities
     *  - remove html tags
     *
     * @param string $productName
     *
     * @return string
     */
    public function buildName($productName)
    {
        return strip_tags(html_entity_decode($productName));
    }

    /**
     * converts the "last modified" date in into the syntax year-month-day
     *
     * @param $lastModified
     *
     * @return string
     */
    public function buildLastUpdate($lastModified)
    {
        return $lastModified ? strftime("%Y-%m-%d", $lastModified) : '';
    }

    /**
     * converts the tax rate to int
     *
     * @param float $taxRate
     *
     * @return float
     */
    public function buildTaxRate($taxRate)
    {
        return intval($taxRate * 100) / 100;
    }

    /**
     * removes all carriage returns (\r) and line feeds (\n)
     *
     * @param string $description
     *
     * @return string
     */
    public function buildDescription($description)
    {
        return str_replace("\r", "", $description);
    }

    /**
     * generate a direct link to the product
     *
     * @param int $productId
     *
     * @return string
     */
    public function buildDeeplink($productId)
    {
        return tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $productId);
    }

    /**
     * check if oversold (ignore stock) is allowed
     *
     * @return int
     */
    public function buildUseStock()
    {
        // STOCK_ALLOW_CHECKOUT might be a string containing "false", so we have to check for the type first
        if (!is_bool(STOCK_ALLOW_CHECKOUT) && !is_int(STOCK_ALLOW_CHECKOUT)) {
            $stockAllowCheckout = STOCK_ALLOW_CHECKOUT != 'false';
        } else {
            $stockAllowCheckout = STOCK_ALLOW_CHECKOUT == true;
        }

        // STOCK_CHECK might be a string containing "false", so we have to check for the type first
        if (!is_bool(STOCK_CHECK) && !is_int(STOCK_CHECK)) {
            $stockCheck = STOCK_CHECK != 'false';
        } else {
            $stockCheck = STOCK_CHECK == true;
        }

        return !$stockAllowCheckout && $stockCheck;
    }

    /**
     * read images to an product from the database by a products uid
     *
     * @param $itemRow
     *
     * @return array
     * @throws ShopgateLibraryException
     */
    public function getProductImages($itemRow)
    {
        $images = array();
        if (defined('PROJECT_VERSION') && strpos(PROJECT_VERSION, '2.3') !== false && empty($this->swisscartVersion)) {
            $sqlQuery    = "SELECT tbl_i.image FROM " . TABLE_PRODUCTS_IMAGES . " AS tbl_i "
                . "WHERE tbl_i.products_id = " . $itemRow["products_id"]
                . " ORDER BY tbl_i.sort_order ASC";
            $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);
            if (!$queryResult) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error selecting additional product images.",
                    true
                );
            } else {
                while ($row = ShopgateWrapper::db_fetch_array($queryResult)) {
                    $tmpString = $this->validateImageUrl($row["image"]);
                    if (!empty($tmpString)) {
                        $images[] = $tmpString;
                    }
                }
            }
        }

        if (empty($images)) {
            $tmpString = $this->validateImageUrl($itemRow["products_image"]);
            if (!empty($tmpString)) {
                $images[] = $tmpString;
            }
        }

        if (!empty($this->_swisscartVersion)) {
            for ($i = 2; $i <= ($this->_swisscartImageCount); $i++) {
                $tmpString = $this->validateImageUrl($itemRow["products_image$i"]);
                if (!empty($tmpString)) {
                    $images[] = $tmpString;
                }
            }
        }

        return $images;
    }

    /**
     * generate tax classes for export
     *
     * @param $productRow
     *
     * @return string
     */
    public function buildTaxClass($productRow)
    {
        return (isset($productRow['tax_class_id']))
            ? $productRow['tax_class_id']
            : null;
    }

    /**
     * @return string
     */
    private function getProductsSortOrderFields()
    {
        // All sortings DESC, aka inverted because of our weird sorting in the XML.
        // The order
        return (defined('PRODUCT_LIST_SORT_ORDER') && PRODUCT_LIST_SORT_ORDER > 0)
            ? 'tbl_p.products_sort_order DESC, tbl_d.products_name DESC'
            : 'tbl_d.products_name DESC';
    }
}
