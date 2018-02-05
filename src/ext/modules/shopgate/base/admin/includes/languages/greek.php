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
define('SHOPGATE_CONFIG_EXTENDED_ENCODING', '����������� Shop system');
define('SHOPGATE_CONFIG_EXTENDED_ENCODING_DESCRIPTION', '�������� ��� ������������ ��� shop system. ���� ����� ������� "%s" ��� %s.');
define('SHOPGATE_CONFIG_WIKI_LINK', 'http://wiki.shopgate.com/OsCommerce');

### Menu ###
define('BOX_SHOPGATE', 'Shopgate');
define('BOX_SHOPGATE_INFO', '�� ����� �� Shopgate');
define('BOX_SHOPGATE_HELP', '������� ������������');
define('BOX_SHOPGATE_REGISTER', '�������');
define('BOX_SHOPGATE_CONFIG', '���������');
define('BOX_SHOPGATE_MERCHANT', '������� Shopgate');

### storno ###
define("SHOPGATE_ORDER_NOT_FOUND", "Η συγκεκριμένη σειρά βρέθηκε ούτε στο Shopgate ακόμα στο σύστημα κατάστημα.");

### Configuration ###
define('SHOPGATE_CONFIG_TITLE', 'SHOPGATE');
define('SHOPGATE_CONFIG_ERROR', '������:');
define('SHOPGATE_CONFIG_ERROR_SAVING', '������ ���� ��� ���������� ��� ���������. ');
define('SHOPGATE_CONFIG_ERROR_LOADING', '������ ���� �� ������� ��� ���������. ');
define('SHOPGATE_CONFIG_ERROR_READ_WRITE', '�������� ������� ��� ��������� (777) ��� �� ������ &quot;data/configuration&quot; ��� Shopgate plugin.');
define('SHOPGATE_CONFIG_ERROR_INVALID_VALUE', '�������� ������� ��� ���������� ��� ��� �������� �����: ');
define('SHOPGATE_CONFIG_ERROR_DUPLICATE_SHOP_NUMBERS', '�������� ��������� ������������ �� �� ���� ������ ������������. ���� ������ �� ���������� ����� ����������� ������!');
define('SHOPGATE_CONFIG_INFO_MULTIPLE_CONFIGURATIONS', '�� ��������� ��� �������� market places ����� �������.');
define('SHOPGATE_CONFIG_SAVE', '����������');
define('SHOPGATE_CONFIG_GLOBAL_CONFIGURATION', '����� ����������');
define('SHOPGATE_CONFIG_USE_GLOBAL_CONFIG', '�������������� ��� ����� ���������� ��� ����� �� ������.');
define('SHOPGATE_CONFIG_MULTIPLE_SHOPS_BUTTON', '������� ��������� Shopgate marketplaces');
define(
'SHOPGATE_CONFIG_LANGUAGE_SELECTION',
    '��� Shopgate ���������� ��� ��������� ��� ���� ��� marketplace ������������ �� ��� ������ ��� �������. ��� �������� �� �������������� ��� ������������� ������� ��� ������������ ��� Shopgate �� ����������� '
    .
    'marketplaces. �������� ��� ������ ��� �������� �� �������������� ��� ������������ ��� Shopgate ���� ���������� marketplace. �� ��� ����� ��������� Shopgate ��� ��� ������������ ������ '
    .
    '�� �������� �� ������� ��������� ��� ����.'
);

### Connection Settings ###
define('SHOPGATE_CONFIG_CONNECTION_SETTINGS', '��������� ��������');

define('SHOPGATE_CONFIG_CUSTOMER_NUMBER', '������� ������');
define('SHOPGATE_CONFIG_CUSTOMER_NUMBER_DESCRIPTION', '�������� �� ������ ��� ������ ������ ��� ����� &quot;Integration&quot; ��� ������������ ���.');

