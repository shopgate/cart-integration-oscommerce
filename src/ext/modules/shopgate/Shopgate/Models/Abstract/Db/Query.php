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
 * Zend select initialization
 */
class Shopgate_Models_Abstract_Db_Query extends Shopgate_Models_Abstract_Object
{
    protected $select;

    /**
     * Not the best way of initializing, injection would be best
     */
    public function __construct()
    {
        $adapter = new Shopgate_Models_Abstract_Db_Adapter(
            array(
                'dbname'   => '',
                'password' => '',
                'username' => ''
            )
        );
        $this->select = $adapter->select();

        return parent::__construct();
    }

    /**
     * Returns the select object
     *
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * Exception for the DB classes
     *
     * @param $message - exception message to print
     *
     * @return Shopgate_Models_Abstract_Db_Exception
     */
    protected function getDbException($message)
    {
        return new Shopgate_Models_Abstract_Db_Exception($message);
    }
}
