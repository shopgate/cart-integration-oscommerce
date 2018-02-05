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
### Plugin ###
define('SHOPGATE_CONFIG_EXTENDED_ENCODING', 'Shop system encoding');
define('SHOPGATE_CONFIG_EXTENDED_ENCODING_DESCRIPTION', 'Choose the encoding of your shop system. This is usually "%s" for %s.');
define('SHOPGATE_CONFIG_WIKI_LINK', 'http://wiki.shopgate.com/OsCommerce');

### Menu ###
define('BOX_SHOPGATE', 'Shopgate');
define('BOX_SHOPGATE_INFO', 'What is Shopgate');
define('BOX_SHOPGATE_HELP', 'Installation aid');
define('BOX_SHOPGATE_REGISTER', 'Registration');
define('BOX_SHOPGATE_CONFIG', 'Settings');
define('BOX_SHOPGATE_MERCHANT', 'Shopgate login');

### Links ###
define('SHOPGATE_LINK_HOME', 'http://www.shopgate.com/en/');

### Storno ###
define("SHOPGATE_ORDER_NOT_FOUND", "The given order can't be found at Shopgate nor in the shopsystem.");

### Configuration ###
define('SHOPGATE_CONFIG_TITLE', 'SHOPGATE');
define('SHOPGATE_CONFIG_ERROR', 'ERROR:');
define('SHOPGATE_CONFIG_ERROR_SAVING', 'Error saving configuration. ');
define('SHOPGATE_CONFIG_ERROR_LOADING', 'Error loading configuration. ');
define('SHOPGATE_CONFIG_ERROR_READ_WRITE', 'Please check the permissions (777) for the folder &quot;data/configuration&quot; of the Shopgate plugin.');
define('SHOPGATE_CONFIG_ERROR_INVALID_VALUE', 'Please check your input in the following fields: ');
define('SHOPGATE_CONFIG_ERROR_DUPLICATE_SHOP_NUMBERS', 'There are multiple configurations with the same shop number. This can cause major unforeseen issues!');
define('SHOPGATE_CONFIG_INFO_MULTIPLE_CONFIGURATIONS', 'Configurations for multiple market places are active.');
define('SHOPGATE_CONFIG_SAVE', 'Save');
define('SHOPGATE_CONFIG_GLOBAL_CONFIGURATION', 'Global configuration');
define('SHOPGATE_CONFIG_USE_GLOBAL_CONFIG', 'Use the global configuration for this language.');
define('SHOPGATE_CONFIG_MULTIPLE_SHOPS_BUTTON', 'Setup multiple Shopgate marketplaces');
define(
'SHOPGATE_CONFIG_LANGUAGE_SELECTION',
    'At Shopgate you need a shop for each marketplace restricted to one language and currency. Here you can map the configured languages to your Shopgate shops on different '
    .
    'marketplaces. Choose a language and enter the credentials of your Shopgate shop at the corresponding marketplace. If you do not have a Shopgate shop for a certain language '
    .
    'the global configuration will be used for this one.'
);

### Connection Settings ###
define('SHOPGATE_CONFIG_CONNECTION_SETTINGS', 'Connection Settings');

define('SHOPGATE_CONFIG_PLUGIN_TYPE', 'Module type');
define('SHOPGATE_CONFIG_PLUGIN_TYPE_NON_US', 'Non US');
define('SHOPGATE_CONFIG_PLUGIN_TYPE_US', 'US');
define(
'SHOPGATE_CONFIG_PLUGIN_TYPE_DESCRIPTION',
'Choose the type of module here. The setting depends on the Shopgate marketplace, for which you have registered. "Non US" is intended to be used by shops in countries like Germany, Austria or Switzerland, while "US" is used for shops in countries with more complex tax systems.'
);

define('SHOPGATE_CONFIG_CUSTOMER_NUMBER', 'Customer number');
define('SHOPGATE_CONFIG_CUSTOMER_NUMBER_DESCRIPTION', 'You can find your customer number at the &quot;Integration&quot; section of your shop.');

