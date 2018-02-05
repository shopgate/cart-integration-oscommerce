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
define('SHOPGATE_CONFIG_EXTENDED_ENCODING', 'Encoding des Shopsystems');
define(
'SHOPGATE_CONFIG_EXTENDED_ENCODING_DESCRIPTION', ##### osCommerce LINE-DIFFERENCE #####
'W&auml;hlen Sie das Encoding Ihres Shopsystems. &Uuml;blicherweise ist "%s" f&uuml;r %s zu w&auml;hlen.'
);
define('SHOPGATE_CONFIG_WIKI_LINK', 'http://wiki.shopgate.com/OsCommerce/de');

### Menu ###
define('BOX_SHOPGATE', 'Shopgate');
define('BOX_SHOPGATE_INFO', 'Was ist Shopgate');
define('BOX_SHOPGATE_HELP', 'Installationshilfe');
define('BOX_SHOPGATE_REGISTER', 'Registrierung');
define('BOX_SHOPGATE_CONFIG', 'Einstellungen');
define('BOX_SHOPGATE_MERCHANT', 'Shopgate-Login');

### Links ###
define('SHOPGATE_LINK_HOME', 'https://www.shopgate.com/de/');

### Storno ###
define("SHOPGATE_ORDER_NOT_FOUND", "Die angegebene Bestellung wurde weder bei Shopgate noch im Shopsystem gefunden.");

### Konfiguration ###
define('SHOPGATE_CONFIG_TITLE', 'SHOPGATE');
define('SHOPGATE_CONFIG_ERROR', 'FEHLER:');
define('SHOPGATE_CONFIG_ERROR_SAVING', 'Fehler beim Speichern der Konfiguration. ');
define('SHOPGATE_CONFIG_ERROR_LOADING', 'Fehler beim Laden der Konfiguration. ');
define('SHOPGATE_CONFIG_ERROR_READ_WRITE', 'Bitte überprüfen Sie die Schreibrechte (777) für den Ordner "data/configuration" des Shopgate-Plugins.');
define('SHOPGATE_CONFIG_ERROR_INVALID_VALUE', 'Bitte überprüfen Sie ihre Eingaben in den folgenden Feldern: ');
define('SHOPGATE_CONFIG_ERROR_DUPLICATE_SHOP_NUMBERS', 'Es existieren mehrere Konfigurationen mit der gleichen Shop-Nummer. Dies kann zu erheblichen Problemen führen!');
define('SHOPGATE_CONFIG_INFO_MULTIPLE_CONFIGURATIONS', 'Es existieren Konfigurationen f&uuml;r mehrere Marktpl&auml;tze.');
define('SHOPGATE_CONFIG_SAVE', 'Speichern');
define('SHOPGATE_CONFIG_GLOBAL_CONFIGURATION', 'Globale Konfiguration');
define('SHOPGATE_CONFIG_USE_GLOBAL_CONFIG', 'F&uuml;r diese Sprache die globale Konfiguration nutzen.');
define('SHOPGATE_CONFIG_MULTIPLE_SHOPS_BUTTON', 'Mehrere Shopgate-Marktpl&auml;tze einrichten');
define(
'SHOPGATE_CONFIG_LANGUAGE_SELECTION',
    'Bei Shopgate ben&ouml;tigen Sie pro Marktplatz einen Shop, der auf eine Sprache und eine W&auml;hrung festgelegt ist. Hier haben Sie die M&ouml;glichkeit, Ihre konfigurierten '
    .
    'Sprachen mit Ihren Shopgate-Shops auf unterschiedlichen Marktpl&auml;tzen zu verbinden. W&auml;hlen Sie eine Sprache und tragen Sie die Zugangsdaten zu Ihrem Shopgate-Shop auf '
    .
    'dem entsprechenden Marktplatz ein. Wenn Sie f&uuml;r eine Sprache keinen eigenen Shop bei Shopgate haben, wird daf&uuml;r die "Globale Konfiguration" genutzt.'
);

