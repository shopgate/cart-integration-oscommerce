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
require_once(dirname(__FILE__) . '/../vendor/autoload.php');

/**
 * Class loaded outside of auto-loading some times
 */
class ShopgateConfigOsCommerce extends ShopgateConfig
{
    protected $is_usa_plugin;
    protected $redirect_languages;
    protected $tax_zone_id;
    protected $customers_status_id;
    protected $customer_price_group;
    protected $order_status_open;
    protected $order_status_shipped;
    protected $order_status_shipping_blocked;
    protected $order_status_canceled;
    protected $always_cancel_shipping;
    protected $send_order_confirmation_mail;
    protected $shopgate_table_version;

    /** @var string */
    protected $payment_name_mapping;

    /**
     * @inheritdoc
     */
    public function startup()
    {
        // overwrite library defaults
        $this->plugin_name                    = 'osCommerce';
        $this->enable_redirect_keyword_update = 24;
        $this->enable_ping                    = 1;
        $this->enable_add_order               = 1;
        $this->enable_update_order            = 1;
        $this->enable_get_orders              = 1;
        $this->enable_get_customer            = 1;
        $this->enable_get_items_csv           = 1;
        $this->enable_get_categories_csv      = 1;
        $this->enable_get_reviews_csv         = 1;
        $this->enable_get_log_file            = 1;
        $this->enable_mobile_website          = 1;
        $this->enable_cron                    = 1;
        $this->enable_clear_log_file          = 1;
        $this->enable_clear_cache             = 1;
        $this->enable_get_settings            = 1;
        $this->enable_check_cart              = 1;
        $this->enable_check_stock             = 1;
        $this->enable_redeem_coupons          = 1;
        $this->enable_register_customer       = 1;
        $this->enable_get_reviews             = 1;
        $this->enable_get_categories          = 1;
        $this->enable_get_items               = 1;

        // default depends on PROJECT_VERSION constant of OsCommerce
        if (defined('PROJECT_VERSION')
            && strpos(PROJECT_VERSION, '2.3') !== false
        ) {
            $this->encoding = 'UTF-8';
        } else {
            $this->encoding = 'ISO-8859-15';
        }

        // the path to cache, logs and temporary export files changed with plugin version 2.9.33
        $this->export_folder_path = $this->getDataFolderPath() . '/temp';
        $this->log_folder_path    = $this->getDataFolderPath() . '/log';
        $this->cache_folder_path  = $this->getDataFolderPath() . '/cache';

        // set path to configuration to the new path (starting with plugin version 2.9.33) just to be safe
        /** @noinspection PhpDeprecationInspection */
        $this->config_folder_path = $this->getDataFolderPath() . '/configuration';

        // default file names if no language was selected
        $this->items_csv_filename      = 'items-undefined.csv';
        $this->categories_csv_filename = 'categories-undefined.csv';
        $this->reviews_csv_filename    = 'reviews-undefined.csv';

        $this->access_log_filename  = 'access-undefined.log';
        $this->request_log_filename = 'request-undefined.log';
        $this->error_log_filename   = 'error-undefined.log';
        $this->debug_log_filename   = 'debug-undefined.log';

        $this->redirect_keyword_cache_filename      = 'redirect_keywords-undefined.txt';
        $this->redirect_skip_keyword_cache_filename = 'skip_redirect_keywords-undefined.txt';

        // initialize plugin specific stuff
        $this->is_usa_plugin                 = null;
        $this->redirect_languages            = array();
        $this->tax_zone_id                   = defined('STORE_ZONE') ? STORE_ZONE : 'STORE_ZONE';
        $this->customers_status_id           = 1;
        $this->customer_price_group          = 0;
        $this->order_status_open             = 1;
        $this->order_status_shipped          = 3;
        $this->order_status_shipping_blocked = 1;
        $this->order_status_canceled         = 0;
        $this->always_cancel_shipping        = 0;
        $this->send_order_confirmation_mail  = 0;
        $this->shopgate_table_version        = '';
        $this->supported_fields_check_cart   = array("items", "external_coupons", "shipping_methods", "currency");
        $this->payment_name_mapping          = "";
    }

