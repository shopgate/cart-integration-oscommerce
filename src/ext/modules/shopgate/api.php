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

define('SHOPGATE_PLUGIN_API_REQUEST', 1);

// Include the shopgate library (i.e. core.php; customers.php; JSON.php etc.)
require_once(dirname(__FILE__) . '/base/shopgate_config.php');
require_once(dirname(__FILE__) . '/bootstrap.php');
require_once(dirname(__FILE__) . '/vendor/autoload.php');

/**
 * @link https://github.com/lucatume/di52
 * @see Shopgate_Di
 */
$di = new tad_DI52_Container();
$di->register('Shopgate_Di');

// Change to a base directory to include all files from
$shopgateShopBaseDir = realpath(dirname(__FILE__) . "/../../../");
chdir($shopgateShopBaseDir);

/** @var ShopgateConfigOsCommerce $shopgateConfig */
$shopgateConfig = $di->make('ShopgateConfigOsCommerce');

// application_top couldn't read the browser language here and also no session is set!
if (empty($_GET['language'])) {
    // Use the language from the shopgate config file
    $_GET['language'] = strtolower($shopgateConfig->getLanguage());
}

// try finding out if this is swisscart instead of plain osCommerce
$shopgateSwisscartVersion = '';

// swisscart >= 4.0 defines a constant in the admin area's application_top.php
$shopgateApplicationTop = @file_get_contents('admin/includes/application_top.php');
if ($shopgateApplicationTop !== false) {
    $shopgateRegexMatches = array();
    if (preg_match(
        '/define\([\'"]SWISSCART_VERSION[\'"].*,.*[\'"](?P<swisscartVersion>.*)[\'"]\);/',
        $shopgateApplicationTop, $shopgateRegexMatches
    )) {
        // Only one match expected
        $shopgateSwisscartVersion = !empty($shopgateRegexMatches['swisscartVersion'])
            ? $shopgateRegexMatches['swisscartVersion'] : '';
    }
}

// on swisscart the admin application_top file must be loaded instead of the frontend application_top file!
if (!empty($shopgateSwisscartVersion)) {
    include_once('includes/application_top.php');

    // password functions inclusion may be missing in the backend application_top file!
    if (!function_exists('tep_validate_password')) {
        require(rtrim(DIR_WS_FUNCTIONS, "/") . '/password_funcs.php');
    }
    // currencies class needed!
    if (!class_exists('currencies')) {
        require(rtrim(DIR_WS_CLASSES, "/") . '/currencies.php');
    }
    if (!function_exists('tep_get_products_special_price')) {
        function tep_get_products_special_price($product_id)
        {
            $product_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "' and status = 1");
            $product = tep_db_fetch_array($product_query);

            return $product['specials_new_products_price'];
        }
    }

    if (!defined('FILENAME_PRODUCT_INFO')) {
        require(rtrim(DIR_WS_INCLUDES, "/") . '/filenames.php');
    }

    // if this is an old swisscart version that doesn't define the constant, do it now
    if (!defined('SWISSCART_VERSION')) {
        define('SWISSCART_VERSION', $shopgateSwisscartVersion);
    }
} else {
    include_once('includes/application_top.php');
}

// Now the plugin can be included
include_once dirname(__FILE__) . '/plugin.php';

$shopgateMarketplaceFieldName = 'marketplace';
$shopgateMarketplaceIsoCode2  = '';
$shopgatePluginTypeMap        = array();

if (!empty($_REQUEST[$shopgateMarketplaceFieldName])) {
    $shopgateMarketplaceIsoCode2 = strtoupper($_REQUEST[$shopgateMarketplaceFieldName]);
    $shopgatePluginTypeMap       = $shopgatePluginTypeMap = ShopgatePluginType::getMap();
    if (!array_key_exists($shopgateMarketplaceIsoCode2, $shopgatePluginTypeMap)) {
        $shopgatePluginTypeMap[$shopgateMarketplaceIsoCode2] = 1;
    }
}

if ($shopgateConfig->getIsUsaPlugin() === null) {
    if ($shopgatePluginTypeMap[$shopgateMarketplaceIsoCode2]) {
        $shopgateConfig->setIsUsaPlugin(1);
        $shopgateConfig->saveFile(array('is_usa_plugin'));
    }
}

if ($shopgateConfig->getIsUsaPlugin()
    || !empty($shopgateMarketplaceIsoCode2)
    && $shopgatePluginTypeMap[$shopgateMarketplaceIsoCode2]
) {
    $shopgateFramework = $di->make('ShopgatePluginOsCommerceUsa');
} else {
    $shopgateFramework = $di->make('ShopgatePluginOsCommerce');
}

/** @var ShopgatePluginOsCommerce $shopgateFramework */
$shopgateFramework->init(array('di' => $di));
$shopgateFramework->handleRequest($_REQUEST);
