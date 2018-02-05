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

 /**
 * @method int getProductsId()
 * @method $this setProductsId($id_int)
 * @method int getProductsQuantity()
 * @method $this setProductsQuantity($qty_int)
 * @method string getProductsModel()
 * @method $this setProductsModel($model_str) - something like a non-unique sku
 * @method string getPorductsImage()
 * @method $this setPorductsImage($path_str) - relative path
 * @method float getProductsPrice()
 * @method $this setProductsPrice($price_float) - 4 decimal points
 * @method string getProductsDateAdded()
 * @method $this setProductsDateAdded($date_str) - format DATETIME - 2015-08-18 19:07:13
 * @method string getProductsLastModified()
 * @method $this setProductsLastModified($date_str) - format DATETIME - 2015-08-18 19:07:13
 * @method string getProductsDateAvailable()
 * @method $this setProductsDateAvailable($date_str) - format DATETIME - 2015-08-18 19:07:13
 * @method float getProductsWeight()
 * @method $this setProductsWeight($weight_float)
 * @method int getProductsStatus()
 * @method $this setProductsStatus($status_int) - tinyint, 0 or 1
 * @method int getManufacturersId()
 * @method $this setManufacturersId($id_int) - main ID from the manufacturers table
 * @method int getProductsOrdered()
 * @method $this setProductsOrdered($qty_int) - provide amount ordered
 */
class Shopgate_Models_Products_Native extends Shopgate_Models_Abstract_Db_Collection
{
    protected $tableName = TABLE_PRODUCTS;
}