define('SHOPGATE_CONFIG_SHOP_NUMBER', 'Shop number');
define('SHOPGATE_CONFIG_SHOP_NUMBER_DESCRIPTION', 'You can find the shop number at the &quot;Integration&quot; section of your shop.');

define('SHOPGATE_CONFIG_APIKEY', 'API key');
define('SHOPGATE_CONFIG_APIKEY_DESCRIPTION', 'You can find the API key at the &quot;Integration&quot; section of your shop.');

### Mobile Redirect ###
define('SHOPGATE_CONFIG_MOBILE_REDIRECT_SETTINGS', 'Mobile Redirect');

define('SHOPGATE_CONFIG_ALIAS', 'Shop alias');
define('SHOPGATE_CONFIG_ALIAS_DESCRIPTION', 'You can find the alias at the &quot;Integration&quot; section of your shop.');

define('SHOPGATE_CONFIG_CNAME', 'Custom URL to mobile webpage (CNAME) incl. http://');
define(
'SHOPGATE_CONFIG_CNAME_DESCRIPTION',
    'Enter a custom URL (defined by CNAME) for your mobile website. You can find the URL at the &quot;Integration&quot; section of your shop '
    .
    'after you activated this option in the &quot;Settings&quot; &equals;&gt; &quot;Mobile website / webapp&quot; section.'
);

define('SHOPGATE_CONFIG_REDIRECT_LANGUAGES', 'Redirected languages');
define(
'SHOPGATE_CONFIG_REDIRECT_LANGUAGES_DESCRIPTION',
'Choose the languages that should be redirected to this Shopgate shop. At least one language must be selected. Hold CTRL to select multiple entries.'
);

### Export ###
define('SHOPGATE_CONFIG_EXPORT_SETTINGS', 'Exporting Categories and Products');

define('SHOPGATE_CONFIG_LANGUAGE', 'Language');
define('SHOPGATE_CONFIG_LANGUAGE_DESCRIPTION', 'Choose the language in which categories and products should be exported.');

define('SHOPGATE_CONFIG_EXTENDED_CURRENCY', 'Currency');
define('SHOPGATE_CONFIG_EXTENDED_CURRENCY_DESCRIPTION', 'Choose the currency for products export.');

define('SHOPGATE_CONFIG_EXTENDED_COUNTRY', 'Country');
define('SHOPGATE_CONFIG_EXTENDED_COUNTRY_DESCRIPTION', 'Choose the country for which your products should be exported');

define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE', 'Tax zone for Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE_DESCRIPTION', 'Choose the valid tax zone for Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER', 'Reverse category sort order');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_ON', 'Yes');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_OFF', 'No');
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_DESCRIPTION',
'Choose "Yes" if the sort order of the categories in your mobile shop appears upside down.'
);

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER', 'Reverse products sort order');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_ON', 'Yes');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_OFF', 'No');
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_DESCRIPTION',
'Choose "Yes" if the sort order of the products in your mobile shop appears upside down.'
);

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP', 'Price group for Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_DESCRIPTION', 'Choose the valid price group for Shopgate (the customer group of which the price information is taken for the products export).');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_OFF', '-- Deactivated --');

### Orders Import ###
define('SHOPGATE_CONFIG_ORDER_IMPORT_SETTINGS', 'Importing Orders');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP', 'Customer group');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP_DESCRIPTION', 'Choose the Shopgate customer group (the customer group that all guest customers will be set to on importing orders).');

define('SHOPGATE_CONFIG_EXTENDED_SHIPPING', 'Shipping method');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_DESCRIPTION', 'Choose the shipping method for the import of the orders. This will be used to calculate the tax for the shipping costs.');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_NO_SELECTION', '-- no selection --');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED', 'Shipping not blocked');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED_DESCRIPTION', 'Choose the status for orders that are not blocked for shipping by Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED', 'Shipping blocked');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED_DESCRIPTION', 'Choose the status for orders that are blocked for shipping by Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT', 'Shipped');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT_DESCRIPTION', 'Choose the status you apply to orders that have been shipped.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED', 'Cancelled');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_NOT_SET', '- Status not set -');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_DESCRIPTION', 'Choose the status for orders that have been cancelled.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING', 'Cancel shipping');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SEND_CONFIRMATION_MAIL', 'Send order confirmation mail');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SEND_CONFIRMATION_MAIL_DESCRIPTION', 'This will generate the shopsystem default email. Manual changes wont be honored');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_ON', 'Yes');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_OFF', 'No');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_DESCRIPTION', 'If "yes" is chosen the shipping will always be cancelled at Shopgate, too.');

