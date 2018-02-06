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
$shopgateMobileHeader = '';
$shopgateJsHeader     = '';

if (defined('MODULE_PAYMENT_INSTALLED')
    && (strpos(MODULE_PAYMENT_INSTALLED, 'shopgate.php') !== false)
    && !defined('SHOPGATE_PLUGIN_API_REQUEST')
) {
    require_once(dirname(__FILE__) . '/../../vendor/autoload.php');
    require_once(dirname(__FILE__) . '/../shopgate_config.php');

    try {
        if (empty($_SESSION['languages_id']) && ($_SESSION['languages_id'] !== 0)) {
            $_SESSION['languages_id'] = !empty($languages_id) ? $languages_id : 0;
        }

        $shopgateCurrentLanguage = ShopgateWrapper::db_fetch_array(
            ShopgateWrapper::db_query(
                "SELECT * FROM `" . TABLE_LANGUAGES
                . "` WHERE languages_id = {$_SESSION['languages_id']}"
            )
        );

        $shopgateCurrentLanguage = (empty($shopgateCurrentLanguage) || empty($shopgateCurrentLanguage['code']))
            ? 'en'
            : $shopgateCurrentLanguage['code'];

        $shopgateHeaderConfig = new ShopgateConfigOsCommerce();
        $shopgateHeaderConfig->loadByLanguage($shopgateCurrentLanguage);

        if ($shopgateHeaderConfig->checkUseGlobalFor($shopgateCurrentLanguage)) {
            $shopgateRedirectThisLanguage = in_array(
                $shopgateCurrentLanguage,
                $shopgateHeaderConfig->getRedirectLanguages()
            );
        } else {
            $shopgateRedirectThisLanguage = true;
        }

        if ($shopgateRedirectThisLanguage) {

            // instantiate and set up redirect class
            $shopgateBuilder  = new ShopgateBuilder($shopgateHeaderConfig);
            $shopgateRedirect = $shopgateBuilder->buildRedirect();

            ##################
            # redirect logic #
            ##################

            if (isset($_GET['products_id']) && !empty($_GET['products_id'])) {
                $shopgateJsHeader = $shopgateRedirect->buildScriptItem($_GET['products_id']);
            } elseif (isset($HTTP_GET_VARS['products_id']) && !empty($HTTP_GET_VARS['products_id'])) {
                $shopgateJsHeader = $shopgateRedirect->buildScriptItem($HTTP_GET_VARS['products_id']);
            } elseif (!empty($current_category_id)) {
                $shopgateJsHeader = $shopgateRedirect->buildScriptCategory($current_category_id);
            } elseif (sgIsHomepage()) {
                $shopgateJsHeader = $shopgateRedirect->buildScriptShop();
            } else {
                $shopgateJsHeader = $shopgateRedirect->buildScriptDefault();
            }
        }
    } catch (ShopgateLibraryException $e) {
    }
}

/**
 * @return bool
 */
function sgIsHomepage()
{
    $scriptName = explode('/', $_SERVER['SCRIPT_NAME']);
    $scriptName = end($scriptName);

    if ($scriptName != 'index.php') {
        return false;
    }

    return true;
}
