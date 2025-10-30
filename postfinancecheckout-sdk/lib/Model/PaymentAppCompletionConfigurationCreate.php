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
 * PaymentAppCompletionConfigurationCreate model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class PaymentAppCompletionConfigurationCreate implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'PaymentAppCompletionConfiguration.Create';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'completion_endpoint' => 'string',
        'completion_timeout_in_minutes' => 'int',
        'maximal_completion_delay_in_days' => 'int',
        'multiple_completions_supported' => 'bool',
        'void_endpoint' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'completion_endpoint' => null,
        'completion_timeout_in_minutes' => 'int32',
        'maximal_completion_delay_in_days' => 'int32',
        'multiple_completions_supported' => null,
        'void_endpoint' => null
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'completion_endpoint' => 'completionEndpoint',
        'completion_timeout_in_minutes' => 'completionTimeoutInMinutes',
        'maximal_completion_delay_in_days' => 'maximalCompletionDelayInDays',
        'multiple_completions_supported' => 'multipleCompletionsSupported',
        'void_endpoint' => 'voidEndpoint'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'completion_endpoint' => 'setCompletionEndpoint',
        'completion_timeout_in_minutes' => 'setCompletionTimeoutInMinutes',
        'maximal_completion_delay_in_days' => 'setMaximalCompletionDelayInDays',
        'multiple_completions_supported' => 'setMultipleCompletionsSupported',
        'void_endpoint' => 'setVoidEndpoint'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'completion_endpoint' => 'getCompletionEndpoint',
        'completion_timeout_in_minutes' => 'getCompletionTimeoutInMinutes',
        'maximal_completion_delay_in_days' => 'getMaximalCompletionDelayInDays',
        'multiple_completions_supported' => 'getMultipleCompletionsSupported',
        'void_endpoint' => 'getVoidEndpoint'
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
        
        $this->container['completion_endpoint'] = isset($data['completion_endpoint']) ? $data['completion_endpoint'] : null;
        
        $this->container['completion_timeout_in_minutes'] = isset($data['completion_timeout_in_minutes']) ? $data['completion_timeout_in_minutes'] : null;
        
        $this->container['maximal_completion_delay_in_days'] = isset($data['maximal_completion_delay_in_days']) ? $data['maximal_completion_delay_in_days'] : null;
        
        $this->container['multiple_completions_supported'] = isset($data['multiple_completions_supported']) ? $data['multiple_completions_supported'] : null;
        
        $this->container['void_endpoint'] = isset($data['void_endpoint']) ? $data['void_endpoint'] : null;
        
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
     * Gets completion_endpoint
     *
     * @return string
     */
    public function getCompletionEndpoint()
    {
        return $this->container['completion_endpoint'];
    }

    /**
     * Sets completion_endpoint
     *
     * @param string $completion_endpoint The completion endpoint is invoked to request the payment service provider to execute a completion.
     *
     * @return $this
     */
    public function setCompletionEndpoint($completion_endpoint)
    {
        $this->container['completion_endpoint'] = $completion_endpoint;

        return $this;
    }
    

    /**
     * Gets completion_timeout_in_minutes
     *
     * @return int
     */
    public function getCompletionTimeoutInMinutes()
    {
        return $this->container['completion_timeout_in_minutes'];
    }

    /**
     * Sets completion_timeout_in_minutes
     *
     * @param int $completion_timeout_in_minutes When the completion or the void is triggered we expect a feedback about the state of it. This timeout defines after how long we consider the void resp. completion as failed without receiving a final state update.
     *
     * @return $this
     */
    public function setCompletionTimeoutInMinutes($completion_timeout_in_minutes)
    {
        $this->container['completion_timeout_in_minutes'] = $completion_timeout_in_minutes;

        return $this;
    }
    

    /**
     * Gets maximal_completion_delay_in_days
     *
     * @return int
     */
    public function getMaximalCompletionDelayInDays()
    {
        return $this->container['maximal_completion_delay_in_days'];
    }

    /**
     * Sets maximal_completion_delay_in_days
     *
     * @param int $maximal_completion_delay_in_days The completion resp. the void can be triggered a while after the authorization of the transaction has been executed. This delay defines how many days after the authorization the void resp. completion must be triggered at the latest.
     *
     * @return $this
     */
    public function setMaximalCompletionDelayInDays($maximal_completion_delay_in_days)
    {
        $this->container['maximal_completion_delay_in_days'] = $maximal_completion_delay_in_days;

        return $this;
    }
    

    /**
     * Gets multiple_completions_supported
     *
     * @return bool
     */
    public function getMultipleCompletionsSupported()
    {
        return $this->container['multiple_completions_supported'];
    }

    /**
     * Sets multiple_completions_supported
     *
     * @param bool $multiple_completions_supported This flag indicates whether the connector supports multiple completions for a single transaction or not.
     *
     * @return $this
     */
    public function setMultipleCompletionsSupported($multiple_completions_supported)
    {
        $this->container['multiple_completions_supported'] = $multiple_completions_supported;

        return $this;
    }
    

    /**
     * Gets void_endpoint
     *
     * @return string
     */
    public function getVoidEndpoint()
    {
        return $this->container['void_endpoint'];
    }

    /**
     * Sets void_endpoint
     *
     * @param string $void_endpoint The void endpoint is invoked to request the payment service provider to execute a void.
     *
     * @return $this
     */
    public function setVoidEndpoint($void_endpoint)
    {
        $this->container['void_endpoint'] = $void_endpoint;

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


