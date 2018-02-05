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
define('SHOPGATE_CONFIG_EXTENDED_ENCODING', 'Kodowanie');
define('SHOPGATE_CONFIG_EXTENDED_ENCODING_DESCRIPTION', 'Wybierz kodowanie swojego sklepu. Zazwyczaj "%s" dla %s.');
define('SHOPGATE_CONFIG_WIKI_LINK', 'http://wiki.shopgate.com/OsCommerce/pl');

### Menu ###
define('BOX_SHOPGATE', 'Shopgate');
define('BOX_SHOPGATE_INFO', 'O Shopgate');
define('BOX_SHOPGATE_HELP', 'O instalacji');
define('BOX_SHOPGATE_REGISTER', 'Rejestracja');
define('BOX_SHOPGATE_CONFIG', 'Ustawienia');
define('BOX_SHOPGATE_MERCHANT', 'Shopgate login');

### Links ###
define('SHOPGATE_LINK_HOME', 'http://www.shopgate.com/pl');

### Storno ###
define("SHOPGATE_ORDER_NOT_FOUND", "Podana kolejność znaleziono ani w Shopgate nadal w systemie sklepu.");

### Configuration ###
define('SHOPGATE_CONFIG_TITLE', 'SHOPGATE');
define('SHOPGATE_CONFIG_ERROR', 'BŁĄD:');
define('SHOPGATE_CONFIG_ERROR_SAVING', 'Błąd przy zapisie konfiguracji. ');
define('SHOPGATE_CONFIG_ERROR_LOADING', 'Błąd przy wczytywaniu konfiguracji. ');
define('SHOPGATE_CONFIG_ERROR_READ_WRITE', 'Sprawdź pozwolenie (777) dla folderu &quot;data/configuration&quot; wtyczki Shopgate.');
define('SHOPGATE_CONFIG_ERROR_INVALID_VALUE', 'Sprawdź wprowadzone wartości w polach:');
define('SHOPGATE_CONFIG_ERROR_DUPLICATE_SHOP_NUMBERS', 'Istnieje więcej niż jedna konfiguracja z tym samym numerem sklepu. Może to powodować błędy!');
define('SHOPGATE_CONFIG_INFO_MULTIPLE_CONFIGURATIONS', 'Konfiguracja dla różnych rynków jest gotowa.');
define('SHOPGATE_CONFIG_SAVE', 'ZAPISZ');
define('SHOPGATE_CONFIG_GLOBAL_CONFIGURATION', 'Globalna konfiguracja');
define('SHOPGATE_CONFIG_USE_GLOBAL_CONFIG', 'Użyj globalnej konfiguracji dla tego sklepu.');
define('SHOPGATE_CONFIG_MULTIPLE_SHOPS_BUTTON', 'Ustaw wiele rynków Shopgate');
define(
'SHOPGATE_CONFIG_LANGUAGE_SELECTION',
    'W Shopgate dla każdej waluty i języka potrzebujesz innego sklepu mobilnego, sprawdź jaki języki możesz wybrać.' .
    'marketplaces. Wybierz język i wprowadź dane.' .
    'globalna konfiguracja będzie użyta.'
);

### Connection Settings ###
define('SHOPGATE_CONFIG_CONNECTION_SETTINGS', 'Ustawienia łączności');

define('SHOPGATE_CONFIG_PLUGIN_TYPE', 'Typ modułu');
define('SHOPGATE_CONFIG_PLUGIN_TYPE_NON_US', 'Nie US');
define('SHOPGATE_CONFIG_PLUGIN_TYPE_US', 'US');
define(
'SHOPGATE_CONFIG_PLUGIN_TYPE_DESCRIPTION',
'Wybierz Nie US dla krajów takich jak Austria, Niemcy, Szwajcaria, US jest dla krajów z bardziej skomplikowanym systemem podatkowym.'
);

