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
 * PaymentAppConnector model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class PaymentAppConnector implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'PaymentAppConnector';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'authorization_timeout' => 'string',
        'completion_configuration' => '\Wallee\Sdk\Model\PaymentAppCompletionConfiguration',
        'connector_configuration' => '\Wallee\Sdk\Model\PaymentConnectorConfiguration',
        'created_on' => '\DateTime',
        'external_id' => 'string',
        'id' => 'int',
        'linked_space_id' => 'int',
        'name' => 'string',
        'payment_page_endpoint' => 'string',
        'processor' => '\Wallee\Sdk\Model\PaymentAppProcessor',
        'refund_configuration' => '\Wallee\Sdk\Model\PaymentAppRefundConfiguration',
        'state' => '\Wallee\Sdk\Model\PaymentAppConnectorState',
        'updated_on' => '\DateTime',
        'version' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'authorization_timeout' => null,
        'completion_configuration' => null,
        'connector_configuration' => null,
        'created_on' => 'date-time',
        'external_id' => null,
        'id' => 'int64',
        'linked_space_id' => 'int64',
        'name' => null,
        'payment_page_endpoint' => null,
        'processor' => null,
        'refund_configuration' => null,
        'state' => null,
        'updated_on' => 'date-time',
        'version' => 'int32'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'authorization_timeout' => 'authorizationTimeout',
        'completion_configuration' => 'completionConfiguration',
        'connector_configuration' => 'connectorConfiguration',
        'created_on' => 'createdOn',
        'external_id' => 'externalId',
        'id' => 'id',
        'linked_space_id' => 'linkedSpaceId',
        'name' => 'name',
        'payment_page_endpoint' => 'paymentPageEndpoint',
        'processor' => 'processor',
        'refund_configuration' => 'refundConfiguration',
        'state' => 'state',
        'updated_on' => 'updatedOn',
        'version' => 'version'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'authorization_timeout' => 'setAuthorizationTimeout',
        'completion_configuration' => 'setCompletionConfiguration',
        'connector_configuration' => 'setConnectorConfiguration',
        'created_on' => 'setCreatedOn',
        'external_id' => 'setExternalId',
        'id' => 'setId',
        'linked_space_id' => 'setLinkedSpaceId',
        'name' => 'setName',
        'payment_page_endpoint' => 'setPaymentPageEndpoint',
        'processor' => 'setProcessor',
        'refund_configuration' => 'setRefundConfiguration',
        'state' => 'setState',
        'updated_on' => 'setUpdatedOn',
        'version' => 'setVersion'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'authorization_timeout' => 'getAuthorizationTimeout',
        'completion_configuration' => 'getCompletionConfiguration',
        'connector_configuration' => 'getConnectorConfiguration',
        'created_on' => 'getCreatedOn',
        'external_id' => 'getExternalId',
        'id' => 'getId',
        'linked_space_id' => 'getLinkedSpaceId',
        'name' => 'getName',
        'payment_page_endpoint' => 'getPaymentPageEndpoint',
        'processor' => 'getProcessor',
        'refund_configuration' => 'getRefundConfiguration',
        'state' => 'getState',
        'updated_on' => 'getUpdatedOn',
        'version' => 'getVersion'
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
        
        $this->container['authorization_timeout'] = isset($data['authorization_timeout']) ? $data['authorization_timeout'] : null;
        
        $this->container['completion_configuration'] = isset($data['completion_configuration']) ? $data['completion_configuration'] : null;
        
        $this->container['connector_configuration'] = isset($data['connector_configuration']) ? $data['connector_configuration'] : null;
        
        $this->container['created_on'] = isset($data['created_on']) ? $data['created_on'] : null;
        
        $this->container['external_id'] = isset($data['external_id']) ? $data['external_id'] : null;
        
        $this->container['id'] = isset($data['id']) ? $data['id'] : null;
        
        $this->container['linked_space_id'] = isset($data['linked_space_id']) ? $data['linked_space_id'] : null;
        
        $this->container['name'] = isset($data['name']) ? $data['name'] : null;
        
        $this->container['payment_page_endpoint'] = isset($data['payment_page_endpoint']) ? $data['payment_page_endpoint'] : null;
        
        $this->container['processor'] = isset($data['processor']) ? $data['processor'] : null;
        
        $this->container['refund_configuration'] = isset($data['refund_configuration']) ? $data['refund_configuration'] : null;
        
        $this->container['state'] = isset($data['state']) ? $data['state'] : null;
        
        $this->container['updated_on'] = isset($data['updated_on']) ? $data['updated_on'] : null;
        
        $this->container['version'] = isset($data['version']) ? $data['version'] : null;
        
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if (!is_null($this->container['external_id']) && (mb_strlen($this->container['external_id']) > 40)) {
            $invalidProperties[] = "invalid value for 'external_id', the character length must be smaller than or equal to 40.";
        }

        if (!is_null($this->container['name']) && (mb_strlen($this->container['name']) > 100)) {
            $invalidProperties[] = "invalid value for 'name', the character length must be smaller than or equal to 100.";
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
     * Gets authorization_timeout
     *
     * @return string
     */
    public function getAuthorizationTimeout()
    {
        return $this->container['authorization_timeout'];
    }

    /**
     * Sets authorization_timeout
     *
     * @param string $authorization_timeout 
     *
     * @return $this
     */
    public function setAuthorizationTimeout($authorization_timeout)
    {
        $this->container['authorization_timeout'] = $authorization_timeout;

        return $this;
    }
    

    /**
     * Gets completion_configuration
     *
     * @return \Wallee\Sdk\Model\PaymentAppCompletionConfiguration
     */
    public function getCompletionConfiguration()
    {
        return $this->container['completion_configuration'];
    }

    /**
     * Sets completion_configuration
     *
     * @param \Wallee\Sdk\Model\PaymentAppCompletionConfiguration $completion_configuration The completion configuration defines how the deferred completion is processed. If it is not present it means that deferred completion is not supported by this connector.
     *
     * @return $this
     */
    public function setCompletionConfiguration($completion_configuration)
    {
        $this->container['completion_configuration'] = $completion_configuration;

        return $this;
    }
    

    /**
     * Gets connector_configuration
     *
     * @return \Wallee\Sdk\Model\PaymentConnectorConfiguration
     */
    public function getConnectorConfiguration()
    {
        return $this->container['connector_configuration'];
    }

    /**
     * Sets connector_configuration
     *
     * @param \Wallee\Sdk\Model\PaymentConnectorConfiguration $connector_configuration The connector configuration references the configuration that was created as part of this connector within the space. The connector configuration is referenced within transactions created with this connector.
     *
     * @return $this
     */
    public function setConnectorConfiguration($connector_configuration)
    {
        $this->container['connector_configuration'] = $connector_configuration;

        return $this;
    }
    

    /**
     * Gets created_on
     *
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->container['created_on'];
    }

    /**
     * Sets created_on
     *
     * @param \DateTime $created_on The created on date indicates when the connector was added.
     *
     * @return $this
     */
    public function setCreatedOn($created_on)
    {
        $this->container['created_on'] = $created_on;

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
     * @param string $external_id The external ID corresponds to the ID provided during inserting of the processor.
     *
     * @return $this
     */
    public function setExternalId($external_id)
    {
        if (!is_null($external_id) && (mb_strlen($external_id) > 40)) {
            throw new \InvalidArgumentException('invalid length for $external_id when calling PaymentAppConnector., must be smaller than or equal to 40.');
        }

        $this->container['external_id'] = $external_id;

        return $this;
    }
    

    /**
     * Gets id
     *
     * @return int
     */
    public function getId()
    {
        return $this->container['id'];
    }

    /**
     * Sets id
     *
     * @param int $id A unique identifier for the object.
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->container['id'] = $id;

        return $this;
    }
    

    /**
     * Gets linked_space_id
     *
     * @return int
     */
    public function getLinkedSpaceId()
    {
        return $this->container['linked_space_id'];
    }

    /**
     * Sets linked_space_id
     *
     * @param int $linked_space_id The ID of the space this object belongs to.
     *
     * @return $this
     */
    public function setLinkedSpaceId($linked_space_id)
    {
        $this->container['linked_space_id'] = $linked_space_id;

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
        if (!is_null($name) && (mb_strlen($name) > 100)) {
            throw new \InvalidArgumentException('invalid length for $name when calling PaymentAppConnector., must be smaller than or equal to 100.');
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
     * @param string $payment_page_endpoint The payment page endpoint is invoked to process the transaction. The endpoint is defined by the external service provider.
     *
     * @return $this
     */
    public function setPaymentPageEndpoint($payment_page_endpoint)
    {
        $this->container['payment_page_endpoint'] = $payment_page_endpoint;

        return $this;
    }
    

    /**
     * Gets processor
     *
     * @return \Wallee\Sdk\Model\PaymentAppProcessor
     */
    public function getProcessor()
    {
        return $this->container['processor'];
    }

    /**
     * Sets processor
     *
     * @param \Wallee\Sdk\Model\PaymentAppProcessor $processor The processor references the app processor to which this connector belongs to. The relationship is established during the creation of the connector.
     *
     * @return $this
     */
    public function setProcessor($processor)
    {
        $this->container['processor'] = $processor;

        return $this;
    }
    

    /**
     * Gets refund_configuration
     *
     * @return \Wallee\Sdk\Model\PaymentAppRefundConfiguration
     */
    public function getRefundConfiguration()
    {
        return $this->container['refund_configuration'];
    }

    /**
     * Sets refund_configuration
     *
     * @param \Wallee\Sdk\Model\PaymentAppRefundConfiguration $refund_configuration The refund configuration defines how refunds are processed. If it is not present it means that refunds are not supported by this connector.
     *
     * @return $this
     */
    public function setRefundConfiguration($refund_configuration)
    {
        $this->container['refund_configuration'] = $refund_configuration;

        return $this;
    }
    

    /**
     * Gets state
     *
     * @return \Wallee\Sdk\Model\PaymentAppConnectorState
     */
    public function getState()
    {
        return $this->container['state'];
    }

    /**
     * Sets state
     *
     * @param \Wallee\Sdk\Model\PaymentAppConnectorState $state The object's current state.
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->container['state'] = $state;

        return $this;
    }
    

    /**
     * Gets updated_on
     *
     * @return \DateTime
     */
    public function getUpdatedOn()
    {
        return $this->container['updated_on'];
    }

    /**
     * Sets updated_on
     *
     * @param \DateTime $updated_on The updated on date indicates when the last time the connector was updated on.
     *
     * @return $this
     */
    public function setUpdatedOn($updated_on)
    {
        $this->container['updated_on'] = $updated_on;

        return $this;
    }
    

    /**
     * Gets version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->container['version'];
    }

    /**
     * Sets version
     *
     * @param int $version The version is used for optimistic locking and incremented whenever the object is updated.
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->container['version'] = $version;

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