### Verbindungseinstellungen ###
define('SHOPGATE_CONFIG_CONNECTION_SETTINGS', 'Verbindungseinstellungen');

define('SHOPGATE_CONFIG_PLUGIN_TYPE', 'Modultyp');
define('SHOPGATE_CONFIG_PLUGIN_TYPE_NON_US', 'Nicht US');
define('SHOPGATE_CONFIG_PLUGIN_TYPE_US', 'US');
define(
'SHOPGATE_CONFIG_PLUGIN_TYPE_DESCRIPTION',
'W&auml;hlen Sie hier den Modultyp aus. Dieser ist abh&auml;ngig vom Shopgate Marktplatz f&uuml;r den Sie sich registriert haben. F&uuml;r L&auml;nder wie Deutschland, &Ouml;sterreich oder Schweiz wird &uuml;blicherweise "Nicht US" verwendet, w&auml;hrend Shops aus L&auml;ndern mit komplexeren Steuerberechnungen "US" verwenden.'
);

define('SHOPGATE_CONFIG_CUSTOMER_NUMBER', 'Kundennummer');
define('SHOPGATE_CONFIG_CUSTOMER_NUMBER_DESCRIPTION', 'Tragen Sie hier Ihre Kundennummer ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_SHOP_NUMBER', 'Shopnummer');
define('SHOPGATE_CONFIG_SHOP_NUMBER_DESCRIPTION', 'Tragen Sie hier die Shopnummer Ihres Shops ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_APIKEY', 'API-Key');
define('SHOPGATE_CONFIG_APIKEY_DESCRIPTION', 'Tragen Sie hier den API-Key Ihres Shops ein. Sie finden diesen im Tab &quot;Integration&quot; Ihres Shops.');

### Mobile Weiterleitung ###
define('SHOPGATE_CONFIG_MOBILE_REDIRECT_SETTINGS', 'Mobile Weiterleitung');

define('SHOPGATE_CONFIG_ALIAS', 'Shop-Alias');
define('SHOPGATE_CONFIG_ALIAS_DESCRIPTION', 'Tragen Sie hier den Alias Ihres Shops ein. Sie finden diesen im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_CNAME', 'Eigene URL zur mobilen Webseite (mit http://)');
define(
'SHOPGATE_CONFIG_CNAME_DESCRIPTION',
    'Tragen Sie hier eine eigene (per CNAME definierte) URL zur mobilen Webseite Ihres Shops ein. Sie finden die URL im Tab &quot;Integration&quot; Ihres Shops, '
    .
    'nachdem Sie diese Option unter &quot;Einstellungen&quot; &equals;&gt; &quot;Mobile Webseite / Webapp&quot; aktiviert haben.'
);

define('SHOPGATE_CONFIG_REDIRECT_LANGUAGES', 'Weitergeleitete Sprachen');
define(
'SHOPGATE_CONFIG_REDIRECT_LANGUAGES_DESCRIPTION',
    'W&auml;hlen Sie die Sprachen aus, die auf diesen Shopgate-Shop weitergeleitet werden sollen. Es muss mindestens ' .
    'eine Sprache ausgew&auml;hlt werden. Halten Sie STRG gedr&uuml;ckt, um mehrere Eintr&auml;ge zu w&auml;hlen.'
);

### Export ###
define('SHOPGATE_CONFIG_EXPORT_SETTINGS', 'Kategorie- und Produktexport');

define('SHOPGATE_CONFIG_LANGUAGE', 'Sprache');
define('SHOPGATE_CONFIG_LANGUAGE_DESCRIPTION', 'W&auml;hlen Sie die Sprache, in der Kategorien und Produkte exportiert werden sollen.');

