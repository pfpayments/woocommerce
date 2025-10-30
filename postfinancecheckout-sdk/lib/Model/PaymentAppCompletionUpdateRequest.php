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
 * PaymentAppCompletionUpdateRequest model
 *
 * @category    Class
 * @description The completion update request allows to change the state of a completion. The completion must be linked with a processor that was created by the payment Web App that invokes the operation.
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class PaymentAppCompletionUpdateRequest implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'PaymentAppCompletionUpdateRequest';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'completion_id' => 'int',
        'failure_reason_id' => 'int',
        'reference' => 'string',
        'target_state' => '\Wallee\Sdk\Model\PaymentAppCompletionTargetState'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'completion_id' => 'int64',
        'failure_reason_id' => 'int64',
        'reference' => null,
        'target_state' => null
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'completion_id' => 'completionId',
        'failure_reason_id' => 'failureReasonId',
        'reference' => 'reference',
        'target_state' => 'targetState'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'completion_id' => 'setCompletionId',
        'failure_reason_id' => 'setFailureReasonId',
        'reference' => 'setReference',
        'target_state' => 'setTargetState'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'completion_id' => 'getCompletionId',
        'failure_reason_id' => 'getFailureReasonId',
        'reference' => 'getReference',
        'target_state' => 'getTargetState'
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
        
        $this->container['completion_id'] = isset($data['completion_id']) ? $data['completion_id'] : null;
        
        $this->container['failure_reason_id'] = isset($data['failure_reason_id']) ? $data['failure_reason_id'] : null;
        
        $this->container['reference'] = isset($data['reference']) ? $data['reference'] : null;
        
        $this->container['target_state'] = isset($data['target_state']) ? $data['target_state'] : null;
        
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if (!is_null($this->container['reference']) && (mb_strlen($this->container['reference']) > 100)) {
            $invalidProperties[] = "invalid value for 'reference', the character length must be smaller than or equal to 100.";
        }

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
     * Gets completion_id
     *
     * @return int
     */
    public function getCompletionId()
    {
        return $this->container['completion_id'];
    }

    /**
     * Sets completion_id
     *
     * @param int $completion_id This is the ID of the completion that should be updated.
     *
     * @return $this
     */
    public function setCompletionId($completion_id)
    {
        $this->container['completion_id'] = $completion_id;

        return $this;
    }
    

    /**
     * Gets failure_reason_id
     *
     * @return int
     */
    public function getFailureReasonId()
    {
        return $this->container['failure_reason_id'];
    }

    /**
     * Sets failure_reason_id
     *
     * @param int $failure_reason_id The failure reason indicates why the completion failed. It is required when the target state is FAILED.
     *
     * @return $this
     */
    public function setFailureReasonId($failure_reason_id)
    {
        $this->container['failure_reason_id'] = $failure_reason_id;

        return $this;
    }
    

    /**
     * Gets reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->container['reference'];
    }

    /**
     * Sets reference
     *
     * @param string $reference The reference identifies the completion within the systems of the external service provider. It is required when the target state is SUCCESSFUL.
     *
     * @return $this
     */
    public function setReference($reference)
    {
        if (!is_null($reference) && (mb_strlen($reference) > 100)) {
            throw new \InvalidArgumentException('invalid length for $reference when calling PaymentAppCompletionUpdateRequest., must be smaller than or equal to 100.');
        }

        $this->container['reference'] = $reference;

        return $this;
    }
    

    /**
     * Gets target_state
     *
     * @return \Wallee\Sdk\Model\PaymentAppCompletionTargetState
     */
    public function getTargetState()
    {
        return $this->container['target_state'];
    }

    /**
     * Sets target_state
     *
     * @param \Wallee\Sdk\Model\PaymentAppCompletionTargetState $target_state The target state defines the state into which the completion should be switched into. Once the completion changed the state it will not be possible to change it again.
     *
     * @return $this
     */
    public function setTargetState($target_state)
    {
        $this->container['target_state'] = $target_state;

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