    /**
     * @param array $fieldList
     *
     * @return array|string[]
     */
    protected function validateCustom(array $fieldList = array())
    {
        $failedFields = array();

        foreach ($fieldList as $field) {
            switch ($field) {
                case 'redirect_languages':
                    // at least one redirect language must be selected
                    if (empty($this->redirect_languages)) {
                        $failedFields[] = $field;
                    }
                    break;
            }
        }

        return $failedFields;
    }

    /**
     * @return boolean
     */
    public function getIsUsaPlugin()
    {
        return $this->is_usa_plugin;
    }

    /**
     * @param $value
     */
    public function setIsUsaPlugin($value)
    {
        $this->is_usa_plugin = $value;
    }

    /**
     * @return mixed
     */
    public function getRedirectLanguages()
    {
        return $this->redirect_languages;
    }

    /**
     * @param $value
     */
    public function setRedirectLanguages($value)
    {
        $this->redirect_languages = $value;
    }

    /**
     * @return mixed
     */
    public function getTaxZoneId()
    {
        return $this->tax_zone_id;
    }

    /**
     * @param $value
     */
    public function setTaxZoneId($value)
    {
        $this->tax_zone_id = $value;
    }

    /**
     * @return mixed
     */
    public function getCustomersStatusId()
    {
        return $this->customers_status_id;
    }

    /**
     * @param $value
     */
    public function setCustomersStatusId($value)
    {
        $this->customers_status_id = $value;
    }

    /**
     * @return mixed
     */
    public function getCustomerPriceGroup()
    {
        return $this->customer_price_group;
    }

    /**
     * @param $value
     */
    public function setCustomerPriceGroup($value)
    {
        $this->customer_price_group = $value;
    }

    /**
     * @return mixed
     */
    public function getOrderStatusOpen()
    {
        return $this->order_status_open;
    }

    /**
     * @param $value
     */
    public function setOrderStatusOpen($value)
    {
        $this->order_status_open = $value;
    }

    /**
     * @return mixed
     */
    public function getOrderStatusShipped()
    {
        return $this->order_status_shipped;
    }

    /**
     * @param $value
     */
    public function setOrderStatusShipped($value)
    {
        $this->order_status_shipped = $value;
    }

    /**
     * @return mixed
     */
    public function getOrderStatusShippingBlocked()
    {
        return $this->order_status_shipping_blocked;
    }

    /**
     * @param $value
     */
    public function setOrderStatusShippingBlocked($value)
    {
        $this->order_status_shipping_blocked = $value;
    }

    /**
     * @return mixed
     */
    public function getOrderStatusCanceled()
    {
        return $this->order_status_canceled;
    }

    /**
     * @param $value
     */
    public function setOrderStatusCanceled($value)
    {
        $this->order_status_canceled = $value;
    }

    /**
     * @return mixed
     */
    public function getAlwaysCancelShipping()
    {
        return $this->always_cancel_shipping;
    }

    /**
     * @param $value
     */
    public function setAlwaysCancelShipping($value)
    {
        $this->always_cancel_shipping = $value;
    }

    /**
     * @return mixed
     */
    public function getSendOrderConfirmationMail()
    {
        return $this->send_order_confirmation_mail;
    }

    /**
     * @param $value
     */
    public function setSendOrderConfirmationMail($value)
    {
        $this->send_order_confirmation_mail = $value;
    }

    /**
     * @return mixed
     */
    public function getShopgateTableVersion()
    {
        return $this->shopgate_table_version;
    }

    /**
     * @param $value
     */
    public function setShopgateTableVersion($value)
    {
        $this->shopgate_table_version = $value;
    }

    /**
     * @return string
     */
    public function getPaymentNameMapping()
    {
        return $this->payment_name_mapping;
    }