define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING', 'Display names for payment methods');
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_DESCRIPTION', "Individual names for payment methods, which are used on order import. Defined by '=' and separated by ';'.<br/>(Example: PREPAY=Prepay;SHOPGATE=Handled by Shopgate)<br/>");
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_LINK', 'https://support.shopgate.com/hc/en-us/articles/202798386-Connecting-to-oscommerce#4.4');
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_LINK_DESCRIPTION', "Link to the support page");


### System Settings ###
define('SHOPGATE_CONFIG_SYSTEM_SETTINGS', 'System Settings');

define('SHOPGATE_CONFIG_SERVER_TYPE', 'Shopgate server');
define('SHOPGATE_CONFIG_SERVER_TYPE_LIVE', 'Live');
define('SHOPGATE_CONFIG_SERVER_TYPE_PG', 'Playground');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM', 'Custom');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL', 'Custom Shopgate server url');
define('SHOPGATE_CONFIG_SERVER_TYPE_DESCRIPTION', 'Choose the Shopgate server to connect to.');

### Orders overview ###
define('ENTRY_IS_TEST_ORDER_COMMENT_TEXT', '### THIS IS A TEST ORDER ###');

define('ENTRY_ORDER_ADDED_BY_SHOPGATE_COMMENT_TEXT', 'Order added by Shopgate.');

define('ENTRY_PAYMENT_SHOPGATE_ORDER_NUMBER_COMMENT_TEXT', 'Shopgate order number:');
define('ENTRY_PAYMENT_TRANSACTION_NUMBER_COMMENT_TEXT', 'Payment transaction number:');
define('ENTRY_SHIPPING_METHOD_COMMENT_TEXT', 'Shipping method:');

define('ENTRY_NEW_PAYMENT_STATUS_IS_PAID_COMMENT_TEXT', 'Order status changed by Shopgate: Payment received');
define('ENTRY_NEW_PAYMENT_STATUS_IS_NOT_PAID_COMMENT_TEXT', 'Order status changed by Shopgate: Payment not received, yet');

define('ENTRY_SHIPPING_BLOCKED_COMMENT_TEXT', 'Note: Shipping of this order is blocked by Shopgate!');
define('ENTRY_SHIPPING_NOT_BLOCKED_COMMENT_TEXT', 'Note: Shipping of this order is not blocked by Shopgate!');
define('ENTRY_NEW_SHIPPING_STATUS_SHIPPING_BLOCKED_COMMENT_TEXT', 'Order status changed by Shopgate: Shipping is blocked!');
define('ENTRY_NEW_SHIPPING_STATUS_SHIPPING_APPROVED_COMMENT_TEXT', 'Order status changed by Shopgate: Shipping is not blocked anymore!');

define('ENTRY_ALREADY_SHIPPED_WARNING', 'Attention: The shipping status could not be changed at Shopgate because the order has already been marked as shipped.');

define(
'ENTRY_ORDER_SHIPPING_BLOCKED_IGNORED_WARNING',
    'Attention: The order was marked as "shipped" at Shopgate although Shopgate reports status "'
    . ENTRY_SHIPPING_BLOCKED_COMMENT_TEXT . '"!'
);

define('ENTRY_PAYMENT_METHOD_CHANGED', 'The payment method has been changed to "%s" (from "%s").');

define('ENTRY_PAYMENT_SHOPGATE', 'Shopgate');

define('ENTRY_PAYMENT_UPDATED', "Shopgate: Payment information has been updated: \n\n");

