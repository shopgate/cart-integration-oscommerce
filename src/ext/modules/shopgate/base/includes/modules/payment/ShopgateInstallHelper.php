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
class ShopgateInstallHelper
{
    /**
     * salt to create hash. This hash identifies the shop
     */
    const SHOPGATE_SALT = "shopgate_oscommerce";

    /**
     * defines the shop system (predefined by sg)
     */
    const SHOPGATE_SHOP_TYPE = 95;

    /**
     * url to the sg api controller. calling the action log_api (live)
     */
    const SHOPGATE_REQUEST_URL = 'https://api.shopgate.com/log';

    /**
     * database configuration key
     */
    const SHOPGATE_DATABASE_CONFIG_KEY = "MODULE_PAYMENT_SHOPGATE_IDENT";

    /**
     * file where the ident hash will be stored
     */
    const SHOPGATE_HASH_FILE = "/sg_identity.php";

    /**
     * default currency configuration key
     */
    const SHOPGATE_DEFAULT_CURRENCY_KEY = "DEFAULT_CURRENCY";

    /**
     * default email configuration key
     */
    const SHOPGATE_DEFAULT_EMAIL_KEY = "STORE_OWNER_EMAIL_ADDRESS";

    /**
     * default contact name configuration key
     */
    const SHOPGATE_DEFAULT_CONTACT_NAME_KEY = "STORE_OWNER";

    /**
     * default store name configuration key
     */
    const SHOPGATE_DEFAULT_STORE_NAME_KEY = "STORE_NAME";

    /**
     * default store address configuration key
     */
    const SHOPGATE_DEFAULT_STORE_NAME_ADDRESS_KEY = "STORE_NAME_ADDRESS";

    /**
     * send information about the store to sg
     */
    public function sendData()
    {
        $shopHolderInformation = $this->getStoreHolderInformation();
        $postData              =
            array(
                'action'              => 'interface_install',
                'uid'                 => $this->getUid(),
                'url'                 => $this->getUrl(),
                'name'                => $shopHolderInformation['store_name'],
                'plugin_version'      => $this->getPluginVersion(),
                'shopping_system_id'  => $this->getShopSystemId(),
                'contact_name'        => $shopHolderInformation['contact_name'],
                'contact_phone'       => $shopHolderInformation['store_phone'],
                'contact_email'       => $shopHolderInformation['contact_email'],
                'stats_items'         => $this->getProductCount(true),
                'stats_categories'    => $this->getCategoryCount(),
                'stats_orders'        => $this->getOrderAmount($this->getDate()),
                'stats_acs'           => $this->getAcs(),
                'stats_currency'      => $this->getDefaultCurrency(),
                'stats_unique_visits' => '',
                'stats_mobile_visits' => '',
            );
        $this->sendPostRequest($postData);
    }

    /**
     * return an array with store holder information
     *
     * @return array
     */
    private function getStoreHolderInformation()
    {
        $keyQuery = 'SELECT configuration_key,configuration_value FROM '
            . TABLE_CONFIGURATION . ' as c
						 WHERE c.configuration_key = "'
            . self::SHOPGATE_DEFAULT_EMAIL_KEY . '"
						 OR c.configuration_key    = "'
            . self::SHOPGATE_DEFAULT_CONTACT_NAME_KEY . '" 
						 OR c.configuration_key    = "'
            . self::SHOPGATE_DEFAULT_STORE_NAME_KEY . '"
						 OR c.configuration_key    = "'
            . self::SHOPGATE_DEFAULT_STORE_NAME_ADDRESS_KEY . '";';

        $result                 = tep_db_query($keyQuery);
        $storeHolderInformation = array();

        while ($row = tep_db_fetch_array($result)) {
            if (array_key_exists('configuration_value', $row)) {
                if ($row['configuration_key'] == "STORE_OWNER_EMAIL_ADDRESS") {
                    $storeHolderInformation['contact_email'] =
                        $row['configuration_value'];
                }
                if ($row['configuration_key'] == "STORE_OWNER") {
                    $storeHolderInformation['contact_name'] =
                        $row['configuration_value'];
                }
                if ($row['configuration_key'] == "STORE_NAME") {
                    $storeHolderInformation['store_name'] =
                        $row['configuration_value'];
                }
                if ($row['configuration_key'] == "STORE_NAME_ADDRESS") {
                    $storeHolderInformation['store_phone'] =
                        $row['configuration_value'];
                }
            }
        }

        return $storeHolderInformation;
    }

    /**
     * get an unique hash to identify the shop
     *
     * @return string
     */
    private function getUid()
    {
        $hashFile = realpath(dirname(__FILE__) . "/../../../../../../../")
            . self::SHOPGATE_HASH_FILE;

        if (file_exists($hashFile)) {
            $content = file_get_contents($hashFile);
            preg_match("/([a-z0-9]{32})/", $content, $result);
            if (is_array($result)) {
                return (count($result) > 1) ? $result[1] : $result[0];
            }
        }

        $keyQuery =
            'SELECT c.configuration_value AS val FROM ' . TABLE_CONFIGURATION
            . ' AS c WHERE c.configuration_key = "'
            . self::SHOPGATE_DATABASE_CONFIG_KEY . '" LIMIT 1;';
        $result   = tep_db_query($keyQuery);
        $row      = tep_db_fetch_array($result);

        if (!empty($row) && $row['val'] && $row['val'] != 0) {
            return $row['val'];
        }

        $httpServer = null;

        if (defined('HTTP_SERVER')) {
            $httpServer = HTTP_SERVER;
        } elseif (isset($_SERVER) && !empty($_SERVER['SERVER_NAME'])) {
            $httpServer = $_SERVER['SERVER_NAME'];
        }

        if (!empty($httpServer) && defined('DIR_WS_CATALOG')) {
            $url        = preg_replace(
                '/^www\./', '',
                preg_replace('#^https?://#', '', trim($httpServer, '/'))
            );
            $uri        = trim(DIR_WS_CATALOG, '/');
            $hashString = $url . '/' . $uri;
        } else {
            $storeHolderInfo = $this->getStoreHolderInformation();
            $hashString      = $storeHolderInfo['contact_email'];
        }

        $saltedHash = md5($hashString . self::SHOPGATE_SALT);
        $content    = "<?php //" . $saltedHash;

        if (file_put_contents($hashFile, $content) === false) {
            // error
        }

        $updateKeyQuery = 'UPDATE ' . TABLE_CONFIGURATION
            . ' AS c  SET c.configuration_value ="' . $saltedHash
            . '" WHERE c.configuration_key = "'
            . self::SHOPGATE_DATABASE_CONFIG_KEY . '";';
        tep_db_query($updateKeyQuery);

        return $saltedHash;
    }

