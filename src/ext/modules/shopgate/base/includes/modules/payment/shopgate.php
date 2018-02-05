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

include_once DIR_FS_CATALOG . 'ext/modules/shopgate/base/shopgate_config.php';
include_once DIR_FS_CATALOG . 'ext/modules/shopgate/base/includes/modules/payment/ShopgateInstallHelper.php';

class shopgate
{
    var $code, $title, $description, $enabled, $sort_order;

    function shopgate()
    {
        global $order;

        $this->code        = 'shopgate';
        $this->title       = MODULE_PAYMENT_SHOPGATE_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION;
        $this->enabled     = false;
        $this->sort_order  = 88457;
    }

    function mobile_payment()
    {
        global $order;

        $this->code        = 'shopgate';
        $this->title       = MODULE_PAYMENT_SHOPGATE_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION;
        $this->enabled     = false;
        $this->sort_order  = 88457;
    }

    function update_status()
    {
    }

    function javascript_validation()
    {
        return false;
    }

    function selection()
    {
        return array('id' => $this->code, 'module' => $this->title, 'description' => $this->info);
    }

    function pre_confirmation_check()
    {
        return false;
    }

    function confirmation()
    {
        return array('title' => MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION);
    }

    function process_button()
    {
        return false;
    }

    function before_process()
    {
        return false;
    }

    function after_process()
    {
        global $insert_id;

        if ($this->order_status) {
            ShopgateWrapper::db_query(
                "UPDATE " . TABLE_ORDERS . " SET orders_status='" . $this->order_status . "' WHERE orders_id='"
                . $insert_id . "'"
            );
        }
    }

    function get_error()
    {
        return false;
    }

    function check()
    {
        if (!isset ($this->_check)) {
            $check_query  = ShopgateWrapper::db_query(
                "select configuration_value from " . TABLE_CONFIGURATION
                . " where configuration_key = 'MODULE_PAYMENT_SHOPGATE_STATUS'"
            );
            $this->_check = ShopgateWrapper::db_num_rows($check_query);
        }

        return $this->_check ? true : false;
    }

    /**
     * install the module
     *
     * -- KEYS --:
     * MODULE_PAYMENT_SHOPGATE_STATUS - The state of the module ( true / false )
     * MODULE_PAYMENT_SHOPGATE_ALLOWED - Is the module allowed on frontend
     * MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID - (DEPRECATED) keep it for old installations
     */
    function install()
    {
        $this->defineTables();

        ShopgateWrapper::db_query(
            "delete from " . TABLE_CONFIGURATION
            . " where configuration_key in ('MODULE_PAYMENT_SHOPGATE_STATUS', 'MODULE_PAYMENT_SHOPGATE_ALLOWED', 'MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID')"
        );
        ShopgateWrapper::db_query(
            "insert into " . TABLE_CONFIGURATION
            . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SHOPGATE_STATUS', 'True', '6', '"
            . $this->sort_order . "', now())"
        );
        ShopgateWrapper::db_query(
            "insert into " . TABLE_CONFIGURATION
            . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SHOPGATE_ALLOWED', '0',   '6', '"
            . $this->sort_order . "', now())"
        );
        $result = ShopgateWrapper::db_query(
            'select configuration_key,configuration_value from configuration as c where c.configuration_key = "'
            . ShopgateInstallHelper::SHOPGATE_DATABASE_CONFIG_KEY . '"'
        );
        $row    = ShopgateWrapper::db_fetch_array($result);
        if (empty($row)) {
            ShopgateWrapper::db_query(
                "insert into " . TABLE_CONFIGURATION
                . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SHOPGATE_IDENT'  , '0',   '6', '"
                . $this->sort_order . "', now())"
            );
        }
        $this->installTables();
        $this->updateDatabase();
        $this->estimatePluginType();
        $installHelper = new ShopgateInstallHelper();
        $installHelper->sendData();
    }

    /**
     * remove the shopgate module
     */
    function remove()
    {
        // MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID - Keep this on removing for old installation
        ShopgateWrapper::db_query(
            "delete from " . TABLE_CONFIGURATION
            . " where configuration_key in ('MODULE_PAYMENT_SHOPGATE_STATUS', 'MODULE_PAYMENT_SHOPGATE_ALLOWED', 'MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID')"
        );
    }

    /**
     * Keep the array empty to disable all configuration options
     *
     * @return mixed
     */
    function keys()
    {
        return array();
    }