define('SHOPGATE_CONFIG_CUSTOMER_NUMBER', 'Numer klienta');
define('SHOPGATE_CONFIG_CUSTOMER_NUMBER_DESCRIPTION', 'Numer klienta znajdziesz w zakładce &quot;Integracja&quot; w panelu Shopgate.');

define('SHOPGATE_CONFIG_SHOP_NUMBER', 'Numer sklepu');
define('SHOPGATE_CONFIG_SHOP_NUMBER_DESCRIPTION', 'Numer sklepu znajdziesz w zakładce &quot;Integracja&quot; w panelu Shopgate.');

define('SHOPGATE_CONFIG_APIKEY', 'Klucz API');
define('SHOPGATE_CONFIG_APIKEY_DESCRIPTION', 'Klucz API znajdziesz w zakładce &quot;Integracja&quot; w panelu Shopgate.');

### Mobile Redirect ###
define('SHOPGATE_CONFIG_MOBILE_REDIRECT_SETTINGS', 'Przekierowanie mobilne');

define('SHOPGATE_CONFIG_ALIAS', 'Alias');
define('SHOPGATE_CONFIG_ALIAS_DESCRIPTION', 'Alias znajdziesz w zakładce &quot;Integracja&quot; w panelu Shopgate.');

define('SHOPGATE_CONFIG_CNAME', 'Domena mobilna (CNAME) z  http://');
define(
'SHOPGATE_CONFIG_CNAME_DESCRIPTION',
    'Wprowadź domenę mobilną (CNAME) dla wersji mobilnej sklepu. Należy ją także wprowadzić w zakładce &quot;Integracja&quot; w panelu Shopgate '
    .
    'po aktywowaniu tej opcji w &quot;Ustawieniach&quot; &equals;&gt; &quot;Strona mobilna&quot;.'
);

define('SHOPGATE_CONFIG_REDIRECT_LANGUAGES', 'Język w wersji mobilnej');
define(
'SHOPGATE_CONFIG_REDIRECT_LANGUAGES_DESCRIPTION',
'Wybierz, do której wersji językowej sklepu ma być włączone przekierowanie na stronę mobilną, aby wybrać więcej niż jeden przytrzymaj CTRL.'
);

### Export ###
define('SHOPGATE_CONFIG_EXPORT_SETTINGS', 'Eksportowanie kategorii i produktów');

define('SHOPGATE_CONFIG_LANGUAGE', 'Język');
define('SHOPGATE_CONFIG_LANGUAGE_DESCRIPTION', 'Wybierz język, w którym mają być eksportowane .');

define('SHOPGATE_CONFIG_EXTENDED_CURRENCY', 'Waluta');
define('SHOPGATE_CONFIG_EXTENDED_CURRENCY_DESCRIPTION', 'Wybierz walutę w jakiej produkty mają być eksportowane.');

define('SHOPGATE_CONFIG_EXTENDED_COUNTRY', 'Kraj');
define('SHOPGATE_CONFIG_EXTENDED_COUNTRY_DESCRIPTION', 'Wybierz kraj dla którego powinny być eksportowane produkty');

define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE', 'Strefa podatkowa');
define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE_DESCRIPTION', 'Wybierz odpowiedni podatek.');

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER', 'Odwróć sortowanie kategorii');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_ON', 'Tak');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_OFF', 'Nie');
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_DESCRIPTION',
'Wybierz „Tak“ jeżeli kategorie wyświetlają się w odwrotnej kolejności na stronie mobilnej.'
);

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER', 'Odwróć sortowanie kategorii');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_ON', 'Tak');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_OFF', 'Nie');
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_DESCRIPTION',
'Wybierz „Tak“ jeżeli produkty wyświetlają się w odwrotnej kolejności na stronie mobilnej.'
);

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP', 'Grupa cenowa dla Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_DESCRIPTION', 'Wybierz odpowiednią grupę cenową dla Shopgate (cena z tej grupy będzie eksportowana do Shopgate).');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_OFF', '-- Deactivated --');