define('SHOPGATE_CONFIG_EXTENDED_CURRENCY', 'W&auml;hrung');
define('SHOPGATE_CONFIG_EXTENDED_CURRENCY_DESCRIPTION', 'W&auml;hlen Sie die W&auml;hrung f&uuml;r den Produktexport.');

define('SHOPGATE_CONFIG_EXTENDED_COUNTRY', 'Land');
define('SHOPGATE_CONFIG_EXTENDED_COUNTRY_DESCRIPTION', 'W&auml;hlen Sie das Land, f&uuml;r das Ihre Produkte und Kategorien exportiert werden sollen.');

define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE', 'Steuerzone f&uuml;r Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE_DESCRIPTION', 'Geben Sie die Steuerzone an, die f&uuml;r Shopgate g&uuml;ltig sein soll.');

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER', 'Kategorie-Reihenfolge umkehren');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_ON', 'Ja');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_OFF', 'Nein');
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_DESCRIPTION',
'W&auml;hlen Sie hier "Ja" aus, wenn die Sortierung Ihrer Kategorien in Ihrem mobilen Shop genau falsch herum ist.'
);

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER', 'Produkt-Reihenfolge umkehren');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_ON', 'Ja');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_OFF', 'Nein');
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_DESCRIPTION',
'W&auml;hlen Sie hier "Ja" aus, wenn die Sortierung Ihrer Produkte in Ihrem mobilen Shop genau falsch herum ist.'
);

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP', 'Preisgruppe f&uuml;r Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_DESCRIPTION', 'W&auml;hlen Sie die Preisgruppe, die f&uuml;r Shopgate gilt (bzw. die Kundengruppe, aus welcher die Preisinformationen beim Produktexport verwendet werden).');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_OFF', '-- Deaktiviert --');

### Bestellungsimport ###
define('SHOPGATE_CONFIG_ORDER_IMPORT_SETTINGS', 'Bestellungsimport');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP', 'Kundengruppe');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP_DESCRIPTION', 'W&auml;hlen Sie die Gruppe f&uuml;r Shopgate-Kunden (die Kundengruppe, unter welcher alle Gastkunden von Shopgate beim Bestellungsimport eingerichtet werden).');

define('SHOPGATE_CONFIG_EXTENDED_SHIPPING', 'Versandart');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_DESCRIPTION', 'W&auml;hlen Sie die Versandart f&uuml;r den Bestellungsimport. Diese wird f&uuml;r die Ausweisung der Steuern der Versandkosten genutzt, sofern eine Steuerklasse f&uuml;r die Versandart ausgew&auml;hlt ist.');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_NO_SELECTION', '-- keine Auswahl --');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED', 'Versand nicht blockiert');
define(
'SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED_DESCRIPTION',
'W&auml;hlen Sie den Status f&uuml;r Bestellungen, deren Versand bei Shopgate nicht blockiert ist.'
);

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED', 'Versand blockiert');
define(
'SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED_DESCRIPTION',
'W&auml;hlen Sie den Status f&uuml;r Bestellungen, deren Versand bei Shopgate blockiert ist.'
);

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT', 'Versendet');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT_DESCRIPTION', 'W&auml;hlen Sie den Status, mit dem Sie Bestellungen als &quot;versendet&quot; markieren.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED', 'Storniert');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_NOT_SET', '- Status nicht ausgew&auml;hlt -');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_DESCRIPTION', 'W&auml;hlen Sie den Status f&uuml;r stornierte Bestellungen.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING', 'Versand stornieren');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SEND_CONFIRMATION_MAIL', 'Sende Bestellbest&auml;tigungs-Email');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SEND_CONFIRMATION_MAIL_DESCRIPTION', 'Standard Email des Shopsystems. Manuelle &Auml;nderungen werden ignoriert.');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_ON', 'Ja');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_OFF', 'Nein');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_SHIPPING_DESCRIPTION', 'Wenn "ja" ausgew&auml;hlt ist, so wird bei der Stornierung einer Bestellung auch der Versand storniert.');