    /**
     * Install shopgate tables
     */
    private function installTables()
    {
        ShopgateWrapper::db_query(
            "
			CREATE TABLE IF NOT EXISTS `" . TABLE_ORDERS_SHOPGATE_ORDER . "` (
					`shopgate_order_id` INT(11) NOT NULL AUTO_INCREMENT,
					`orders_id` INT(11) NOT NULL,
					`shopgate_order_number` BIGINT(20) NOT NULL,
					`shopgate_shop_number` BIGINT(20) UNSIGNED DEFAULT NULL,
					`is_paid` tinyint(1) UNSIGNED DEFAULT NULL,
					`is_shipping_blocked` tinyint(1) UNSIGNED DEFAULT NULL,
					`payment_infos` TEXT NULL,
					`order_data` TEXT NULL,
					`is_sent_to_shopgate` tinyint(1) UNSIGNED DEFAULT NULL,
					`is_cancellation_sent_to_shopgate` tinyint(1) UNSIGNED DEFAULT NULL,
					`modified` datetime DEFAULT NULL,
					`created` datetime DEFAULT NULL,
					PRIMARY KEY (`shopgate_order_id`)
			) ENGINE=MyISAM; "
        );

        ShopgateWrapper::db_query(
            "
			CREATE TABLE IF NOT EXISTS `" . TABLE_CUSTOMERS_SHOPGATE_CUSTOMER . "` (
					`customer_id` INT(11) NOT NULL,
					`customer_token` VARCHAR(50) NULL,
					PRIMARY KEY (`customer_id`)
			) ENGINE=MyISAM; "
        );
    }