define('SHOPGATE_CONFIG_SHOP_NUMBER', '������� ������������');
define('SHOPGATE_CONFIG_SHOP_NUMBER_DESCRIPTION', '�������� �� ������ ��� ������ ������������ ��� ����� &quot;Integration&quot; ��� ������������ ���.');

define('SHOPGATE_CONFIG_APIKEY', 'API ������');
define('SHOPGATE_CONFIG_APIKEY_DESCRIPTION', '�������� �� ������ �� ������ API ��� ����� &quot;Integration&quot; ��� ������������ ���.');

### Mobile Redirect ###
define('SHOPGATE_CONFIG_MOBILE_REDIRECT_SETTINGS', '�������� �������');

define('SHOPGATE_CONFIG_ALIAS', '�������� ������������');
define('SHOPGATE_CONFIG_ALIAS_DESCRIPTION', '�������� �� ������ ��� �������� ��� ����� &quot;Integration&quot; ��� ������������ ���.');

define('SHOPGATE_CONFIG_CNAME', '���������� URL ��� ��� ������ ����������� ��� ������ (CNAME) ����. http://');
define(
'SHOPGATE_CONFIG_CNAME_DESCRIPTION',
    '�������� ��� ���������� URL (����������� ��� CNAME) ��� ��� ������ ����������� ��� ������. �������� �� ������ ��� URL ��� ����� &quot;Integration&quot; ��� ������������ ��� '
    .
    '���� �������������� ���� ��� ������� ��� ����� &quot;Settings&quot; &equals;&gt; &quot;Mobile website / webapp&quot; .'
);

define('SHOPGATE_CONFIG_REDIRECT_LANGUAGES', '����������� �������');
define(
'SHOPGATE_CONFIG_REDIRECT_LANGUAGES_DESCRIPTION',
'�������� ��� ������� ��� �� ����������� ��� ��������� Shopgate. ������ �� �������� ���������� ��� ������. �������� �� CTRL ��� ��������� ��������.'
);

### Export ###
define('SHOPGATE_CONFIG_EXPORT_SETTINGS', '������� ���������� ��� ���������');

define('SHOPGATE_CONFIG_LANGUAGE', '������');
define('SHOPGATE_CONFIG_LANGUAGE_DESCRIPTION', '�������� �� ������ ��� ��� ����� �� �������� �� ���������� ��� �� ��������.');

define('SHOPGATE_CONFIG_EXTENDED_CURRENCY', '�������');
define('SHOPGATE_CONFIG_EXTENDED_CURRENCY_DESCRIPTION', '�������� �� ������� ��� �� ����� �� �������� �� ��������.');

define('SHOPGATE_CONFIG_EXTENDED_COUNTRY', '����');
define('SHOPGATE_CONFIG_EXTENDED_COUNTRY_DESCRIPTION', '�������� �� ���� ��� ��� ����� �� �������� �� ��������');

define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE', '���������� ���� ��� �� Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE_DESCRIPTION', '�������� ��� �������� ���������� ���� ��� �� Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER', '���������� ������ ����������� ��� ����������');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_ON', '���');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_OFF', '���');
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_DESCRIPTION',
'�������� "���" �� � ����� ����������� ��� ���������� ���� ������ ��� ������ ����������� ����������.'
);

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER', '���������� ������ ����������� ���������');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_ON', '���');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_OFF', '���');
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_DESCRIPTION',
'�������� "���" �� � ����� ����������� ��� ��������� ���� ������ ��� ������ ����������� ����������.'
);

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP', '���� group ��� �� Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_DESCRIPTION', '�������� ��� ����������� ����� ��� �� Shopgate (� ����� ������� ��� �� ����� �� ����������� ����� ����������� ��� ��� ������� ���������).');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_OFF', '-- ���������������� --');

### Orders Import ###
define('SHOPGATE_CONFIG_ORDER_IMPORT_SETTINGS', '�������� �����������');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP', '����� �������');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP_DESCRIPTION', 'Choose the Shopgate customer group (the customer group that all guest customers will be set to on importing orders).');

