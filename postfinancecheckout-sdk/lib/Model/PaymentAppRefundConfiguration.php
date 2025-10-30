<?php
/**
 * wallee SDK
 *
 * This library allows to interact with the wallee payment service.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


namespace Wallee\Sdk\Model;

use \ArrayAccess;
use \Wallee\Sdk\ObjectSerializer;

/**
 * PaymentAppRefundConfiguration model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class PaymentAppRefundConfiguration implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'PaymentAppRefundConfiguration';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'multiple_refunds_supported' => 'bool',
        'refund_endpoint' => 'string',
        'refund_timeout_in_minutes' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'multiple_refunds_supported' => null,
        'refund_endpoint' => null,
        'refund_timeout_in_minutes' => 'int32'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'multiple_refunds_supported' => 'multipleRefundsSupported',
        'refund_endpoint' => 'refundEndpoint',
        'refund_timeout_in_minutes' => 'refundTimeoutInMinutes'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'multiple_refunds_supported' => 'setMultipleRefundsSupported',
        'refund_endpoint' => 'setRefundEndpoint',
        'refund_timeout_in_minutes' => 'setRefundTimeoutInMinutes'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'multiple_refunds_supported' => 'getMultipleRefundsSupported',
        'refund_endpoint' => 'getRefundEndpoint',
        'refund_timeout_in_minutes' => 'getRefundTimeoutInMinutes'
    ];

    

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        
        $this->container['multiple_refunds_supported'] = isset($data['multiple_refunds_supported']) ? $data['multiple_refunds_supported'] : null;
        
        $this->container['refund_endpoint'] = isset($data['refund_endpoint']) ? $data['refund_endpoint'] : null;
        
        $this->container['refund_timeout_in_minutes'] = isset($data['refund_timeout_in_minutes']) ? $data['refund_timeout_in_minutes'] : null;
        
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerFormats()
    {
        return self::$swaggerFormats;
    }


    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$swaggerModelName;
    }

    

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }

    

    /**
     * Gets multiple_refunds_supported
     *
     * @return bool
     */
    public function getMultipleRefundsSupported()
    {
        return $this->container['multiple_refunds_supported'];
    }

    /**
     * Sets multiple_refunds_supported
     *
     * @param bool $multiple_refunds_supported This flag indicates whether the connector supports multiple refunds for a single transaction or not.
     *
     * @return $this
     */
    public function setMultipleRefundsSupported($multiple_refunds_supported)
    {
        $this->container['multiple_refunds_supported'] = $multiple_refunds_supported;

        return $this;
    }
    

    /**
     * Gets refund_endpoint
     *
     * @return string
     */
    public function getRefundEndpoint()
    {
        return $this->container['refund_endpoint'];
    }

    /**
     * Sets refund_endpoint
     *
     * @param string $refund_endpoint The refund endpoint is invoked to request the payment service provider to execute a refund.
     *
     * @return $this
     */
    public function setRefundEndpoint($refund_endpoint)
    {
        $this->container['refund_endpoint'] = $refund_endpoint;

        return $this;
    }
    

    /**
     * Gets refund_timeout_in_minutes
     *
     * @return int
     */
    public function getRefundTimeoutInMinutes()
    {
        return $this->container['refund_timeout_in_minutes'];
    }

    /**
     * Sets refund_timeout_in_minutes
     *
     * @param int $refund_timeout_in_minutes When the refund is triggered we expect a feedback about the state of it. This timeout defines after how long we consider the refund as failed without receiving a final state update.
     *
     * @return $this
     */
    public function setRefundTimeoutInMinutes($refund_timeout_in_minutes)
    {
        $this->container['refund_timeout_in_minutes'] = $refund_timeout_in_minutes;

        return $this;
    }
    
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
            return json_encode(
                ObjectSerializer::sanitizeForSerialization($this),
                JSON_PRETTY_PRINT
            );
        }

        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


