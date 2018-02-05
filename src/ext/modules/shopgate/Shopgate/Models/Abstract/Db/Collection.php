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
 * Main class to manipulate collections
 */
class Shopgate_Models_Abstract_Db_Collection extends Shopgate_Models_Abstract_Db_Item implements
    Shopgate_Interfaces_Db_ItemInterface,
    Shopgate_Interfaces_Db_CollectionInterface,
    Iterator
{
    /**
     * Retrieves the DbItem collection
     *
     * @var Shopgate_Models_Abstract_Db_Item[]
     */
    protected $collection = array();

    /**
     * Collection setter
     *
     * @param Shopgate_Models_Abstract_Db_Item[] $collection
     *
     * @return $this
     */
    private function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Collection getter based on Main table
     *
     * @return $this
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->prepareSelectFrom();
            $collection = $this->tableToObject($this->fetchSelectResults());
            $this->setCollection($collection);
        }

        return $this;
    }

    /**
     * Attempts to retrieve the first object of the collection
     *
     * @return $this|Shopgate_Models_Abstract_Db_Item
     */
    public function getFirstItem()
    {
        if (!isset($this->collection[0])) {
            return $this;
        }

        return $this->collection[0];
    }

    /**
     * Helper that allows to transfer table array row
     * into a collection of objects
     *
     * @param array $tableRows
     *
     * @return Shopgate_Models_Abstract_Db_Item[]
     */
    private function tableToObject($tableRows)
    {
        $list = array();
        foreach ($tableRows as $row) {
            /** @var Shopgate_Models_Abstract_Db_Item $item */
            $item = new $this();
            $item->setMainTable($this->getMainTable());
            $item->setData($row);
            $this->setOrigData();
            $item->setObjectToOld();
            $list[] = $item;
        }

        return $list;
    }

    /**
     * Only saves objects that were modified
     * inside the collection or saves object
     * if the collection was not loaded
     *
     * @return $this
     * @throws Exception
     */
    public function save()
    {
        if ($this->getSize() > 0) {
            foreach ($this->getCollection() as $item) {
                $item->save();
            }
        } else {
            parent::save();
        }

        return $this;
    }

    /**
     * Load only works if a collection was not loaded. If we don't,
     * we will have issues with the save method as it will prioritize
     * collection saving over object data saving
     *
     * @param int    $index  - table primary key ID
     * @param string $column - name of the primary unique table
     *
     * @return $this
     * @throws Exception
     */
    public function load($index, $column = '')
    {
        if ($this->getSize() > 0) {
            throw $this->getDbException('Cannot load object if collection was loaded previously');
        } else {
            parent::load($index, $column);
        }

        return $this;
    }

    /**
     * Outputs size of the collection
     *
     * @return int
     */
    public function getSize()
    {
        return count($this->collection);
    }

    /**
     * Returns a string of item ids in the collection
     *
     * @return string
     */
    public function getAllIds()
    {
        $ids = array();
        foreach ($this->collection as $item) {
            $ids[] = $item->getId();
        }

        return implode(',', $ids);
    }

    /** =========   Iterator Functions  =============== */

    /**
     * Rewind function rewrite
     */
    public function rewind()
    {
        reset($this->collection);
    }

    /**
     * Current pointer rewrite
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * Next pointer rewrite
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * Key pointer rewrite
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->collection);
    }

    /**
     * Validation check rewrite
     *
     * @return bool
     */
    public function valid()
    {
        $key = $this->key();

        return ($key !== null && $key !== false);
    }
}
