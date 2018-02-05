<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Varien
 * @package     Varien_Object
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Default object handler
 */
class Shopgate_Models_Abstract_Object implements ArrayAccess
{
    /**
     * Object attributes
     *
     * @var array
     */
    protected $data = array();

    /**
     * Data changes flag (true after setData|unsetData call)
     *
     * @var $_hasDataChange bool
     */
    protected $hasDataChanges = false;

    /**
     * Original data that was loaded
     *
     * @var array
     */
    protected $origData;

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $idFieldName = 'id';

    /**
     * Setter/Getter underscore transformation cache
     *
     * @var array
     */
    protected static $underscoreCache = array();

    /**
     * Object delete flag
     *
     * @var bool
     */
    protected $isDeleted = false;

    /**
     * Constructor
     *
     * By default is looking for first argument as array and assigns it as object attributes
     * This behavior may change in child classes
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

    /**
     * Set isDeleted flag value (if $isDeleted parameter is defined) and return current flag value
     *
     * @param boolean $isDeleted
     *
     * @return bool
     */
    public function isDeleted($isDeleted = null)
    {
        $result = $this->isDeleted;
        if ($isDeleted !== null) {
            $this->isDeleted = $isDeleted;
        }

        return $result;
    }

    /**
     * Check if initial object data was changed.
     *
     * Initial data is coming to object constructor.
     * Flag value should be set up to true after any external data changes
     *
     * @return bool
     */
    public function hasDataChanges()
    {
        return $this->hasDataChanges;
    }

    /**
     * Id field name setter
     *
     * @param  string $name
     *
     * @return $this
     */
    public function setIdFieldName($name)
    {
        $this->idFieldName = $name;

        return $this;
    }

    /**
     * Id field name getter
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->idFieldName;
    }

    /**
     * Identifier getter
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getDataProperty($this->idFieldName);
    }

    /**
     * Identifier setter
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setId($value)
    {
        $this->setData($this->getIdFieldName(), $value);

        return $this;
    }

    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $arr
     *
     * @return $this
     */
    public function addData(array $arr)
    {
        foreach ($arr as $index => $value) {
            $this->setData($index, $value);
        }

        return $this;
    }

    /**
     * Overwrite data in the object.
     *
     * The $key parameter can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if ($key === null) {
            return $this;
        } elseif ($key === (array)$key) {
            if ($this->data !== $key) {
                $this->hasDataChanges = true;
            }
            $this->data = $key;
        } else {
            if (!array_key_exists($key, $this->data) || $this->data[$key] !== $value) {
                $this->hasDataChanges = true;
            }
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Unset data from the object.
     *
     * @param null|string|array $key
     *
     * @return $this
     */
    public function unsetData($key = null)
    {
        if ($key === null) {
            $this->setData(array());
        } elseif (is_string($key)) {
            if (isset($this->data[$key]) || array_key_exists($key, $this->data)) {
                $this->hasDataChanges = true;
                unset($this->data[$key]);
            }
        } elseif ($key === (array)$key) {
            foreach ($key as $element) {
                $this->unsetData($element);
            }
        }

        return $this;
    }

    /**
     * Object data getter
     *
     * If $key is not defined will return all the data as an array.
     * Otherwise it will return value of the element specified by $key.
     * It is possible to use keys like a/b/c for access nested array data
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member. If data is the string - it will be explode
     * by new line character and converted to array.
     *
     * @param string     $key
     * @param string|int $index
     *
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        if ('' === $key) {
            return $this->data;
        }

        /* process a/b/c key as ['a']['b']['c'] */
        if (strpos($key, '/')) {
            $data = $this->getDataByPath($key);
        } else {
            $data = $this->getDataProperty($key);
        }

        if ($index !== null) {
            if ($data === (array)$data) {
                $data = isset($data[$index]) ? $data[$index] : null;
            } elseif (is_string($data)) {
                $data = explode(PHP_EOL, $data);
                $data = isset($data[$index]) ? $data[$index] : null;
            } elseif ($data instanceof Shopgate_Models_Abstract_Object) {
                $data = $data->getData($index);
            } else {
                $data = null;
            }
        }

