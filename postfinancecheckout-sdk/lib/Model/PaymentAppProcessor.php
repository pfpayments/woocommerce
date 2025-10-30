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
 * PaymentAppProcessor model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class PaymentAppProcessor implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'PaymentAppProcessor';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'configured_environment' => '\Wallee\Sdk\Model\ChargeAttemptEnvironment',
        'created_on' => '\DateTime',
        'documentation_url' => 'string',
        'external_id' => 'string',
        'id' => 'int',
        'installation_id' => 'int',
        'linked_space_id' => 'int',
        'name' => 'string',
        'processor_configuration' => '\Wallee\Sdk\Model\PaymentProcessorConfiguration',
        'production_mode_url' => 'string',
        'state' => '\Wallee\Sdk\Model\PaymentAppProcessorState',
        'svg_icon' => 'string',
        'updated_on' => '\DateTime',
        'usable_in_production' => 'bool',
        'usable_in_production_since' => '\DateTime',
        'version' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'configured_environment' => null,
        'created_on' => 'date-time',
        'documentation_url' => null,
        'external_id' => null,
        'id' => 'int64',
        'installation_id' => 'int64',
        'linked_space_id' => 'int64',
        'name' => null,
        'processor_configuration' => null,
        'production_mode_url' => null,
        'state' => null,
        'svg_icon' => null,
        'updated_on' => 'date-time',
        'usable_in_production' => null,
        'usable_in_production_since' => 'date-time',
        'version' => 'int32'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'configured_environment' => 'configuredEnvironment',
        'created_on' => 'createdOn',
        'documentation_url' => 'documentationUrl',
        'external_id' => 'externalId',
        'id' => 'id',
        'installation_id' => 'installationId',
        'linked_space_id' => 'linkedSpaceId',
        'name' => 'name',
        'processor_configuration' => 'processorConfiguration',
        'production_mode_url' => 'productionModeUrl',
        'state' => 'state',
        'svg_icon' => 'svgIcon',
        'updated_on' => 'updatedOn',
        'usable_in_production' => 'usableInProduction',
        'usable_in_production_since' => 'usableInProductionSince',
        'version' => 'version'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'configured_environment' => 'setConfiguredEnvironment',
        'created_on' => 'setCreatedOn',
        'documentation_url' => 'setDocumentationUrl',
        'external_id' => 'setExternalId',
        'id' => 'setId',
        'installation_id' => 'setInstallationId',
        'linked_space_id' => 'setLinkedSpaceId',
        'name' => 'setName',
        'processor_configuration' => 'setProcessorConfiguration',
        'production_mode_url' => 'setProductionModeUrl',
        'state' => 'setState',
        'svg_icon' => 'setSvgIcon',
        'updated_on' => 'setUpdatedOn',
        'usable_in_production' => 'setUsableInProduction',
        'usable_in_production_since' => 'setUsableInProductionSince',
        'version' => 'setVersion'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'configured_environment' => 'getConfiguredEnvironment',
        'created_on' => 'getCreatedOn',
        'documentation_url' => 'getDocumentationUrl',
        'external_id' => 'getExternalId',
        'id' => 'getId',
        'installation_id' => 'getInstallationId',
        'linked_space_id' => 'getLinkedSpaceId',
        'name' => 'getName',
        'processor_configuration' => 'getProcessorConfiguration',
        'production_mode_url' => 'getProductionModeUrl',
        'state' => 'getState',
        'svg_icon' => 'getSvgIcon',
        'updated_on' => 'getUpdatedOn',
        'usable_in_production' => 'getUsableInProduction',
        'usable_in_production_since' => 'getUsableInProductionSince',
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
        
        $this->container['configured_environment'] = isset($data['configured_environment']) ? $data['configured_environment'] : null;
        
        $this->container['created_on'] = isset($data['created_on']) ? $data['created_on'] : null;
        
        $this->container['documentation_url'] = isset($data['documentation_url']) ? $data['documentation_url'] : null;
        
        $this->container['external_id'] = isset($data['external_id']) ? $data['external_id'] : null;
        
        $this->container['id'] = isset($data['id']) ? $data['id'] : null;
        
        $this->container['installation_id'] = isset($data['installation_id']) ? $data['installation_id'] : null;
        
        $this->container['linked_space_id'] = isset($data['linked_space_id']) ? $data['linked_space_id'] : null;
        
        $this->container['name'] = isset($data['name']) ? $data['name'] : null;
        
        $this->container['processor_configuration'] = isset($data['processor_configuration']) ? $data['processor_configuration'] : null;
        
        $this->container['production_mode_url'] = isset($data['production_mode_url']) ? $data['production_mode_url'] : null;
        
        $this->container['state'] = isset($data['state']) ? $data['state'] : null;
        
        $this->container['svg_icon'] = isset($data['svg_icon']) ? $data['svg_icon'] : null;
        
        $this->container['updated_on'] = isset($data['updated_on']) ? $data['updated_on'] : null;
        
        $this->container['usable_in_production'] = isset($data['usable_in_production']) ? $data['usable_in_production'] : null;
        
        $this->container['usable_in_production_since'] = isset($data['usable_in_production_since']) ? $data['usable_in_production_since'] : null;
        
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

        if (!is_null($this->container['svg_icon']) && (mb_strlen($this->container['svg_icon']) > 10000)) {
            $invalidProperties[] = "invalid value for 'svg_icon', the character length must be smaller than or equal to 10000.";
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
     * Gets configured_environment
     *
     * @return \Wallee\Sdk\Model\ChargeAttemptEnvironment
     */
    public function getConfiguredEnvironment()
    {
        return $this->container['configured_environment'];
    }

    /**
     * Sets configured_environment
     *
     * @param \Wallee\Sdk\Model\ChargeAttemptEnvironment $configured_environment 
     *
     * @return $this
     */
    public function setConfiguredEnvironment($configured_environment)
    {
        $this->container['configured_environment'] = $configured_environment;

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
     * @param \DateTime $created_on The created on date is the date when this processor has been added.
     *
     * @return $this
     */
    public function setCreatedOn($created_on)
    {
        $this->container['created_on'] = $created_on;

        return $this;
    }
    

    /**
     * Gets documentation_url
     *
     * @return string
     */
    public function getDocumentationUrl()
    {
        return $this->container['documentation_url'];
    }

    /**
     * Sets documentation_url
     *
     * @param string $documentation_url The documentation URL points to a web site that describes how to configure and use the processor.
     *
     * @return $this
     */
    public function setDocumentationUrl($documentation_url)
    {
        $this->container['documentation_url'] = $documentation_url;

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
     * @param string $external_id The external ID corresponds to the ID that was provided during creation of the processor.
     *
     * @return $this
     */
    public function setExternalId($external_id)
    {
        if (!is_null($external_id) && (mb_strlen($external_id) > 40)) {
            throw new \InvalidArgumentException('invalid length for $external_id when calling PaymentAppProcessor., must be smaller than or equal to 40.');
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
     * Gets installation_id
     *
     * @return int
     */
    public function getInstallationId()
    {
        return $this->container['installation_id'];
    }

    /**
     * Sets installation_id
     *
     * @param int $installation_id The installation ID identifies the Web App installation.
     *
     * @return $this
     */
    public function setInstallationId($installation_id)
    {
        $this->container['installation_id'] = $installation_id;

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
     * @param string $name The name of the processor will be displayed within the user interfaces that the merchant is interacting with.
     *
     * @return $this
     */
    public function setName($name)
    {
        if (!is_null($name) && (mb_strlen($name) > 100)) {
            throw new \InvalidArgumentException('invalid length for $name when calling PaymentAppProcessor., must be smaller than or equal to 100.');
        }

        $this->container['name'] = $name;

        return $this;
    }
    

    /**
     * Gets processor_configuration
     *
     * @return \Wallee\Sdk\Model\PaymentProcessorConfiguration
     */
    public function getProcessorConfiguration()
    {
        return $this->container['processor_configuration'];
    }

    /**
     * Sets processor_configuration
     *
     * @param \Wallee\Sdk\Model\PaymentProcessorConfiguration $processor_configuration This processor configuration is created as part of the app processor. Any transaction created with the processor is linked with this processor configuration.
     *
     * @return $this
     */
    public function setProcessorConfiguration($processor_configuration)
    {
        $this->container['processor_configuration'] = $processor_configuration;

        return $this;
    }
    

    /**
     * Gets production_mode_url
     *
     * @return string
     */
    public function getProductionModeUrl()
    {
        return $this->container['production_mode_url'];
    }

    /**
     * Sets production_mode_url
     *
     * @param string $production_mode_url When the user sets the processor into the production mode the user will be forwarded to this URL to configure the production environment. When no URL is provided no redirection will happen.
     *
     * @return $this
     */
    public function setProductionModeUrl($production_mode_url)
    {
        $this->container['production_mode_url'] = $production_mode_url;

        return $this;
    }
    

    /**
     * Gets state
     *
     * @return \Wallee\Sdk\Model\PaymentAppProcessorState
     */
    public function getState()
    {
        return $this->container['state'];
    }

    /**
     * Sets state
     *
     * @param \Wallee\Sdk\Model\PaymentAppProcessorState $state The object's current state.
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->container['state'] = $state;

        return $this;
    }
    

    /**
     * Gets svg_icon
     *
     * @return string
     */
    public function getSvgIcon()
    {
        return $this->container['svg_icon'];
    }

    /**
     * Sets svg_icon
     *
     * @param string $svg_icon 
     *
     * @return $this
     */
    public function setSvgIcon($svg_icon)
    {
        if (!is_null($svg_icon) && (mb_strlen($svg_icon) > 10000)) {
            throw new \InvalidArgumentException('invalid length for $svg_icon when calling PaymentAppProcessor., must be smaller than or equal to 10000.');
        }

        $this->container['svg_icon'] = $svg_icon;

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
     * @param \DateTime $updated_on The updated on date indicates when the last update on the processor occurred.
     *
     * @return $this
     */
    public function setUpdatedOn($updated_on)
    {
        $this->container['updated_on'] = $updated_on;

        return $this;
    }
    

    /**
     * Gets usable_in_production
     *
     * @return bool
     */
    public function getUsableInProduction()
    {
        return $this->container['usable_in_production'];
    }

    /**
     * Sets usable_in_production
     *
     * @param bool $usable_in_production When the processor is ready to be used for transactions in the production environment this flag is set to true.
     *
     * @return $this
     */
    public function setUsableInProduction($usable_in_production)
    {
        $this->container['usable_in_production'] = $usable_in_production;

        return $this;
    }
    

    /**
     * Gets usable_in_production_since
     *
     * @return \DateTime
     */
    public function getUsableInProductionSince()
    {
        return $this->container['usable_in_production_since'];
    }

    /**
     * Sets usable_in_production_since
     *
     * @param \DateTime $usable_in_production_since 
     *
     * @return $this
     */
    public function setUsableInProductionSince($usable_in_production_since)
    {
        $this->container['usable_in_production_since'] = $usable_in_production_since;

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


