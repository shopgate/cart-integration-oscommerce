# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]


## [2.9.37] - 2018-02-06
### Changed
- migrated Shopgate integration for osCommerce to GitHub
- uses Shopgate Cart Integartion SDK version 2.9.71
- introduced new sub-folder "data" for cache, configuration, logs and temporary files; check permissions (0777) please

## 2.9.36
- uses Shopgate Library 2.9.65
- fixed compatibility problems with PHP version 5.2

## 2.9.35
- changed Plugin::getShipping signature to protected

## 2.9.34
- fix for missing class when order status is updated

## 2.9.33
- added dependency injection into the framework
- uses Shopgate Library 2.9.58

## 2.9.32
- fixed small php version compatibility issue in shipping helper

## 2.9.31
- fixed check_cart Fedex (and other multi-quote modules) shipping export
- added a new constant to check for product image location on export

## 2.9.30
- fixed compatibility issues with USPS module

## 2.9.29
- fixed issues with missing language file warnings on checkout

## 2.9.28
- improved compatibility with shipping modules
- fixed a bug in customer registration, if no state is given
- fixed a bug in customer registration, customer info was not saved
- fixes an issue with the export of invalid tax rates
- fixed issue with import of orders containing different customizations of the same product

## 2.9.27
- fixed billing last name when saving to orders table on add_order requests

## 2.9.26
- fixed issue with net prices in item export
- fixed issue with product input option UID's in item export
- fixed zone id & country id mapping to customer address table

## 2.9.25
- fixed compatibility issues for SwissCart
- fixed order import for orders with with Shopgate coupons
- uses Shopgate Library 2.9.26
- now export items without tax class
- fixed register_customer zone "too many results loaded" exception

## 2.9.24
- fixed export of additional product images in swisscart
- fixed customer registration with empty address "state" field

## 2.9.23
- added sorting products alphabetically (for swisscart: order index)

## 2.9.22
- fixed bug in item export with missing products

## 2.9.21
- fixed encoding issue with special characters in orders

## 2.9.20
- fixed a bug in the email notification of customers when importing an order

## 2.9.19
- added configuration to define display names for payment methods on order import
- fixed tax export

## 2.9.18
- updated boot strap for Shopgate classes
- restructured model system to separate models per database table

## 2.9.17
- added possibility for product redirect
- fixed street_2 field saving and retrieving in get/register_customer calls
- fixed date of birth field saving and retrieving in get/register_customer calls
- restructured format of payment infos in order history entries

## 2.9.16
- fixed issues with cart check on empty cart
- added order synchronization (library function get_orders)

## 2.9.15
- replaced table names by their global constant in SQL requests
- fixed issues while stock check on product detail page and cart validation

## 2.9.14
- The line feeds inside the description are not converted to br-tags anymore

## 2.9.13
- the manufacturer id will be set correctly now
- uses Shopgate Library 2.9.20

## 2.9.12
- fixed bug in XML export of input fields
- fixed a bug in converting the time zone
- fixed encoding issue on creating new user accounts

## 2.9.11
- fixed a bug in converting the time zone
- fixed a bug in order import
- fixed a bug in product export

## 2.9.10
- the date of order creation at Shopgate will be converted into the time zone configured in the server

## 2.9.9
- fixed a bug in getting shipping methods without tax classes in the cart synchronization
- bug in setting the options to products fixed for xml export

## 2.9.8
- fixed a bug in exporting stock status

## 2.9.7
- fixed issue on order status update
- improved cron methods
- support for Version osC 2.2-MS2
- bug in setting the right zones while checking out shipping methods fixed
- remove not needed Shopgate Links on Plugin configuration page
- set default tax zone where the shop is located
- link to the Shopgate-Wiki adapted

## 2.9.6
- eliminated problems exporting products with variations

## 2.9.5
- removed setting for the default redirect from the plugin configuration
- fixed a bug in adding payment method information to the order history
- fixed a bug in the real-time shipping rate calculation

## 2.9.4
- uses Shopgate Library 2.9.6
- removed unused product export field "internal_order_info"
- support export of SEO category urls

## 2.9.3
- change link to installation manual
- fix tax class in product xml export
- improved tax rate description export

## 2.9.2
- changed Shopgate menu links
- bug in update script fixed

## 2.9.1
- order can be cancelled / partially canceled (without shipping costs) now
- bug in adding status to orders into database fixed
- xml export for products, categories and reviews added

## 2.9.0
- the use of an function removed which is only in new php versions available
- uses Shopgate Library 2.9.3

## 2.8.5
- values of custom input fields (invoice/delivery address, order) will be added to the order history during importing an order

## 2.8.4
- only one coupon can be valid now

## 2.8.3
- Shopgate Models implemented

## 2.8.2
- fixed a bug in exporting special characters
- uses Shopgate Library 2.8.10

## 2.8.1
- fixed a bug in exporting tax settings

## 2.8.0
- Admin language will be used for order status text now
- now its possible to export the tax rules
- uses Shopgate Library 2.8.2

## 2.7.0
- shipping text will be used from Shopgate shipping information
- uses Shopgate Library 2.7.0

## 2.6.9
- shopgate config variable set
- added condig ISO-8859-2

## 2.6.8
- shipping methods will be validated now

## 2.6.7
- wrong named variable changed

## 2.6.6
- bug in shipping price calculation fixed

## 2.6.5
- shipping price will now be exported as net

## 2.6.4
- coupon validation bug fixed

## 2.6.3
- shipping methods will be exported correctly

## 2.6.2
- bug in check_cart function fixed

