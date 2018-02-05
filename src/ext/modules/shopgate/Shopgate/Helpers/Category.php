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
class Shopgate_Helpers_Category extends Shopgate_Model_Catalog_Category
{
    /**
     * @var int
     */
    protected $_maxCategorySortOrder;
    /**
     * @var int
     */
    protected $_minCategorySortOrder;

    /**
     * generate the query to read the whole category data from the database
     *
     * @param int        $languageId
     * @param int|null   $limit
     * @param int|null   $offset
     * @param array|null $uids
     *
     * @return string
     */
    public function buildCategoryQuery($languageId, $limit = null, $offset = null, $uids = null)
    {
        $sqlQuery = "SELECT\n" . "\ttbl_c.categories_id,\n" . "\ttbl_c.parent_id,\n" . "\ttbl_c.sort_order,\n"
            . "\ttbl_c.categories_image,\n" . "\ttbl_cd.categories_name\n" . "FROM " . TABLE_CATEGORIES
            . " AS tbl_c\n" . "\tLEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION
            . " AS tbl_cd ON (tbl_c.categories_id=tbl_cd.categories_id)\n" . "WHERE\n"
            . "\ttbl_cd.language_id = $languageId\n"
            . (!empty($uids) ? "AND\n\ttbl_c.categories_id in (" . implode(',', $uids) . ")" . "\n" : "")
            . ((is_numeric($limit) && is_numeric($offset)) ? "LIMIT " . $offset . "," . $limit : "") . ";";

        $this->log('get categories query: ' . $sqlQuery, ShopgateLogger::LOGTYPE_DEBUG);

        return $sqlQuery;
    }

    /**
     * generate the maximum order value for category export
     *
     * @return int
     * @throws ShopgateLibraryException
     */
    public function getCategoryMaxOrder()
    {
        if (empty($this->_maxCategorySortOrder)) {
            $sqlQuery = "SELECT MAX(sort_order) sort_order FROM " . TABLE_CATEGORIES;
            $result   = ShopgateWrapper::db_query_print_on_err($sqlQuery);

            if (!$result) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error maximum sort order for categories.", true
                );
            }

            $data                        = ShopgateWrapper::db_fetch_array($result);
            $this->_maxCategorySortOrder = empty($data) ? 0 : $data["sort_order"];
        }

        return $this->_maxCategorySortOrder;
    }

    /**
     * generate the minimum oder value for category export
     *
     * @return int
     * @throws ShopgateLibraryException
     */
    public function getCategoryMinOrder()
    {
        if (empty($this->_minCategorySortOrder)) {
            $sqlQuery = "SELECT MIN(sort_order) sort_order FROM " . TABLE_CATEGORIES;
            $result   = ShopgateWrapper::db_query_print_on_err($sqlQuery);

            if (!$result) {
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                    "Shopgate Plugin - Error minimum sort order for categories.", true
                );
            }

            $data                        = ShopgateWrapper::db_fetch_array($result);
            $this->_minCategorySortOrder = empty($data) ? 0 : $data["sort_order"];
        }

        return $this->_minCategorySortOrder;
    }

    /**
     * calculates Shopgate sort order index
     *
     * @param int $shopSort
     *
     * @return int
     */
    public function buildSortOrder($shopSort)
    {
        $maxSortOrderRange = $this->getCategoryMaxOrder() - $this->getCategoryMinOrder();
        $orderIndex        = $maxSortOrderRange - $shopSort - $this->getCategoryMinOrder() + 1;

        return $orderIndex < 0 ? 0 : $orderIndex;
    }

    /**
     * returns parent category id
     *
     * @param int $parentCategoryId
     * @param int $currentCategoryId
     *
     * @return int
     */
    public function buildParentUid($parentCategoryId, $currentCategoryId)
    {
        return empty($parentCategoryId) || ($parentCategoryId == $currentCategoryId) ? null : $parentCategoryId;
    }

    /**
     * build image url from file name
     *
     * @param string $imageFile
     *
     * @return string
     */
    public function buildCategoryImageUrl($imageFile)
    {
        return empty($imageFile) ? '' : (HTTP_SERVER . DIR_WS_HTTP_CATALOG . DIR_WS_IMAGES . $imageFile);
    }

    /**
     * returns category deep link from category id
     *
     * @param int $categoryId
     *
     * @return string
     */
    public function buildDeeplink($categoryId)
    {
        return tep_href_link(FILENAME_DEFAULT, 'cPath=' . $categoryId);
    }
}