    /**
     * return the complete url to the current shop
     *
     * @return string
     */
    private function getUrl()
    {
        if (function_exists('apache_request_headers')) {
            $header = apache_request_headers();
            $host   = ((!empty($header['Referer'])) ? $header['Referer']
                : $header['Host']);

            return $host;
        } else {
            if (isset($_SERVER)) {
                $protocol =
                    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
                        ? "https://" : "http://";
                $host     =
                    (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST']
                        : $_SERVER['HTTP_NAME'];
                $uri      =
                    (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI']
                        : '';

                return ($protocol . $host . $uri);
            }
        }

        return '';
    }

    /**
     * returns the plugin version
     *
     * @return int
     */
    private function getPluginVersion()
    {
        return SHOPGATE_PLUGIN_VERSION;
    }

    /**
     * returns the shop system code defined by shopgate
     *
     * @return mixed
     */
    private function getShopsystemId()
    {
        return self::SHOPGATE_SHOP_TYPE;
    }

    /**
     * return the product count
     *
     * @param bool $ignoreDeactivated if true ignores the inactive products
     *
     * @return int
     */
    private function getProductCount($ignoreDeactivated = false)
    {
        $query = "SELECT count(*) as cnt FROM " . TABLE_PRODUCTS . " AS p ";

        if ($ignoreDeactivated) {
            $query .= "WHERE p.products_status != 0";
        }

        $result = tep_db_query($query);
        $row    = tep_db_fetch_array($result);

        return $row['cnt'];
    }

    /**
     * return the amount of all categories in the shop system
     *
     * @param bool $ignoreDeactivated if true the deactivated categories will be ignored
     *
     * @return int
     */
    private function getCategoryCount($ignoreDeactivated = false)
    {
        $query = "SELECT count(*) AS cnt FROM " . TABLE_CATEGORIES . " AS c ";

        if ($ignoreDeactivated) {
            $query .= "WHERE c.categories_status != 0";
        }

        $result = tep_db_query($query);
        $row    = tep_db_fetch_array($result);

        return $row['cnt'];
    }

    /**
     * return the amount of all orders in the shop system
     *
     * @param string $beginDate in format Y-m-d H:i:s
     *
     * @return int
     */
    private function getOrderAmount($beginDate = null)
    {
        if (is_null($beginDate)) {
            $beginDate = 'now()';
        }

        $query  = "SELECT count(*) as cnt FROM " . TABLE_ORDERS
            . " WHERE date_purchased BETWEEN '{$beginDate}' AND now()";
        $result = tep_db_query($query);
        $row    = tep_db_fetch_array($result);

        return $row['cnt'];
    }

    /**
     * returns the date minus the committed period
     *
     * @param string $interval
     *
     * @return bool|string
     */
    private function getDate($interval = "-1 months")
    {
        return date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . $interval));
    }

    /**
     * return the get Average cart score (acs)
     *
     * @return double
     */
    public function getAcs()
    {
        $query  =
            "SELECT (SUM(op.products_price) DIV (SELECT COUNT(*) FROM " . TABLE_ORDERS_PRODUCTS . ")) as acs FROM "
            . TABLE_ORDERS_PRODUCTS . " as op";
        $result = tep_db_query($query);
        $row    = tep_db_fetch_array($result);

        if (!empty($row)) {
            return (array_key_exists('acs', $row)) ? $row['acs'] : '';
        }

        return '';
    }

    /**
     * returns the default currency of the shop
     *
     * @return string
     */
    private function getDefaultCurrency()
    {
        $query  =
            'SELECT configuration_value AS currency FROM ' . TABLE_CONFIGURATION
            . ' AS c
				   where c.configuration_key = "'
            . self::SHOPGATE_DEFAULT_CURRENCY_KEY . '"';
        $result = tep_db_query($query);
        $row    = tep_db_fetch_array($result);

        return $row['currency'];
    }

    /**
     * send an curl Post request to shopgate
     *
     * @param $data array with post data
     *
     * @return bool return true if post was successful false if not
     */
    private function sendPostRequest($data)
    {
        $query = http_build_query($data);
        $curl  = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::SHOPGATE_REQUEST_URL);
        curl_setopt($curl, CURLOPT_POST, count($data));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (!($result = curl_exec($curl))) {
            return false;
        }

        curl_close($curl);

        return true;
    }

    ############################
    ##### HELPER FUNCTIONS #####
    ############################

    /**
     * @return null
     */
    public function getUniqueVisits()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getMobileVisits()
    {
        return null;
    }
}