define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING', 'Anzeigenamen f&uuml;r Zahlungsweisen');
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_DESCRIPTION', "Individuelle Namen f&uuml;r Zahlungsweisen, die beim Bestellungsimport verwendet werden. Definiert durch '=' und getrennt durch ';'.<br/>(Beispiel: PREPAY=Vorkasse;SHOPGATE=Abwicklung durch Shopgate)<br/>");
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_LINK', 'https://support.shopgate.com/hc/de/articles/202798386-Anbindung-an-oscommerce#4.4');
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_LINK_DESCRIPTION', "Link zur Anleitung");

### Systemeinstellungen ###
define('SHOPGATE_CONFIG_SYSTEM_SETTINGS', 'Systemeinstellungen');

define('SHOPGATE_CONFIG_SERVER_TYPE', 'Shopgate Server');
define('SHOPGATE_CONFIG_SERVER_TYPE_LIVE', 'Live');
define('SHOPGATE_CONFIG_SERVER_TYPE_PG', 'Playground');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM', 'Custom');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL', 'Benutzerdefinierte URL zum Shopgate-Server');
define('SHOPGATE_CONFIG_SERVER_TYPE_DESCRIPTION', 'W&auml;hlen Sie hier die Server-Verbindung zu Shopgate aus.');


### Bestellungsübersicht ###
define('ENTRY_IS_TEST_ORDER_COMMENT_TEXT', '### DIES IST EINE TESTBESTELLUNG ###');

define('ENTRY_ORDER_ADDED_BY_SHOPGATE_COMMENT_TEXT', 'Bestellung durch Shopgate hinzugefügt.');

define('ENTRY_PAYMENT_SHOPGATE_ORDER_NUMBER_COMMENT_TEXT', 'Shopgate-Bestellnummer:');
define('ENTRY_PAYMENT_TRANSACTION_NUMBER_COMMENT_TEXT', 'Payment-Transaktionsnummer:');
define('ENTRY_SHIPPING_METHOD_COMMENT_TEXT', 'Versandmethode:');

define('ENTRY_NEW_PAYMENT_STATUS_IS_PAID_COMMENT_TEXT', 'Bestellstatus von Shopgate geändert: Zahlung erhalten');
define('ENTRY_NEW_PAYMENT_STATUS_IS_NOT_PAID_COMMENT_TEXT', 'Bestellstatus von Shopgate geändert: Zahlung noch nicht erhalten');

define('ENTRY_SHIPPING_BLOCKED_COMMENT_TEXT', 'Hinweis: Der Versand der Bestellung ist von Shopgate blockiert!');
define('ENTRY_SHIPPING_NOT_BLOCKED_COMMENT_TEXT', 'Hinweis: Der Versand der Bestellung ist von Shopgate nicht blockiert!');
define('ENTRY_NEW_SHIPPING_STATUS_SHIPPING_BLOCKED_COMMENT_TEXT', 'Bestellstatus von Shopgate geändert: Versand ist blockiert!');
define('ENTRY_NEW_SHIPPING_STATUS_SHIPPING_APPROVED_COMMENT_TEXT', 'Bestellstatus von Shopgate geändert: Versand ist nicht mehr blockiert!');

define('ENTRY_ALREADY_SHIPPED_WARNING', 'Achtung: Der Versandstatus konnte von Shopgate nicht geändert werden, da die Bestellung bereits als versendet markiert ist!');

define(
'ENTRY_ORDER_SHIPPING_BLOCKED_IGNORED_WARNING',
    'Achtung: Der Versandstatus der Bestellung ist auf "versendet" gesetzt, obwohl der Shopgate-Status für diese Bestellung noch auf "'
    . ENTRY_SHIPPING_BLOCKED_COMMENT_TEXT . '" gesetzt ist!'
);