        return $data;
    }

    /**
     * Get object data by path
     *
     * Method consider the path as chain of keys: a/b/c => ['a']['b']['c']
     *
     * @param string $path
     *
     * @return mixed
     */
    public function getDataByPath($path)
    {
        $keys = explode('/', $path);

        $data = $this->data;
        foreach ($keys as $key) {
            if ((array)$data === $data && isset($data[$key])) {
                $data = $data[$key];
            } elseif ($data instanceof Shopgate_Models_Abstract_Object) {
                $data = $data->getDataByKey($key);
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * Get object data by particular key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getDataByKey($key)
    {
        return $this->getDataProperty($key);
    }

    /**
     * Get value from data array without parsing the key
     *
     * @param   string $key
     *
     * @return  mixed
     */
    protected function getDataProperty($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * Set object data with calling setter method
     *
     * @param string $key
     * @param mixed  $args
     *
     * @return $this
     */
    public function setDataUsingMethod($key, $args = array())
    {
        $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
        $this->{$method}($args);

        return $this;
    }

    /**
     * Get object data by key with calling getter method
     *
     * @param string $key
     * @param mixed  $args
     *
     * @return mixed
     */
    public function getDataUsingMethod($key, $args = null)
    {
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));

        return $this->{$method}($args);
    }

    /**
     * Fast get data or set default if value is not available
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getDataSetDefault($key, $default)
    {
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = $default;
        }

        return $this->data[$key];
    }

    /**
     * If $key is empty, checks whether there's any data in the object
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasData($key = '')
    {
        if (empty($key) || !is_string($key)) {
            return !empty($this->data);
        }

        return array_key_exists($key, $this->data);
    }

    /**
     * Convert array of object data with to array with keys requested in $keys array
     *
     * @param array $keys array of required keys
     *
     * @return array
     */
    public function toArray(array $keys = array())
    {
        if (empty($keys)) {
            return $this->data;
        }

        $result = array();
        foreach ($keys as $key) {
            if (isset($this->data[$key])) {
                $result[$key] = $this->data[$key];
            } else {
                $result[$key] = null;
            }
        }

        return $result;
    }

    /**
     * The "__" style wrapper for toArray method
     *
     * @param  array $keys
     *
     * @return array
     */
    public function convertToArray(array $keys = array())
    {
        return $this->toArray($keys);
    }

    /**
     * Convert object data into XML string
     *
     * @param array  $keys       array of keys that must be represented
     * @param string $rootName   root node name
     * @param bool   $addOpenTag flag that allow to add initial xml node
     * @param bool   $addCdata   flag that require wrap all values in CDATA
     *
     * @return string
     */
    public function toXml(array $keys = array(), $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
        $xml  = '';
        $data = $this->toArray($keys);
        foreach ($data as $fieldName => $fieldValue) {
            if ($addCdata === true) {
                $fieldValue = "<![CDATA[{$fieldValue}]]>";
            } else {
                $fieldValue = str_replace(
                    array('&', '"', "'", '<', '>'),
                    array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'),
                    $fieldValue
                );
            }
            $xml .= "<{$fieldName}>{$fieldValue}</{$fieldName}>\n";
        }
        if ($rootName) {
            $xml = "<{$rootName}>\n{$xml}</{$rootName}>\n";
        }
        if ($addOpenTag) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;
        }

        return $xml;
    }

    /**
     * The "__" style wrapper for toXml method
     *
     * @param array  $arrAttributes array of keys that must be represented
     * @param string $rootName      root node name
     * @param bool   $addOpenTag    flag that allow to add initial xml node
     * @param bool   $addCdata      flag that require wrap all values in CDATA
     *
     * @return string
     */
    public function convertToXml(
        array $arrAttributes = array(),
        $rootName = 'item',
        $addOpenTag = false,
        $addCdata = true
    ) {
        return $this->toXml($arrAttributes, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * Convert object data to JSON
     *
     * @param array $keys array of required keys
     *
     * @return string
     */
    public function toJson(array $keys = array())
    {
        $data = $this->toArray($keys);

        return json_encode($data);
    }

    /**
     * The "__" style wrapper for toJson
     *
     * @param  array $keys
     *
     * @return string
     */
    public function convertToJson(array $keys = array())
    {
        return $this->toJson($keys);
    }

    /**
     * Convert object data into string with predefined format
     *
     * Will use $format as an template and substitute {{key}} for attributes
     *
     * @param string $format
     *
     * @return string
     */
    public function toString($format = '')
    {
        if (empty($format)) {
            $result = implode(', ', $this->getData());
        } else {
            preg_match_all('/\{\{([a-z0-9_]+)\}\}/is', $format, $matches);
            foreach ($matches[1] as $var) {
                $format = str_replace('{{' . $var . '}}', $this->getData($var), $format);
            }
            $result = $format;
        }

        return $result;
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array  $args
     *
     * @return  mixed
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key   = $this->_underscore(substr($method, 3));
                $index = isset($args[0]) ? $args[0] : null;

                return $this->getData($key, $index);
            case 'set':
                $key   = $this->_underscore(substr($method, 3));
                $value = isset($args[0]) ? $args[0] : null;

                return $this->setData($key, $value);
            case 'uns':
                $key = $this->_underscore(substr($method, 3));

                return $this->unsetData($key);
            case 'has':
                $key = $this->_underscore(substr($method, 3));

                return isset($this->data[$key]);
        }
        throw new Exception(
            sprintf('Invalid method %1::%2(%3)', get_class($this), $method, print_r($args, 1))
        );
    }

    /**
     * Checks whether the object is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        if (empty($this->data)) {
            return true;
        }

        return false;
    }

    /**
     * Converts field names for setters and getters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unnecessary preg_replace
     *
     * @param string $name
     *
     * @return string
     */
    protected function _underscore($name)
    {
        if (isset(self::$underscoreCache[$name])) {
            return self::$underscoreCache[$name];
        }
        $result                       = strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $name), '_'));
        self::$underscoreCache[$name] = $result;

        return $result;
    }

    /**
     * Convert object data into string with defined keys and values.
     *
     * Example: key1="value1" key2="value2" ...
     *
     * @param   array  $keys           array of accepted keys
     * @param   string $valueSeparator separator between key and value
     * @param   string $fieldSeparator separator between key/value pairs
     * @param   string $quote          quoting sign
     *
     * @return  string
     */
    public function serialize($keys = array(), $valueSeparator = '=', $fieldSeparator = ' ', $quote = '"')
    {
        $data = array();
        if (empty($keys)) {
            $keys = array_keys($this->data);
        }

        foreach ($this->data as $key => $value) {
            if (in_array($key, $keys)) {
                $data[] = $key . $valueSeparator . $quote . $value . $quote;
            }
        }

        return implode($fieldSeparator, $data);
    }

    /**
     * Initialize object original data
     *
     * @param string $key
     * @param mixed  $data
     *
     * @return $this
     */
    protected function setOrigData($key = null, $data = null)
    {
        if ($key === null) {
            $this->origData = $this->data;
        } else {
            $this->origData[$key] = $data;
        }

        return $this;
    }

    /**
     * Get object original data
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getOrigData($key = null)
    {
        if ($key === null) {
            return $this->origData;
        }
        if (isset($this->origData[$key])) {
            return $this->origData[$key];
        }

        return null;
    }

    /**
     * Compare object data with original data
     *
     * @param string $field
     *
     * @return bool
     */
    public function dataHasChangedFor($field)
    {
        $newData  = $this->getData($field);
        $origData = $this->getOrigData($field);

        return $newData != $origData;
    }

    /**
     * Clears data changes status
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setDataChanges($value)
    {
        $this->hasDataChanges = (bool)$value;

        return $this;
    }

    /**
     * Present object data as string in debug mode
     *
     * @param mixed $data
     * @param array &$objects
     *
     * @return array
     */
    public function debug($data = null, &$objects = array())
    {
        if ($data === null) {
            $hash = spl_object_hash($this);
            if (!empty($objects[$hash])) {
                return '*** RECURSION ***';
            }
            $objects[$hash] = true;
            $data           = $this->getData();
        }
        $debug = array();
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $debug[$key] = $value;
            } elseif (is_array($value)) {
                $debug[$key] = $this->debug($value, $objects);
            } elseif ($value instanceof Shopgate_Models_Abstract_Object) {
                $debug[$key . ' (' . get_class($value) . ')'] = $value->debug(null, $objects);
            }
        }

        return $debug;
    }

    /**
     * Implementation of \ArrayAccess::offsetSet()
     *
     * @param string $offset
     * @param mixed  $value
     *
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Implementation of \ArrayAccess::offsetExists()
     *
     * @param string $offset
     *
     * @return bool
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]) || array_key_exists($offset, $this->data);
    }

    /**
     * Implementation of \ArrayAccess::offsetUnset()
     *
     * @param string $offset
     *
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * Implementation of \ArrayAccess::offsetGet()
     *
     * @param string $offset
     *
     * @return mixed
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return null;
    }
}