## 2.6.1
- Shipping methods can be selected
- Shopgate Plugin supports Oscommerce-Plugins ot_discount_coupon

## 2.6.0
- added a comment for shipping methods to the order
- uses Shopgate Library 2.6.7

## 2.5.5
- workaround for traling slash in shop constants

## 2.5.4
- bug in request Shopgate plugin properties fixed

## 2.5.3
- uses Shopgate Library 2.5.6
- plugin ping function extended

## 2.5.2
- bug in Shopgate configuration file fixed

## 2.5.1
- uses Shopgate Library 2.5.5
- request Shopgate plugin properties

## 2.5.0
- uses Shopgate Library 2.5.2
- bug fixed in Plugin installation

## 2.4.9
- plugin installation optimized

## 2.4.8
- fixed issue in export of products without a tax class

## 2.4.7
- added date_long and date_short to ShopgateWrapper class

## 2.4.6
- problem with Line Feeds in polish language files fixed

## 2.4.5
- uses Shopgate Library 2.4.12

## 2.4.4
- added head comment (license) into plugin files

## 2.4.3
- Uses Library V## 2.4.6
- function register_customer implemented

## 2.4.2
- additional language files are now included in the package

## 2.4.1
- added polish language support.

## 2.4.0
- uses Shopgate Library 2.4.0
- fixed issue with PHP-Warning in class ShopgateConfig
- fixed issue with mysqli version

## 2.3.3
- fixed product export issue with weight for USA
- uses Shopgate Library 2.3.8

## 2.3.2
- fixed encoding issues on exporting products, categories and reviews when using ISO encodings different from ISO-8859-1/15

## 2.3.1
- a bug has been fixed that happened to make the selection field for the tax zone unavailable for the "non US" module type. Instead it was visible for the "US" module type.

## 2.3.0
- Only home page, product detail pages and category pages are always redirected to the mobile web site from now on. There's a new setting for specifying whether or not other pages should also be redirected.
- The Shopgate OsCommerce Plugin default version has been merged with the USA version. The Version type is mostly detected automatically while installing the payment module and can be changed in the plugin settings
- uses Shopgate Library 2.3.2

## 2.1.16
- a problem has been fixed, where the shopgate-menu on the admin page did not work properly, when the session id is added to all links
- a problem has been solved, that happened to cause warnings while csv exports and order imports on servers that has set its error level to strict
- uses Shopgate Library 2.1.29

## 2.1.15
- fixed an issue that caused to appear error messages without really being an error, while updating orders

## 2.1.14
- moved functionality for handling of global and language dependend configurations to Shopgate Library
- fixed a bug in saving of log files
- The order import does not abort anymore on usage of the mySQL "strict mode"
- Additional export encodings can be directly edited in the configuration file and will be detected on the settings page
- There is an additional encoding ISO-8859-7 available on the settings page for greek shops
- fixed an issue where the shopgate payment module could not be installed properly
- uses Shopgate Library 2.1.26

## 2.1.13
- the customers data while importing orders is now taken from the shop customer data, instead of the customer data, given by the addOrder request
- the missing optional additional address data is now added to the street if set, when importing orders and customers
- fixed a bug in the export of product images from shops that reside in a sub-directory

## 2.1.12
- better diagnostics on errors during the export functions
- fixed bug in the logging process

## 2.1.11
- fixed a bug in the administration section

## 2.1.10
- fixed issue added constants
- on loading the admin page there is no longer reported a false error message

## 2.1.9
- fixed bug on product redirect
- fixed wrong translation strings in backend

## 2.1.8
- osCommerce version 2.2-LC does not support reviews, so they are not tried to export any longer
- supports language based configuration of multiple Shopgagte shops / marketplaces
- JavaScript header is included into the HTML <head> tag
- <link rel="alternate" ...> is included into the HTML <head> tag
- reworked settings page
- uses Shopgate Library 2.1.23

## 2.1.7
- Installing the Shopgate-Payment-Module should no longer cause errors on some osCommerce versions
- Added support for osCommerce version 2.2-MS2-CVS
- uses Shopgate-Library version 2.1.18

## 2.1.6
- the default charset is no longer set while creating the orders_shopgate_order table, because there seems to be a problem with some providers, while using this functionality
- mysql selects using join are now called explicit for cases where the keyword "JOIN" can not be used alone
- prices while importing orders are now calculated correctly when the currency exchange rate is set
- database is now checked if an older version (1.x.x) was installed before and is then updated if so
- tax_percent field is now limited to two decimal places
- the language in the config file must exist, or ortherwise the selection field is empty. Choosing the empty field for saving is not possible

## 2.1.5
- cron functionality completely refactored and fixed some minor bugs.

## 2.1.4
- fixed issue at cron(set_order_shipping_completed).

## 2.1.3
- the date is now set correctly on importing reviews.
- when importing categories, the sort order index is now calculated to display categories in the right order.
- on exporting items on databases with more than four pictures in a swisscart shop, all item-images can now be exported.
- the ping action now gives additional information about the version of the shoppingsystem.

## 2.1.2
- orders where the customer did not login via Shopgate-Connect are now imported using a guest account.
- resolved an issue where the tax was not calculated right, displaying the wrong total amount for the order.
- uses Shopgate-Library version 2.1.17

## 2.1.1
- can be integrated into swisscart v4.0 and supports up to four product images. Also the swisscart highlights are exported.
- uses Shopgate-Library version 2.1.12

## 2.1.0
- new plugin version released

[Unreleased]: https://github.com/shopgate/cart-integration-oscommerce/compare/2.9.37...HEAD
[2.9.37]: https://github.com/shopgate/cart-integration-oscommerce/tree/2.9.37