define('ENTRY_PAYMENT_METHOD_CHANGED', 'Die Zahlungsart wurde von "%s" nach "%s" aktualisiert');

define('ENTRY_PAYMENT_SHOPGATE', 'Shopgate');

define('ENTRY_PAYMENT_UPDATED', "Shopgate: Zahlungsinformationen wurden aktualisiert: \n\n");

define('ENTRY_PAYMENT_PREPAY', 'Vorkasse');
define('ENTRY_PAYMENT_PREPAY_PAYMENT_PURPOSE', 'Der Kunde wurde angewiesen den kompletten Betrag auf Ihr Bankkonto unter folgendem Verwendungszweck zu überweisen:');
define('ENTRY_PAYMENT_MAPPED', "Zahlungsweise '%s' durch '%s' ersetzt");

define('ENTRY_PAYMENT_ELV', 'Lastschrift');
define('ENTRY_PAYMENT_ELV_INFOTEXT', 'Bitte buchen Sie den Betrag via Lastschrift von folgendem Bankkonto ab:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_OWNER', 'Kontoinhaber:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_ACCOUNT_NUMBER', 'Kontonummer:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_CODE', 'BLZ:');
define('ENTRY_PAYMENT_ELV_TEXT_BANK_NAME', 'Name der Bank:');

define('ENTRY_PAYMENT_CASH_ON_DELIVERY', 'Barzahlung bei Lieferung');

define('ENTRY_PAYMENT_SHOPGATE_GENERIC', 'mobile_payment');

define('ENTRY_SUB_TOTAL', 'Zwischensumme (exkl.):');
define('ENTRY_SHIPPING', 'Versandkosten:');
define('ENTRY_PAYMENT', 'Zahlungsart:');
define('ENTRY_TAX', 'Steuer (%s%%):');
define('ENTRY_TOTAL', 'Gesamtsumme:');
define('ENTRY_COUPON', 'Gutschein %s gültig:');

define('ENTRY_ORDER_MARKED_AS_SHIPPED', 'Die Bestellung wurde bei Shopgate als versendet markiert.');
define('ENTRY_ORDER_MARKED_AS_CANCELED', 'Die Bestellung wurde bei Shopgate als storniert markiert.');
define('ENTRY_SHOPGATE_MODULE_ERROR', 'Ein Fehler im Shopgate Modul ist aufgetreten.');
define('ENTRY_SHOPGATE_UNKNOWN_ERROR', 'Ein unbekannter Fehler ist aufgetreten.');

define('ENTRY_ERRORS_EXIST', 'Ein oder mehrere Probleme wurden festgestellt:');
define('ENTRY_PRODUCT_NOT_FOUND', 'Das Produkt mit der products_id #PRODUCTS_ID# konnte in der Datenbank nicht gefunden werden!');

### Item Export ###
define('ENTRY_AVAILABLE_TEXT_AVAILABLE', 'Verfügbar');
define('ENTRY_AVAILABLE_TEXT_AVAILABLE_SHORTLY', 'Dieser Artikel wird in kürze nachbestellt und wird versendet, sobald er wieder verfügbar ist.');
define('ENTRY_AVAILABLE_TEXT_NOT_AVAILABLE', 'Nicht verfügbar');
define('ENTRY_AVAILABLE_TEXT_AVAILABLE_ON_DATE', 'Verfügbar ab dem #DATE#');
define("MODULE_PAYMENT_SHOPGATE_DELIVERY_ADDRESS_CUSTOM_FIELDS", 'Benutzerdefinierte Eingabefelder zur Versandadresse');
define("MODULE_PAYMENT_SHOPGATE_INVOICE_ADDRESS_CUSTOM_FIELDS", 'Benutzerdefinierte Eingabefelder zur Rechnungadresse');
define("MODULE_PAYMENT_SHOPGATE_ORDER_CUSTOM_FIELDS", 'Benutzerdefinierte Eingabefelder zur Bestellung');