define('ENTRY_PAYMENT_PREPAY', 'Prepay');
define('ENTRY_PAYMENT_PREPAY_PAYMENT_PURPOSE', 'The customer has been instructed to transfer the complete amount to your bank account using the following payment purpose:');
define('ENTRY_PAYMENT_MAPPED', "Payment method '%s' replaced with '%s'");

define('ENTRY_PAYMENT_ELV', 'Debit');
define('ENTRY_PAYMENT_ELV_INFOTEXT', 'Please debit the amount from the following bank account:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_OWNER', 'Bank Account Owner:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_NUMBER', 'Bank Account Number:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_CODE', 'Bank Code:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_NAME', 'Bank Name:');

define('ENTRY_PAYMENT_CASH_ON_DELIVERY', 'Cash on Delivery');

define('ENTRY_PAYMENT_SHOPGATE_GENERIC', 'mobile_payment');

define('ENTRY_SUB_TOTAL', 'Sub-Total (ex):');
define('ENTRY_SHIPPING', 'Shipping:');
define('ENTRY_PAYMENT', 'Payment:');
define('ENTRY_TAX', 'Tax (%s%%):');
define('ENTRY_TOTAL', 'Total:');
define('ENTRY_COUPON', 'Discount Coupon %s applied:');

define('ENTRY_ORDER_MARKED_AS_SHIPPED', 'The order has been marked "shipped" at Shopgate.');
define('ENTRY_SHOPGATE_MODULE_ERROR', 'An error occured while executing the Shopgate module.');
define('ENTRY_SHOPGATE_UNKNOWN_ERROR', 'An unknown error occured.');

define('ENTRY_ERRORS_EXIST', 'One or more problems have been encountered:');
define('ENTRY_PRODUCT_NOT_FOUND', 'The product with the products_id #PRODUCTS_ID# could not be found in the database!');

### Item Export ###
define('ENTRY_AVAILABLE_TEXT_AVAILABLE', 'Available');
define('ENTRY_AVAILABLE_TEXT_AVAILABLE_SHORTLY', 'This article will be reordered and will be delivered as soon it is avaliable.');
define('ENTRY_AVAILABLE_TEXT_NOT_AVAILABLE', 'Not available');
define('ENTRY_AVAILABLE_TEXT_AVAILABLE_ON_DATE', 'Available on #DATE#');

define("MODULE_PAYMENT_SHOPGATE_DELIVERY_ADDRESS_CUSTOM_FIELDS", 'custom delivery field(s)');
define("MODULE_PAYMENT_SHOPGATE_INVOICE_ADDRESS_CUSTOM_FIELDS", 'custom invoice field(s)');
define("MODULE_PAYMENT_SHOPGATE_ORDER_CUSTOM_FIELDS", 'custom order field(s)');

define('EMAIL_TEXT_SUBJECT', 'Order Process');
define('EMAIL_TEXT_ORDER_NUMBER', 'Order Number:');
define('EMAIL_TEXT_INVOICE_URL', 'Detailed Invoice:');
define('EMAIL_TEXT_DATE_ORDERED', 'Date Ordered:');
define('EMAIL_TEXT_PRODUCTS', 'Products');
define('EMAIL_TEXT_SUBTOTAL', 'Sub-Total:');
define('EMAIL_TEXT_TAX', 'Tax:        ');
define('EMAIL_TEXT_SHIPPING', 'Shipping: ');
define('EMAIL_TEXT_TOTAL', 'Total:    ');
define('EMAIL_TEXT_DELIVERY_ADDRESS', 'Delivery Address');
define('EMAIL_TEXT_BILLING_ADDRESS', 'Billing Address');
define('EMAIL_TEXT_PAYMENT_METHOD', 'Payment Method');

define('EMAIL_SEPARATOR', '------------------------------------------------------');
define('TEXT_EMAIL_VIA', 'via');

define(
'EMAIL_TEXT_STATUS_UPDATE', 'Your order has been updated to the following status.' . "\n\n" . 'New status: %s' . "\n\n"
    . 'Please reply to this email if you have any questions.' . "\n"
);
define('EMAIL_TEXT_COMMENTS_UPDATE', 'The comments for your order are' . "\n\n%s\n\n");