define('SHOPGATE_CONFIG_EXTENDED_SHIPPING', '������� ���������');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_DESCRIPTION', '�������� �� ������ ��������� ��� ��� �������� ��� �����������. ���� �� �������������� ��� ��� ���������� �� ���� ��� �� ����� ���������.');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_NO_SELECTION', '-- ����� ������� --');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED', '�������� ����� �����������');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED_DESCRIPTION', '�������� ��� ��������� ��� ����������� �� ������ ��� ����� �������������� ��� �������� ��� �� Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED', '�������� �������������');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED_DESCRIPTION', '�������� ��� ��������� ��� ����������� �� ������ ����� �������������� ��� �������� ��� �� Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT', '�����������');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT_DESCRIPTION', '�������� ��� ��������� ��� ���������� ���� ����������� �� ������ ����� ������.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED', '���������');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_NOT_SET', '- ��������� ��� ���� ���������� -');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_DESCRIPTION', '�������� ��� ��������� ��� ����������� ��� ����� ��������.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING', 'Ακύρωση αποστολή');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_ON', 'Ναι');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_OFF', 'Δεν');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_DESCRIPTION', 'Εάν έχει επιλεγεί ναι , όπως και στην ακύρωση της αποστολής παραγγελίας είναι επίσης ακυρώθηκε.');

### System Settings ###
define('SHOPGATE_CONFIG_SYSTEM_SETTINGS', '��������� ����������');

define('SHOPGATE_CONFIG_SERVER_TYPE', 'Shopgate server');
define('SHOPGATE_CONFIG_SERVER_TYPE_LIVE', 'Live');
define('SHOPGATE_CONFIG_SERVER_TYPE_PG', 'Playground');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM', 'Custom');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL', 'Custom Shopgate server url');
define('SHOPGATE_CONFIG_SERVER_TYPE_DESCRIPTION', '�������� �� Shopgate server ��� �������.');

### Orders overview ###
define('ENTRY_IS_TEST_ORDER_COMMENT_TEXT', '### ���� ����� ��� ����������� ���������� ###');

define('ENTRY_ORDER_ADDED_BY_SHOPGATE_COMMENT_TEXT', '���������� ���������� ��� �� Shopgate.');

define('ENTRY_PAYMENT_SHOPGATE_ORDER_NUMBER_COMMENT_TEXT', '������� ����������� Shopgate:');
define('ENTRY_PAYMENT_TRANSACTION_NUMBER_COMMENT_TEXT', '������� ����������:');
define('ENTRY_SHIPPING_METHOD_COMMENT_TEXT', 'Shipping method:');

define('ENTRY_NEW_PAYMENT_STATUS_IS_PAID_COMMENT_TEXT', '� ��������� ��� ����������� ������ ��� �� Shopgate: ������� �����������');
define('ENTRY_NEW_PAYMENT_STATUS_IS_NOT_PAID_COMMENT_TEXT', '� ��������� ��� ����������� ������ ��� �� Shopgate: � ������� ��� ���� ����������, �����');

define('ENTRY_SHIPPING_BLOCKED_COMMENT_TEXT', '��������: � �������� ����� ��� ����������� ���� ������������ ��� �� Shopgate!');
define('ENTRY_SHIPPING_NOT_BLOCKED_COMMENT_TEXT', '��������: � �������� ����� ��� ����������� ���� ������������ ��� �� Shopgate!');
define('ENTRY_NEW_SHIPPING_STATUS_SHIPPING_BLOCKED_COMMENT_TEXT', '� ��������� ��� ����������� ������ ��� �� Shopgate: �������� ��� ���������!');
define('ENTRY_NEW_SHIPPING_STATUS_SHIPPING_APPROVED_COMMENT_TEXT', '� ��������� ��� ����������� ������ ��� �� Shopgate: �������� ���������!');