### Orders Import ###
define('SHOPGATE_CONFIG_ORDER_IMPORT_SETTINGS', 'Importowanie zamówień');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP', 'Grupa klientów');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP_DESCRIPTION', 'Wybierz grupę klientów Shopgate (grupę do której wszyscy klienci goście będą przypisywani przy imporcie zamówienia).');

define('SHOPGATE_CONFIG_EXTENDED_SHIPPING', 'Metoda wysyłki');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_DESCRIPTION', 'Wybierz metodę płatności dla importowanych zamówień. Pomoże to w obliczeniu podatku od dostawy.');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_NO_SELECTION', '—nie wybrano --');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED', 'Wysyłka zatwierdzona');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED_DESCRIPTION', 'Wybierz status dla zamówień nie blokowanych przez Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED', 'Wysyłka zablokowana');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED_DESCRIPTION', 'Wybierz status dla zamówień blokowanych przez Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT', 'Dostarczono');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT_DESCRIPTION', 'Wybierz status dla zamówień wysłanych.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED', 'Anulowane');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_NOT_SET', '- Status nieustawiony -');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_DESCRIPTION', 'Wybierz status dla zamówień anulowanych.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING', 'Anuluj wysyłka');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_ON', 'Tak');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_OFF', 'Nie');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_DESCRIPTION', 'Jeśli tak jest zaznaczone , jak w do przerwania o wysyłce zamówienia jest odwołany.');

### System Settings ###
define('SHOPGATE_CONFIG_SYSTEM_SETTINGS', 'Ustawienia');

define('SHOPGATE_CONFIG_SERVER_TYPE', 'Serwer');
define('SHOPGATE_CONFIG_SERVER_TYPE_LIVE', 'Produkcyjny');
define('SHOPGATE_CONFIG_SERVER_TYPE_PG', 'Testowy');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM', 'Inny');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL', 'Inny serwer url dla Shopgate');
define('SHOPGATE_CONFIG_SERVER_TYPE_DESCRIPTION', 'Wybierz serwer, z którym ma łączyć się Shopgate.');

### Orders overview ###
define('ENTRY_IS_TEST_ORDER_COMMENT_TEXT', '### ZAMÓWIENIE TESTOWE ###');

define('ENTRY_ORDER_ADDED_BY_SHOPGATE_COMMENT_TEXT', 'Zamówienie z Shopgate.');

define('ENTRY_PAYMENT_SHOPGATE_ORDER_NUMBER_COMMENT_TEXT', 'Numer zamówienia Shopgate:');
define('ENTRY_PAYMENT_TRANSACTION_NUMBER_COMMENT_TEXT', 'Numer transakcji płatniczej:');
define('ENTRY_SHIPPING_METHOD_COMMENT_TEXT', 'Sposób wysyłki:');


define('ENTRY_NEW_PAYMENT_STATUS_IS_PAID_COMMENT_TEXT', 'Status zamówienia zmieniony przez Shopgate: Płatność otrzymana');
define('ENTRY_NEW_PAYMENT_STATUS_IS_NOT_PAID_COMMENT_TEXT', 'Status zamówienia zmieniony przez Shopgate: Płatność jeszcze NIE wpłynęła');

define('ENTRY_SHIPPING_BLOCKED_COMMENT_TEXT', 'Info: Zamówienie zablokowane przez Shopgate!');
define('ENTRY_SHIPPING_NOT_BLOCKED_COMMENT_TEXT', 'Info: Zamówienie zatwierdzone przez Shopgate!');
define('ENTRY_NEW_SHIPPING_STATUS_SHIPPING_BLOCKED_COMMENT_TEXT', 'Status zamówienia zmieniony przez Shopgate: Zamówienie zablokowane!');
define('ENTRY_NEW_SHIPPING_STATUS_SHIPPING_APPROVED_COMMENT_TEXT', 'Status zamówienia zmieniony przez Shopgate: Zamówienie zostało odblokowane!');