    /**
     * @param string $value
     */
    public function setPaymentNameMapping($value)
    {
        $this->payment_name_mapping = $value;
    }

    public function buildConfigFilePath($fileName = self::DEFAULT_CONFIGURATION_FILE_NAME)
    {
        // if a configuration file under the default path does not exist
        // assume it in the former (pre-composer) default path
        return file_exists($this->getDefaultConfigFilePath($fileName))
            ? $this->getDefaultConfigFilePath($fileName)
            : $this->getLegacyConfigFilePath($fileName);
    }

    public function saveFile(array $fieldList, $path = null, $validate = true)
    {
        $fileName = null === $path
            ? self::DEFAULT_CONFIGURATION_FILE_NAME
            : basename($path);

        // always try writing to the default path
        $path = $this->getDefaultConfigFilePath($fileName);

        parent::saveFile($fieldList, $path, $validate);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getDefaultConfigFilePath($fileName = self::DEFAULT_CONFIGURATION_FILE_NAME)
    {
        return "{$this->getDataFolderPath()}/configuration/{$fileName}";
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getLegacyConfigFilePath($fileName = self::DEFAULT_CONFIGURATION_FILE_NAME)
    {
        return __DIR__ . '/../shopgate_library/config/' . $fileName;
    }

    /**
     * @return string
     */
    private function getDataFolderPath()
    {
        return __DIR__ . '/../data';
    }

}

/**
 * Type of plugin based on region
 */
class ShopgatePluginType
{
    /**
     * Return a map of types based
     * on country 2-letter abbreviation
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            // US Plugin-Type
            'US' => 1,
            // Non-US Plugin-Type
            'DE' => 0,
            'AT' => 0,
            'CH' => 0
        );
    }
}

/**
 * Database wrapper, use Collections instead
 */
class ShopgateWrapper
{
    /**
     * @param string $date
     *
     * @return string
     */
    public static function date_long($date)
    {
        return tep_date_long($date);
    }

    /**
     *
     * @param string $date
     *
     * @return string
     */
    public static function date_short($date)
    {
        return tep_date_long($date);
    }

    /**
     * Wraps for example: tep_db_query (osCommerce)
     *
     * @param string $query
     *
     * @return resource
     */
    public static function db_query($query)
    {
        return tep_db_query($query);
    }

    /**
     * does some additional error output
     *
     * @param string $query
     *
     * @return bool|mysqli_result|resource
     */
    public static function db_query_print_on_err($query)
    {
        global $db_link;

        $backtraceData = debug_backtrace();
        if (isset($backtraceData['file'])) {
            $backtraceData =
                array($backtraceData, array('file', 'line', 'function'),);
        }
        $backtraceString =
            "{$backtraceData[0]['function']} called by [function: {$backtraceData[1]['function']}] ["
            . $backtraceData[0]['file'] . " in Line: "
            . $backtraceData[0]['line'] . "]";

        if ($db_link instanceof mysqli) {
            $result = mysqli_query($db_link, $query);
        } else {
            $result = mysql_query($query, $db_link);
        }

        if (!$result) {
            if ($db_link instanceof mysqli) {
                echo(mysqli_errno($db_link) . ' :: ' . mysqli_error($db_link)
                    . "\nBacktrace: $backtraceString\n\nQuery:\n$query\n");
            } else {
                echo(mysql_errno() . ' :: ' . mysql_error() . "\nBacktrace: $backtraceString\n\nQuery:\n$query\n");
            }
        }

        return $result;
    }

    /**
     * Wraps for example: tep_db_fetch_array (osCommerce)
     *
     * @param resource $result
     *
     * @return mixed[]
     */
    public static function db_fetch_array($result)
    {
        return tep_db_fetch_array($result);
    }

    /**
     * wraps for example: tep_db_num_rows (osCommerce)
     *
     * @param mixed $result
     *
     * @return int
     */
    public static function db_num_rows($result)
    {
        return tep_db_num_rows($result);
    }

    /**
     * Wraps for example: tep_db_input (osCommerce)
     *
     * @param string $input
     *
     * @return string
     */
    public static function db_input($input)
    {
        return tep_db_input($input);
    }

    /**
     * Wraps for example: tep_db_prepare_input (osCommerce)
     *
     * @param string $input
     *
     * @return string
     */
    public static function db_prepare_input($input)
    {
        return tep_db_prepare_input($input);
    }

    /**
     * Wraps for example: tep_db_insert_id (osCommerce)
     *
     * @return int
     */
    public static function db_insert_id()
    {
        return tep_db_insert_id();
    }

    /**
     * Wraps for example: tep_db_perform (osCommerce)
     *
     * @param string $table
     * @param array  $data
     * @param string $action
     * @param string $parameters
     * @param mixed  $link
     *
     * @return bool|mysqli_result|resource
     */
    public static function db_execute_query($table, $data, $action = 'insert', $parameters = '', $link = 'db_link')
    {
        return tep_db_perform($table, $data, $action, $parameters, $link);
    }

    /**
     * Simple selector query
     *
     * @param                               $table  - which table to select from
     * @param                               $where  => array('column_name' => 'value')
     * @param string|array('col','col',...) $select - what to select
     *
     * @return mixed[]
     * @throws ShopgateLibraryException
     */
    public static function db_select_query($table, $where = array(), $select = '*')
    {
        $sqlQuery = "SELECT ";

        /**
         * Handle selection of multiple columns
         */
        if (is_array($select)) {
            foreach ($select as $col) {
                $sqlQuery .= "{$col}, ";
            }
            $sqlQuery = rtrim(trim($sqlQuery), ',');
        } else {
            $sqlQuery .= "c.{$select}";
        }

        $sqlQuery .= " FROM " . $table . " AS c ";

        /**
         * Handle the where clause
         */
        if ($where) {
            $sqlQuery .= "WHERE ";
            $and = false;
            foreach ($where as $col => $value) {
                if ($and) {
                    $sqlQuery .= " AND ";
                }
                $sqlQuery .= "c.{$col} = '" . $value . "'";
                $and = true;
            }
        }

        $sqlQuery .= ";";
        $queryResult = self::db_query_print_on_err($sqlQuery);

        /**
         * Queue orders up
         */
        $orders = array();
        while ($row = self::db_fetch_array($queryResult)) {
            array_push($orders, $row);
        }

        return $orders;
    }

    /**
     * Get all columns of the table
     *
     * @param $table
     *
     * @return bool|mysqli_result|resource
     */
    public static function db_get_columns($table)
    {
        $query = "SHOW COLUMNS FROM {$table};";

        return self::db_query_print_on_err($query);
    }

    /**
     * Wraps for example: tep_validate_password (osCommerce)
     *
     * @param string $plain
     * @param string $encrypted
     *
     * @return bool
     */
    public static function validate_password($plain, $encrypted)
    {
        return tep_validate_password($plain, $encrypted);
    }

    /**
     * Wraps for example: tep_href_link (osCommerce)
     *
     * @param string $link
     * @param string $additionalParameters
     *
     * @return string
     */
    public static function href_link($link, $additionalParameters = '')
    {
        return tep_href_link($link, $additionalParameters);
    }

    /**
     * Wraps for example: tep_redirect (osCommerce)
     *
     * @param string $link
     */
    public static function redirect($link)
    {
        tep_redirect($link);
    }

    /**
     * Wraps for example: tep_get_all_get_params (osCommerce)
     *
     * @return string
     */
    public static function get_all_get_params()
    {
        return tep_get_all_get_params();
    }

    /**
     * Wraps for example: tep_draw_form (osCommerce)
     *
     * @param string $name
     * @param string $action
     * @param string $parameters
     * @param string $method
     * @param string $attributes
     *
     * @return string
     */
    public static function draw_form($name, $action, $parameters = '', $method = 'post', $attributes = '')
    {
        return tep_draw_form($name, $action, $parameters, $method, $attributes);
    }
}