    /**
     * update existing database
     */
    private function updateDatabase()
    {
        if ($this->checkColumn('shopgate_shop_number')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD `shopgate_shop_number` BIGINT(20) UNSIGNED NULL AFTER `shopgate_order_number`;';
            ShopgateWrapper::db_query($qry);
        }

        if ($this->checkColumn('is_paid')) {
            $qry = 'ALTER TABLE  `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `is_paid` TINYINT(1) UNSIGNED NULL AFTER `shopgate_shop_number`;';
            ShopgateWrapper::db_query($qry);
        }

        if ($this->checkColumn('is_shipping_blocked')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `is_shipping_blocked` TINYINT(1) UNSIGNED NULL AFTER  `is_paid`;';
            ShopgateWrapper::db_query($qry);
        }

        if ($this->checkColumn('payment_infos')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `payment_infos` TEXT NULL AFTER  `is_shipping_blocked`;';
            ShopgateWrapper::db_query($qry);
        }

        if ($this->checkColumn('is_sent_to_shopgate')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `is_sent_to_shopgate` TINYINT(1) UNSIGNED NULL AFTER `payment_infos`;';
            ShopgateWrapper::db_query($qry);
        }

        if ($this->checkColumn('modified')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `modified` DATETIME NULL AFTER `is_sent_to_shopgate`;';
            ShopgateWrapper::db_query($qry);
        }

        if ($this->checkColumn('created')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER . '` ADD  `created` DATETIME NULL AFTER `modified`;';
            ShopgateWrapper::db_query($qry);
        }

        if ($this->checkColumn('order_data')) {
            $qry =
                'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER . '` ADD  `order_data` TEXT NULL AFTER `payment_infos`;';
            ShopgateWrapper::db_query($qry);
        }

        if ($this->checkColumn('is_cancellation_sent_to_shopgate')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `is_cancellation_sent_to_shopgate` tinyint(1) UNSIGNED NULL AFTER `is_sent_to_shopgate`;';
            ShopgateWrapper::db_query($qry);
        }

        $languages = ShopgateWrapper::db_query('SELECT `languages_id`, `code` FROM `' . TABLE_LANGUAGES . '`;');
        if (empty($languages)) {
            echo MODULE_PAYMENT_SHOPGATE_ERROR_READING_LANGUAGES;

            return;
        }

        // load global configuration
        try {
            $config = new ShopgateConfigOsCommerce();
            $config->loadFile();
        } catch (ShopgateLibraryException $e) {
            if (!($config instanceof ShopgateConfig)) {
                echo MODULE_PAYMENT_SHOPGATE_ERROR_LOADING_CONFIG;

                return;
            }
        }

        $languageCodes   = array();
        $configFieldList = array('language', 'redirect_languages');
        while ($language = ShopgateWrapper::db_fetch_array($languages)) {
            // collect language codes to enable redirect
            $languageCodes[] = $language['code'];

            switch ($language['code']) {
                case 'de':
                    $statusNameNew    = 'Versand blockiert (Shopgate)';
                    $statusNameSearch = '%shopgate%';
                    break;
                case 'en':
                    $statusNameNew    = 'Shipping blocked (Shopgate)';
                    $statusNameSearch = '%shopgate%';
                    break;
                default:
                    continue 2;
            }

            $result               = ShopgateWrapper::db_query(
                "SELECT `orders_status_id`, `orders_status_name` " .
                "FROM `" . TABLE_ORDERS_STATUS . "` " .
                "WHERE LOWER(`orders_status_name`) LIKE '" . ShopgateWrapper::db_input($statusNameSearch) . "' " .
                "AND `language_id` = " . ShopgateWrapper::db_input($language['languages_id']) . ";"
            );
            $checkShippingBlocked = ShopgateWrapper::db_fetch_array($result);

            if (!empty($checkShippingBlocked)) {
                $orderStatusShippingBlockedId = $checkShippingBlocked['orders_status_id'];
            } else {
                // if no orders_status_id has been determined yet and the status could not be found, create a new one
                if (!isset($orderStatusShippingBlockedId)) {
                    $result                       = ShopgateWrapper::db_query(
                        "SELECT max(orders_status_id) AS orders_status_id FROM " . TABLE_ORDERS_STATUS
                    );
                    $nextId                       = ShopgateWrapper::db_fetch_array($result);
                    $orderStatusShippingBlockedId = $nextId['orders_status_id'] + 1;
                }

                // insert the status into the database
                ShopgateWrapper::db_query(
                    "INSERT INTO `" . TABLE_ORDERS_STATUS . "` " .
                    "(`orders_status_id`, `language_id`, `orders_status_name`) VALUES " .
                    "(" . ShopgateWrapper::db_input($orderStatusShippingBlockedId) . ", " . ShopgateWrapper::db_input(
                        $language['languages_id']
                    ) . ", '" . ShopgateWrapper::db_input($statusNameNew) . "');"
                );
            }

            // set global order status id
            if ($language['code'] == DEFAULT_LANGUAGE) {
                $config->setOrderStatusShippingBlocked($orderStatusShippingBlockedId);
                $configFieldList[] = 'order_status_shipping_blocked';
            }
        }

        // get the actual definition of the plugin version
        if (!defined("SHOPGATE_PLUGIN_VERSION")) {
            require_once(rtrim(DIR_FS_CATALOG, "/") . '/ext/modules/shopgate/plugin.php');
        }
        // shopgate table version equals to the SHOPGATE_PLUGIN_VERSION, save that version to the config file
        $config->setShopgateTableVersion(SHOPGATE_PLUGIN_VERSION);
        $configFieldList[] = 'shopgate_table_version';
        // save default language, order_status_id and redirect languages in the configuration
        try {
            $config->setLanguage(DEFAULT_LANGUAGE);
            $config->setRedirectLanguages($languageCodes);
            $config->saveFile($configFieldList);
        } catch (ShopgateLibraryException $e) {
            echo MODULE_PAYMENT_SHOPGATE_ERROR_SAVING_CONFIG;
        }
    }

    /**
     * A method that tries to detect which type of Plugin should be used. It can be usa- or default-version
     */
    private function estimatePluginType()
    {
        $isUsaPlugin = null;

        // load global configuration
        try {
            $config = new ShopgateConfigOsCommerce();
            $config->loadFile();
        } catch (ShopgateLibraryException $e) {
            if (!($config instanceof ShopgateConfig)) {
                echo MODULE_PAYMENT_SHOPGATE_ERROR_LOADING_CONFIG;

                return;
            }
        }

        // do only if no type is set, yet
        if ($config->getIsUsaPlugin() !== null) {
            return;
        }

        // Check store country from configuration and set to marketplace
        $pluginTypeMap = ShopgatePluginType::getMap();
        $queryResult   = ShopgateWrapper::db_query(
            "SELECT c.countries_id, c.countries_iso_code_2, c.countries_name FROM " . TABLE_CONFIGURATION
            . " cg LEFT JOIN " . TABLE_COUNTRIES
            . " c ON(cg.configuration_value = c.countries_id) WHERE configuration_key='STORE_COUNTRY'"
        );
        $row           = ShopgateWrapper::db_fetch_array($queryResult);
        if (!empty($row)) {
            $isoCode2 = strtoupper($row['countries_iso_code_2']);
            if (array_key_exists($isoCode2, $pluginTypeMap)) {
                $isUsaPlugin = $pluginTypeMap[$isoCode2];
            } else {
                $isUsaPlugin = 1;
            }
        }

        if ($isUsaPlugin !== null) {
            $config->setIsUsaPlugin($isUsaPlugin);
            $config->saveFile(array('is_usa_plugin'));
        }
    }

    /**
     * Check if the column exists in the specified table
     *
     * @param string $columnName
     * @param string $table
     *
     * @return bool
     */
    private function checkColumn($columnName, $table = TABLE_ORDERS_SHOPGATE_ORDER)
    {
        $result = ShopgateWrapper::db_query("show columns from `{$table}`");

        $exists = false;
        while ($field = ShopgateWrapper::db_fetch_array($result)) {
            if ($field['Field'] == $columnName) {
                $exists = true;
                break;
            }
        }

        return !$exists;
    }

    /**
     * Defining table names inside class
     */
    private function defineTables()
    {
        if (!defined('TABLE_ORDERS_SHOPGATE_ORDER')) {
            define('TABLE_ORDERS_SHOPGATE_ORDER', 'orders_shopgate_order');
        }
        if (!defined('TABLE_CUSTOMERS_SHOPGATE_CUSTOMER')) {
            define('TABLE_CUSTOMERS_SHOPGATE_CUSTOMER', 'customers_shopgate_customer');
        }
    }
}