define('ENTRY_ALREADY_SHIPPED_WARNING', '�������: � ��������� ��������� �� ������ �� ������� ��� Shopgate ����� � ���������� ���� ��������� �� �����������.');

define(
'ENTRY_ORDER_SHIPPING_BLOCKED_IGNORED_WARNING',
    '�������: � ���������� ���������� �� "�����������" ��� Shopgate ������ �� Shopgate �������� ��������� "'
    . ENTRY_SHIPPING_BLOCKED_COMMENT_TEXT . '"!'
);

define('ENTRY_PAYMENT_METHOD_CHANGED', '� ������� ������� ���� ������� �� "%s" (from "%s").');

define('ENTRY_PAYMENT_SHOPGATE', 'Shopgate');

define('ENTRY_PAYMENT_PREPAY', 'Prepay');
define('ENTRY_PAYMENT_PREPAY_PAYMENT_PURPOSE', '� ������� ���� ����� ������� �� ��������� �� �������� ���� ���� ��������� ��� ���������� ��������������� ��� �������� ���� ��������:');
define('ENTRY_PAYMENT_MAPPED', "μέθοδο πληρωμής '%s' αντικαθίσταται από '%s'");

define('ENTRY_PAYMENT_ELV', '������');
define('ENTRY_PAYMENT_ELV_INFOTEXT', '�������� ������� �� ���� ��� ��� �������� ��������� ����������:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_OWNER', '������� ���������� �����������:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_NUMBER', '������� ���������� �����������:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_CODE', '������� ��������:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_NAME', '����� ��������:');

define('ENTRY_PAYMENT_CASH_ON_DELIVERY', '������������');

define('ENTRY_PAYMENT_SHOPGATE_GENERIC', 'mobile_payment');

define('ENTRY_SUB_TOTAL', '��������� (ex):');
define('ENTRY_SHIPPING', '��������:');
define('ENTRY_PAYMENT', '�������:');
define('ENTRY_TAX', '����� (%s%%):');
define('ENTRY_TOTAL', '������:');

define('ENTRY_ORDER_MARKED_AS_SHIPPED', '� ���������� ���� ��������� "�����������" ��� Shopgate.');
define('ENTRY_ORDER_MARKED_AS_CANCELED', 'Η διάταξη αυτή χαρακτηρίζεται ως ακυρώθηκε στο Shopgate');
define('ENTRY_SHOPGATE_MODULE_ERROR', '������������� ������ ���� �� ���������� ���� Shopgate module.');
define('ENTRY_SHOPGATE_UNKNOWN_ERROR', '������������� ������� ������.');

define('ENTRY_ERRORS_EXIST', '�������������� ��� � ����������� ����������:');
define('ENTRY_PRODUCT_NOT_FOUND', '�� ������ �� ��� ������ #PRODUCTS_ID# �� ������� �� ������ ��� ���� ���������!');

### Item Export ###
define('ENTRY_AVAILABLE_TEXT_AVAILABLE', '�������������');
define('ENTRY_AVAILABLE_TEXT_AVAILABLE_SHORTLY', '���� �� ����� �� ����������� ��� �� ��������� ����� ����� ���������.');
define('ENTRY_AVAILABLE_TEXT_NOT_AVAILABLE', '�� ���������');
define('ENTRY_AVAILABLE_TEXT_AVAILABLE_ON_DATE', '��������� ���� #DATE#');

define("MODULE_PAYMENT_SHOPGATE_DELIVERY_ADDRESS_CUSTOM_FIELDS", 'custom delivery field(s)');
define("MODULE_PAYMENT_SHOPGATE_INVOICE_ADDRESS_CUSTOM_FIELDS", 'custom invoice field(s)');
define("MODULE_PAYMENT_SHOPGATE_ORDER_CUSTOM_FIELDS", 'custom order field(s)');
