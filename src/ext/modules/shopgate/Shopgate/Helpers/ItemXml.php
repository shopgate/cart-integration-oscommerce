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
class Shopgate_Helpers_ItemXml extends Shopgate_Helpers_Item
{
    public function setUid()
    {
        parent::setUid($this->item['products_id']);
    }

    public function setLastUpdate()
    {
        parent::setLastUpdate($this->buildLastUpdate($this->item['products_last_modified']));
    }

    public function setName()
    {
        parent::setName($this->buildName($this->item['products_name']));
    }

    public function setTaxPercent()
    {
        if (!isset($this->item['tax_rate'])) {
            $this->item['tax_rate'] = 0;
        }
        parent::setTaxPercent($this->buildTaxRate($this->item['tax_rate']));
    }

    public function setTaxClass()
    {
        parent::setTaxClass($this->buildTaxClass($this->item));
    }

    public function setCurrency()
    {
        parent::setCurrency($this->_currency);
    }

    public function setDescription()
    {
        parent::setDescription($this->buildDescription($this->item['products_description']));
    }

    public function setDeeplink()
    {
        parent::setDeeplink($this->buildDeeplink($this->item['products_id']));
    }

    public function setPrice()
    {
        $exportGrossPrice = defined('DISPLAY_PRICE_WITH_TAX') && DISPLAY_PRICE_WITH_TAX != 'false';
        $taxPercent       = $this->getTaxPercent();
        $priceModel = new Shopgate_Model_Catalog_Price();

        $specialPrice = tep_get_products_special_price($this->item['products_id']);
        if ($specialPrice) {
            if ($exportGrossPrice) {
                $specialPrice *= 1 + ($taxPercent / 100);
            }
            $specialPrice *= $this->_exchangeRate;
            $specialPrice = $this->formatPriceNumber($specialPrice, 2);
            $priceModel->setSalePrice($specialPrice);
        }

        $price = $this->item['products_price'];
        if ($exportGrossPrice) {
            $price *= 1 + ($taxPercent / 100);
        }
        $price *= $this->_exchangeRate;
        $price = $this->formatPriceNumber($price, 2);

        $priceModel->setPrice($price);
        $priceModel->setType(
            $exportGrossPrice
                ? Shopgate_Model_Catalog_Price::DEFAULT_PRICE_TYPE_GROSS
                : Shopgate_Model_Catalog_Price::DEFAULT_PRICE_TYPE_NET
        );

        parent::setPrice($priceModel);
    }

    public function setWeight()
    {
        parent::setWeight($this->item['products_weight']);
    }

    public function setWeightUnit()
    {
        parent::setWeightUnit(Shopgate_Helpers_ItemXml::DEFAULT_WEIGHT_UNIT_KG);
    }

    public function setImages()
    {
        $images = array();
        foreach ($this->getProductImages($this->item) as $imageUrl) {
            $imageModel = new Shopgate_Model_Media_Image();
            $imageModel->setUrl(str_replace(' ', '%20', $imageUrl));
            $images[] = $imageModel;
        }

        parent::setImages($images);
    }

    public function setCategoryPaths()
    {
        $categories = array();
        foreach ($this->getCategoryNumbers($this->item['products_id']) as $category) {
            $categoryModel = new Shopgate_Model_Catalog_CategoryPath();
            $categoryModel->setUid($category);
            $categoryModel->setSortOrder($this->item['sort_order']);
            $categories[] = $categoryModel;
        }

        parent::setCategoryPaths($categories);
    }

    public function setShipping()
    {
        $shipping = new Shopgate_Model_Catalog_Shipping();

        parent::setShipping($shipping);
    }

    public function setManufacturer()
    {
        $manufacturer = new Shopgate_Model_Catalog_Manufacturer();
        $manufacturer->setUid($this->item['manufacturers_id']);
        $manufacturer->setTitle($this->item['manufacturers_name']);

        parent::setManufacturer($manufacturer);
    }

    public function setVisibility()
    {
        $visibility = new Shopgate_Model_Catalog_Visibility();
        $visibility->setLevel(Shopgate_Model_Catalog_Visibility::DEFAULT_VISIBILITY_CATALOG_AND_SEARCH);
        $visibility->setMarketplace(1);

        parent::setVisibility($visibility);
    }

    public function setStock()
    {
        $stock = new Shopgate_Model_Catalog_Stock();
        $stock->setAvailabilityText(
            $this->getAvailableText(
                $this->item['products_quantity'], $this->item['products_date_available'], $this->item['products_status']
            )
        );
        $stock->setIsSaleable($this->getIsAvailable($this->item['products_quantity'], $this->item['products_status']));
        $stock->setStockQuantity($this->item['products_quantity']);
        $stock->setUseStock($this->buildUseStock());

        parent::setStock($stock);
    }

    public function setIdentifiers()
    {
        $identifier = new Shopgate_Model_Catalog_Identifier();
        $identifier->setType('sku');
        $identifier->setValue($this->item['products_model']);

        parent::setIdentifiers(array($identifier));
    }

    public function setDisplayType()
    {
        parent::setDisplayType(Shopgate_Helpers_ItemXml::DISPLAY_TYPE_DEFAULT);
    }

    public function setInputs()
    {
        $sqlQuery = "SELECT
                            pa.options_id, pa.options_values_id, pa.options_values_price, pa.price_prefix,
                            po.products_options_name,
                            pov.products_options_values_name
                        FROM " . TABLE_PRODUCTS_ATTRIBUTES . " AS pa
                        LEFT JOIN  " . TABLE_PRODUCTS_OPTIONS . " AS po
                            ON (pa.options_id = po.products_options_id AND po.language_id = " . ((int)$this->_langId) . ")
                        LEFT JOIN  " . TABLE_PRODUCTS_OPTIONS_VALUES . " AS pov
                            ON (pov.products_options_values_id = pa.options_values_id)
                        WHERE
                            pa.products_id = {$this->item['products_id']}
                        ORDER BY
                            po.products_options_id";

        $queryResult = ShopgateWrapper::db_query_print_on_err($sqlQuery);
        $dbResult    = array();

        while ($dbRow = ShopgateWrapper::db_fetch_array($queryResult)) {
            $dbResult[$dbRow['options_id']][] = $dbRow;
        }

        $resultInputs = array();
        foreach ($dbResult as $inputs) {
            $inputOptions = array();
            $firstElement = reset($inputs);
            /** @var Shopgate_Model_Catalog_Input $inputModel */
            $inputModel = new Shopgate_Model_Catalog_Input();

            $inputModel->setUid($firstElement['options_id']);
            $inputModel->setType(Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_SELECT);
            $inputModel->setLabel($firstElement['products_options_name']);
            $inputModel->setRequired(true);

            foreach ($inputs as $row) {
                $prefixModifier   = $row['price_prefix'] == '+' ? 1 : -1;
                $optionsPrice     = $this->getOptionsValuesPrice(
                    $prefixModifier, $row['options_values_price'],
                    $this->item['tax_rate']
                );
                /** @var Shopgate_Model_Catalog_Option $inputOptionModel */
                $inputOptionModel = new Shopgate_Model_Catalog_Option();
                $inputOptionModel->setUid($row['options_values_id']);
                $inputOptionModel->setLabel($row['products_options_values_name']);
                $inputOptionModel->setValue($row['products_options_values_name']);
                $inputOptionModel->setAdditionalPrice($optionsPrice);
                $inputOptions[] = $inputOptionModel;
            }
            $inputModel->setOptions($inputOptions);
            $resultInputs[] = $inputModel;
        }

        parent::setInputs($resultInputs);
    }
}
