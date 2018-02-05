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
 * Handles database table access
 */
class Shopgate_Models_Abstract_Db_Item extends Shopgate_Models_Abstract_Db_Query implements
    Shopgate_Interfaces_Db_ItemInterface
{
    /**
     * Table that this object belongs to
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * Object data that is being saved,
     * may contain less fields as it
     * represents available columns in
     * the table.
     *
     * @var Shopgate_Models_Abstract_Db_Item
     */
    protected $savedObject;

    /**
     * All initialized items are considered
     * to be new unless loaded. Indicator for
     * item to be saved (true) or updated (false)
     *
     * @var bool
     */
    protected $newItem = true;

    /**
     * @throws Shopgate_Models_Abstract_Db_Exception
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Alternative ID getter
     *
     * @return mixed|string
     */
    public function getId()
    {
        $id = $this->getDataProperty($this->getIdFieldName());

        return $id ? $id : parent::getId();
    }

    /**
     * Concat table name to _id to get main index
     * of the table. Does not concatenate in case
     * the field is overwritten.
     *
     * @return string
     */
    public function getIdFieldName()
    {
        $tableName = $this->getMainTable() ? $this->getMainTable() . '_' : '';
        if ($this->idFieldName !== 'id') {
            return $this->idFieldName;
        }

        return $tableName . $this->idFieldName;
    }

    /**
     * Gets main table of object
     *
     * @return string
     */
    public function getMainTable()
    {
        return $this->tableName;
    }

    /**
     * Retrieves the flag to determine
     * if this item exists in the database
     *
     * @return bool
     */
    protected function isNewItem()
    {
        return $this->newItem;
    }

    /**
     * Sets main table name of object
     *
     * @param string $tableName
     *
     * @return string
     */
    public function setMainTable($tableName)
    {
        return $this->tableName = $tableName;
    }

    /**
     * Loads row from database
     *
     * @param int    $index  - WHERE default_col = $index
     * @param string $column - WHERE $column = $index
     *
     * @return $this
     * @throws Shopgate_Models_Abstract_Db_Exception
     */
    public function load($index, $column = '')
    {
        $result    = null;
        $colSelect = $column ? $column : $this->getIdFieldName();

        if (!is_null($index)) {
            $this->prepareSelectFrom();
            $this->getSelect()->where($colSelect . ' = ?', $index);
            $result = $this->fetchSelectResults();
        }

        if (count($result) > 1) {
            throw $this->getDbException(
                'Too many results for load function, column must be unique. Use collection instead.'
            );
        }

        if (!empty($result)) {
            $this->setData(array_pop($result));
            $this->setOrigData();
            $this->setObjectToOld();
        }

        return $this;
    }

    /**
     * Saves current object's fields to the Main table. At least the ones that exist.
     * Note, $this retain all of the values, even the ones that were not saved.
     * SavedObject reflects the saved columns.
     *
     * @return $this
     * @throws Shopgate_Models_Abstract_Db_Exception
     */
    public function save()
    {
        if (!$this->hasDataChanges()) {
            return $this;
        }

        try {
            $this->beforeSave();
            $this->beginTransaction();
            if ($this->isNewItem()) {
                $this->saveItem();
            } else {
                $this->updateItem();
            }
            $this->setObjectToOld();
            $this->afterSave();
        } catch (Exception $e) {
            throw $this->getDbException('Issue saving object: ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Deletes current object using the primary key
     *
     * @return $this
     */
    public function delete()
    {
        if (!$this->getId()) {
            return $this;
        }
        $query   = "DELETE FROM " . $this->getMainTable()
            . " WHERE {$this->getIdFieldName()}={$this->getId()}"
            . " ORDER BY {$this->getIdFieldName()}"
            . " LIMIT 1";
        $success = ShopgateWrapper::db_query($query);

        /**
         * Reset object so we can re-save again
         */
        if ($success) {
            $this->setId(null);
            $this->newItem = true;
        }

        return $this;
    }

    /**
     * Starts recording the transaction details
     *
     * @return $this
     */
    private function beginTransaction()
    {
        return $this;
    }

    /**
     * Before save logic ready for overwrite
     */
    protected function beforeSave()
    {
        return $this;
    }

    /**
     * Compares saved fields to actual
     * object fields. If they do not match it prints them.
     */
    protected function afterSave()
    {
        $objData   = $this->getData();
        $savedData = $this->getSavedObject()->getData();
        if ($objData !== $savedData) {
            $unsaved = array_diff($savedData, $objData);
            $debug   = 'Unsaved variables ' . implode(',', $unsaved);
            ShopgateLogger::getInstance()->log($debug, ShopgateLogger::LOGTYPE_DEBUG);
        }
    }

    /**
     * Object saving logic
     *
     * @return $this
     */
    private function saveItem()
    {
        $this->prepareObjectForSave();
        ShopgateWrapper::db_execute_query($this->getMainTable(), $this->getSavedObject()->getData(), 'insert');
        $id = ShopgateWrapper::db_insert_id();
        if ($id) {
            $this->setId($id);
            $this->getSavedObject()->setId($id);
        }

        return $this;
    }

    /**
     * Updates existing fields only, ignores all others
     * Note, $this retain all of the values, even the ones
     * that were not updated. SavedObject reflects the saved
     * columns.
     *
     * @return $this
     */
    private function updateItem()
    {
        $this->prepareObjectForSave();
        $where = "{$this->getIdFieldName()} = '{$this->getId()}'";
        ShopgateWrapper::db_execute_query($this->getMainTable(), $this->getSavedObject()->getData(), 'update', $where);

        return $this;
    }

    /**
     * Clones the current item and returns
     * an item with fields that can be saved
     *
     * @return Shopgate_Models_Abstract_Db_Item
     */
    private function prepareObjectForSave()
    {
        $columns = ShopgateWrapper::db_get_columns($this->getMainTable());
        $object  = clone $this;
        foreach ($columns as $row) {
            if (!array_key_exists($row['Field'], $object->getData())) {
                $object->unsetData($row['Field']);
            }
        }

        return $this->setSavedObject($object);
    }

    /**
     * Retrieves the object that was
     * actually saved to the database
     * fields
     *
     * @return Shopgate_Models_Abstract_Db_Item
     */
    public function getSavedObject()
    {
        if (!$this->savedObject) {
            return $this->setSavedObject(clone $this);
        }

        return $this->savedObject;
    }

    /**
     * Sets the object that is saved to the database
     *
     * @param Shopgate_Models_Abstract_Db_Item $object
     *
     * @return mixed
     */
    private function setSavedObject(Shopgate_Models_Abstract_Db_Item $object)
    {
        return $this->savedObject = $object;
    }

    /**
     * Fetch collection from select
     *
     * @return mixed[]
     */
    protected function fetchSelectResults()
    {
        $collection = array();
        $query      = $this->getSelect()->__toString();
        if (!$query) {
            return $collection;
        }

        $tableRows = ShopgateWrapper::db_query($query);
        while ($row = ShopgateWrapper::db_fetch_array($tableRows)) {
            $collection[] = $row;
        }

        return $collection;
    }

    /**
     * Sets object to old. Meaning it was
     * loaded from the database and has
     * no data changes yet
     *
     * @return $this
     */
    protected function setObjectToOld()
    {
        $this->hasDataChanges = false;
        $this->newItem        = false;

        return $this;
    }

    /**
     * This prepares the FROM 'table'
     * right before the loading of a single
     * item or collection is made
     */
    protected function prepareSelectFrom()
    {
        $from = $this->getSelect()->getPart('from');
        if (!isset($from['main'])) {
            $this->getSelect()->from(array('main' => $this->getMainTable()));
        }
    }
}