define('ENTRY_ALREADY_SHIPPED_WARNING', 'UWAGA: Status zamówienia nie mógł zostać zmieniony w Shopgate, gdyż zamówienie zostało już oznaczone jako wysłane.');

define(
'ENTRY_ORDER_SHIPPING_BLOCKED_IGNORED_WARNING',
    'UWAGA: Zamówienie zostało oznaczone jako "wysłane" w Shopgate"' . ENTRY_SHIPPING_BLOCKED_COMMENT_TEXT . '"!'
);

define('ENTRY_PAYMENT_METHOD_CHANGED', 'Metoda płatności została zmieniona z "%s" (na "%s").');

define('ENTRY_PAYMENT_SHOPGATE', 'Shopgate');

define('ENTRY_PAYMENT_UPDATED', "Shopgate: informacja o płatności będzie aktualizowana: \n\n");

define('ENTRY_PAYMENT_PREPAY', 'Przedpłata');
define('ENTRY_PAYMENT_PREPAY_PAYMENT_PURPOSE', 'Klient otrzyma informację o numerze konta, na które powinien wpłacić należność:');
define('ENTRY_PAYMENT_MAPPED', "Sposób płatności '%s' zastąpiona przez '%s'");

define('ENTRY_PAYMENT_ELV', 'Obciążenie');
define('ENTRY_PAYMENT_ELV_INFOTEXT', 'Proszę obciążyć odpowiednią kwotą następujące konto bankowe:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_OWNER', 'Właściciel konta bankowego:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_NUMBER', 'Numer konta bankowego:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_CODE', 'Kod banku:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_NAME', 'Nazwa banku:');

define('ENTRY_PAYMENT_CASH_ON_DELIVERY', 'Pobranie');

define('ENTRY_PAYMENT_SHOPGATE_GENERIC', 'Płatność mobilna');

define('ENTRY_SUB_TOTAL', 'Suma');
define('ENTRY_SHIPPING', 'Dostawa:');
define('ENTRY_PAYMENT', 'Płatność:');
define('ENTRY_TAX', 'Podatek(%s%%):');
define('ENTRY_TOTAL', 'Razem:');
define('ENTRY_COUPON', 'Kupon zniżkowy %s został już wykorzystany:');

define('ENTRY_ORDER_MARKED_AS_SHIPPED', 'Zamówienie zostało oznaczone jako wysłane w Shopgate.');
define('ENTRY_ORDER_MARKED_AS_CANCELED', 'Zamówienie zostało oznaczone jako odwołany w Shopgate');
define('ENTRY_SHOPGATE_MODULE_ERROR', 'Wystąpił błąd podczas.');
define('ENTRY_SHOPGATE_UNKNOWN_ERROR', 'Nieznany błąd.');

define('ENTRY_ERRORS_EXIST', 'Wystąpił błąd/błędy:');
define('ENTRY_PRODUCT_NOT_FOUND', 'Produkt o numerze #PRODUCTS_ID# nie został znaleziony!');

### Item Export ###
define('ENTRY_AVAILABLE_TEXT_AVAILABLE', 'Dostępny');
define('ENTRY_AVAILABLE_TEXT_AVAILABLE_SHORTLY', 'Na zamówienie.');
define('ENTRY_AVAILABLE_TEXT_NOT_AVAILABLE', 'Niedostępny');
define('ENTRY_AVAILABLE_TEXT_AVAILABLE_ON_DATE', 'Dostępny od #DATE#');

define("MODULE_PAYMENT_SHOPGATE_DELIVERY_ADDRESS_CUSTOM_FIELDS", 'niestandardowe pole dostawy');
define("MODULE_PAYMENT_SHOPGATE_INVOICE_ADDRESS_CUSTOM_FIELDS", 'niestandardowe pole rachunku');
define("MODULE_PAYMENT_SHOPGATE_ORDER_CUSTOM_FIELDS", 'niestandardowe pole zamówienia');
