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
 * PaymentAppConnectorCreationRequest model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class PaymentAppConnectorCreationRequest implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'PaymentAppConnectorCreationRequest';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'authorization_timeout_in_minutes' => 'int',
        'completion_configuration' => '\Wallee\Sdk\Model\PaymentAppCompletionConfigurationCreate',
        'connector' => 'int',
        'external_id' => 'string',
        'name' => 'string',
        'payment_page_endpoint' => 'string',
        'processor_external_id' => 'string',
        'refund_configuration' => '\Wallee\Sdk\Model\PaymentAppRefundConfigurationCreate'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'authorization_timeout_in_minutes' => 'int32',
        'completion_configuration' => null,
        'connector' => 'int64',
        'external_id' => null,
        'name' => null,
        'payment_page_endpoint' => null,
        'processor_external_id' => null,
        'refund_configuration' => null
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'authorization_timeout_in_minutes' => 'authorizationTimeoutInMinutes',
        'completion_configuration' => 'completionConfiguration',
        'connector' => 'connector',
        'external_id' => 'externalId',
        'name' => 'name',
        'payment_page_endpoint' => 'paymentPageEndpoint',
        'processor_external_id' => 'processorExternalId',
        'refund_configuration' => 'refundConfiguration'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'authorization_timeout_in_minutes' => 'setAuthorizationTimeoutInMinutes',
        'completion_configuration' => 'setCompletionConfiguration',
        'connector' => 'setConnector',
        'external_id' => 'setExternalId',
        'name' => 'setName',
        'payment_page_endpoint' => 'setPaymentPageEndpoint',
        'processor_external_id' => 'setProcessorExternalId',
        'refund_configuration' => 'setRefundConfiguration'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'authorization_timeout_in_minutes' => 'getAuthorizationTimeoutInMinutes',
        'completion_configuration' => 'getCompletionConfiguration',
        'connector' => 'getConnector',
        'external_id' => 'getExternalId',
        'name' => 'getName',
        'payment_page_endpoint' => 'getPaymentPageEndpoint',
        'processor_external_id' => 'getProcessorExternalId',
        'refund_configuration' => 'getRefundConfiguration'
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
        
        $this->container['authorization_timeout_in_minutes'] = isset($data['authorization_timeout_in_minutes']) ? $data['authorization_timeout_in_minutes'] : null;
        
        $this->container['completion_configuration'] = isset($data['completion_configuration']) ? $data['completion_configuration'] : null;
        
        $this->container['connector'] = isset($data['connector']) ? $data['connector'] : null;
        
        $this->container['external_id'] = isset($data['external_id']) ? $data['external_id'] : null;
        
        $this->container['name'] = isset($data['name']) ? $data['name'] : null;
        
        $this->container['payment_page_endpoint'] = isset($data['payment_page_endpoint']) ? $data['payment_page_endpoint'] : null;
        
        $this->container['processor_external_id'] = isset($data['processor_external_id']) ? $data['processor_external_id'] : null;
        
        $this->container['refund_configuration'] = isset($data['refund_configuration']) ? $data['refund_configuration'] : null;
        
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['authorization_timeout_in_minutes'] === null) {
            $invalidProperties[] = "'authorization_timeout_in_minutes' can't be null";
        }
        if ($this->container['connector'] === null) {
            $invalidProperties[] = "'connector' can't be null";
        }
        if ($this->container['external_id'] === null) {
            $invalidProperties[] = "'external_id' can't be null";
        }
        if ((mb_strlen($this->container['external_id']) > 40)) {
            $invalidProperties[] = "invalid value for 'external_id', the character length must be smaller than or equal to 40.";
        }

        if ((mb_strlen($this->container['external_id']) < 1)) {
            $invalidProperties[] = "invalid value for 'external_id', the character length must be bigger than or equal to 1.";
        }

        if ($this->container['name'] === null) {
            $invalidProperties[] = "'name' can't be null";
        }
        if ((mb_strlen($this->container['name']) > 100)) {
            $invalidProperties[] = "invalid value for 'name', the character length must be smaller than or equal to 100.";
        }

        if ($this->container['payment_page_endpoint'] === null) {
            $invalidProperties[] = "'payment_page_endpoint' can't be null";
        }
        if ($this->container['processor_external_id'] === null) {
            $invalidProperties[] = "'processor_external_id' can't be null";
        }
        if ((mb_strlen($this->container['processor_external_id']) > 40)) {
            $invalidProperties[] = "invalid value for 'processor_external_id', the character length must be smaller than or equal to 40.";
        }

        if ((mb_strlen($this->container['processor_external_id']) < 1)) {
            $invalidProperties[] = "invalid value for 'processor_external_id', the character length must be bigger than or equal to 1.";
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
     * Gets authorization_timeout_in_minutes
     *
     * @return int
     */
    public function getAuthorizationTimeoutInMinutes()
    {
        return $this->container['authorization_timeout_in_minutes'];
    }

    /**
     * Sets authorization_timeout_in_minutes
     *
     * @param int $authorization_timeout_in_minutes When the transaction is not authorized within this timeout the transaction is considered as failed.
     *
     * @return $this
     */
    public function setAuthorizationTimeoutInMinutes($authorization_timeout_in_minutes)
    {
        $this->container['authorization_timeout_in_minutes'] = $authorization_timeout_in_minutes;

        return $this;
    }
    

    /**
     * Gets completion_configuration
     *
     * @return \Wallee\Sdk\Model\PaymentAppCompletionConfigurationCreate
     */
    public function getCompletionConfiguration()
    {
        return $this->container['completion_configuration'];
    }

    /**
     * Sets completion_configuration
     *
     * @param \Wallee\Sdk\Model\PaymentAppCompletionConfigurationCreate $completion_configuration The completion configuration allows the connector to support deferred completions on a transaction. If it is not provided the connector will not process transactions in deferred mode.
     *
     * @return $this
     */
    public function setCompletionConfiguration($completion_configuration)
    {
        $this->container['completion_configuration'] = $completion_configuration;

        return $this;
    }
    

    /**
     * Gets connector
     *
     * @return int
     */
    public function getConnector()
    {
        return $this->container['connector'];
    }

    /**
     * Sets connector
     *
     * @param int $connector The ID of the connector identifies which connector that should be linked with this web app connector. The connector defines the payment method.
     *
     * @return $this
     */
    public function setConnector($connector)
    {
        $this->container['connector'] = $connector;

        return $this;
    }
    

    /**
     * Gets external_id
     *
     * @return string
     */
    public function getExternalId()
    {
        return $this->container['external_id'];
    }

    /**
     * Sets external_id
     *
     * @param string $external_id The external ID identifies the connector within the external system. It has to be unique per processor external ID and for any subsequent update the same ID must be sent.
     *
     * @return $this
     */
    public function setExternalId($external_id)
    {
        if ((mb_strlen($external_id) > 40)) {
            throw new \InvalidArgumentException('invalid length for $external_id when calling PaymentAppConnectorCreationRequest., must be smaller than or equal to 40.');
        }
        if ((mb_strlen($external_id) < 1)) {
            throw new \InvalidArgumentException('invalid length for $external_id when calling PaymentAppConnectorCreationRequest., must be bigger than or equal to 1.');
        }

        $this->container['external_id'] = $external_id;

        return $this;
    }
    

    /**
     * Gets name
     *
     * @return string
     */
    public function getName()
    {
        return $this->container['name'];
    }

    /**
     * Sets name
     *
     * @param string $name The name of the connector will be displayed within the user interfaces that the merchant is interacting with.
     *
     * @return $this
     */
    public function setName($name)
    {
        if ((mb_strlen($name) > 100)) {
            throw new \InvalidArgumentException('invalid length for $name when calling PaymentAppConnectorCreationRequest., must be smaller than or equal to 100.');
        }

        $this->container['name'] = $name;

        return $this;
    }
    

    /**
     * Gets payment_page_endpoint
     *
     * @return string
     */
    public function getPaymentPageEndpoint()
    {
        return $this->container['payment_page_endpoint'];
    }

    /**
     * Sets payment_page_endpoint
     *
     * @param string $payment_page_endpoint The payment page endpoint URL will be invoked by the buyer to carry out the authorization of the payment.
     *
     * @return $this
     */
    public function setPaymentPageEndpoint($payment_page_endpoint)
    {
        $this->container['payment_page_endpoint'] = $payment_page_endpoint;

        return $this;
    }
    

    /**
     * Gets processor_external_id
     *
     * @return string
     */
    public function getProcessorExternalId()
    {
        return $this->container['processor_external_id'];
    }

    /**
     * Sets processor_external_id
     *
     * @param string $processor_external_id The external ID of the processor identifies the processor to which this connector belongs to. The processor cannot be changed once it has been set on a connector.
     *
     * @return $this
     */
    public function setProcessorExternalId($processor_external_id)
    {
        if ((mb_strlen($processor_external_id) > 40)) {
            throw new \InvalidArgumentException('invalid length for $processor_external_id when calling PaymentAppConnectorCreationRequest., must be smaller than or equal to 40.');
        }
        if ((mb_strlen($processor_external_id) < 1)) {
            throw new \InvalidArgumentException('invalid length for $processor_external_id when calling PaymentAppConnectorCreationRequest., must be bigger than or equal to 1.');
        }

        $this->container['processor_external_id'] = $processor_external_id;

        return $this;
    }
    

    /**
     * Gets refund_configuration
     *
     * @return \Wallee\Sdk\Model\PaymentAppRefundConfigurationCreate
     */
    public function getRefundConfiguration()
    {
        return $this->container['refund_configuration'];
    }

    /**
     * Sets refund_configuration
     *
     * @param \Wallee\Sdk\Model\PaymentAppRefundConfigurationCreate $refund_configuration The refund configuration allows the connector to support refunds on transactions. In case no refund configuration is provided the connector will not support refunds.
     *
     * @return $this
     */
    public function setRefundConfiguration($refund_configuration)
    {
        $this->container['refund_configuration'] = $refund_configuration;

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


