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
class Shopgate_Helpers_Reviews extends Shopgate_Model_Review
{
    /**
     * returns a Shopgate review title from review text
     *
     * @param string $text
     *
     * @return string
     */
    public function buildTitle($text)
    {
        return substr($text, 0, 20) . "...";
    }

    /**
     * returns a Shopgate time string
     *
     * @param string $date
     *
     * @return string
     */
    public function buildDate($date)
    {
        return empty($date) ? "" : strftime("%Y-%m-%d", strtotime($date));
    }

    /**
     * calculates shopgate score from shop score
     *
     * @param int $shopScore
     *
     * @return int
     */
    public function buildScore($shopScore)
    {
        return intval($shopScore * 2);
    }

    /**
     * generate a query to read the review data from the database
     *
     * @param int|null   $limit
     * @param int|null   $offset
     * @param array|null $uids
     *
     * @return string
     */
    public function buildReviewQuery($limit = null, $offset = null, $uids = null)
    {
        $sqlQuery = "SELECT\n" . "\ttbl_r.reviews_id,\n" . "\ttbl_r.products_id,\n"
            . "\ttbl_r.customers_name,\n" . "\ttbl_r.reviews_rating,\n"
            . "\ttbl_r.date_added,\n" . "\ttbl_rd.reviews_text\n" . "FROM "
            . TABLE_REVIEWS . " AS tbl_r\n" . "\tLEFT JOIN "
            . TABLE_REVIEWS_DESCRIPTION
            . " AS tbl_rd ON (tbl_r.reviews_id=tbl_rd.reviews_id)\n" . "WHERE\n"
            . // OsCommerce-version dependent! (field "reviews_status" only available in osCommerce 2.3+)
            "\t" . ((defined('PROJECT_VERSION') && strpos(PROJECT_VERSION, '2.3') !== false)
                ? "tbl_r.reviews_status=1" : "1=1")
            . "\n" . (!empty($uids) ? "AND\n\ttbl_r.reviews_id in (" . implode(',', $uids) . ")"
                . "\n" : "") . "ORDER BY\n" . "\ttbl_r.products_id\n"
            . ((is_numeric($limit) && is_numeric($offset)) ? "LIMIT " . $offset . "," . $limit : "") . ";";

        return $sqlQuery;
    }
}
