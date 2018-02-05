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
class Shopgate_Helpers_CategoryXml extends Shopgate_Helpers_Category
{
    public function setUid()
    {
        parent::setUid($this->item['categories_id']);
    }

    public function setSortOrder()
    {
        parent::setSortOrder($this->buildSortOrder($this->item["sort_order"]));
    }

    public function setName()
    {
        parent::setName($this->item['categories_name']);
    }

    public function setParentUid()
    {
        parent::setParentUid($this->buildParentUid($this->item["parent_id"], $this->item['categories_id']));
    }

    public function setImage()
    {
        $image = new Shopgate_Model_Media_Image();
        $image->setUrl($this->buildCategoryImageUrl($this->item["categories_image"]));

        parent::setImage($image);
    }

    public function setIsActive()
    {
        parent::setIsActive(true);
    }

    public function setDeeplink()
    {
        parent::setDeeplink($this->buildDeeplink($this->item['categories_id']));
    }
}
