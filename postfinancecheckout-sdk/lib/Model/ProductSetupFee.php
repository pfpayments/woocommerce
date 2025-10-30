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
 * ProductSetupFee model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class ProductSetupFee implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'ProductSetupFee';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'component' => '\Wallee\Sdk\Model\SubscriptionProductComponent',
        'description' => 'map[string,string]',
        'id' => 'int',
        'linked_space_id' => 'int',
        'name' => 'map[string,string]',
        'on_downgrade_credited_amount' => '\Wallee\Sdk\Model\PersistableCurrencyAmount[]',
        'on_upgrade_credited_amount' => '\Wallee\Sdk\Model\PersistableCurrencyAmount[]',
        'setup_fee' => '\Wallee\Sdk\Model\PersistableCurrencyAmount[]',
        'type' => '\Wallee\Sdk\Model\ProductFeeType',
        'version' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'component' => null,
        'description' => null,
        'id' => 'int64',
        'linked_space_id' => 'int64',
        'name' => null,
        'on_downgrade_credited_amount' => null,
        'on_upgrade_credited_amount' => null,
        'setup_fee' => null,
        'type' => null,
        'version' => 'int32'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'component' => 'component',
        'description' => 'description',
        'id' => 'id',
        'linked_space_id' => 'linkedSpaceId',
        'name' => 'name',
        'on_downgrade_credited_amount' => 'onDowngradeCreditedAmount',
        'on_upgrade_credited_amount' => 'onUpgradeCreditedAmount',
        'setup_fee' => 'setupFee',
        'type' => 'type',
        'version' => 'version'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'component' => 'setComponent',
        'description' => 'setDescription',
        'id' => 'setId',
        'linked_space_id' => 'setLinkedSpaceId',
        'name' => 'setName',
        'on_downgrade_credited_amount' => 'setOnDowngradeCreditedAmount',
        'on_upgrade_credited_amount' => 'setOnUpgradeCreditedAmount',
        'setup_fee' => 'setSetupFee',
        'type' => 'setType',
        'version' => 'setVersion'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'component' => 'getComponent',
        'description' => 'getDescription',
        'id' => 'getId',
        'linked_space_id' => 'getLinkedSpaceId',
        'name' => 'getName',
        'on_downgrade_credited_amount' => 'getOnDowngradeCreditedAmount',
        'on_upgrade_credited_amount' => 'getOnUpgradeCreditedAmount',
        'setup_fee' => 'getSetupFee',
        'type' => 'getType',
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
        
        $this->container['component'] = isset($data['component']) ? $data['component'] : null;
        
        $this->container['description'] = isset($data['description']) ? $data['description'] : null;
        
        $this->container['id'] = isset($data['id']) ? $data['id'] : null;
        
        $this->container['linked_space_id'] = isset($data['linked_space_id']) ? $data['linked_space_id'] : null;
        
        $this->container['name'] = isset($data['name']) ? $data['name'] : null;
        
        $this->container['on_downgrade_credited_amount'] = isset($data['on_downgrade_credited_amount']) ? $data['on_downgrade_credited_amount'] : null;
        
        $this->container['on_upgrade_credited_amount'] = isset($data['on_upgrade_credited_amount']) ? $data['on_upgrade_credited_amount'] : null;
        
        $this->container['setup_fee'] = isset($data['setup_fee']) ? $data['setup_fee'] : null;
        
        $this->container['type'] = isset($data['type']) ? $data['type'] : null;
        
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
     * Gets component
     *
     * @return \Wallee\Sdk\Model\SubscriptionProductComponent
     */
    public function getComponent()
    {
        return $this->container['component'];
    }

    /**
     * Sets component
     *
     * @param \Wallee\Sdk\Model\SubscriptionProductComponent $component The product component that the fee belongs to.
     *
     * @return $this
     */
    public function setComponent($component)
    {
        $this->container['component'] = $component;

        return $this;
    }
    

    /**
     * Gets description
     *
     * @return map[string,string]
     */
    public function getDescription()
    {
        return $this->container['description'];
    }

    /**
     * Sets description
     *
     * @param map[string,string] $description The localized description of the fee that is displayed to the customer.
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->container['description'] = $description;

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
     * @return map[string,string]
     */
    public function getName()
    {
        return $this->container['name'];
    }

    /**
     * Sets name
     *
     * @param map[string,string] $name The localized name of the fee that is displayed to the customer.
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->container['name'] = $name;

        return $this;
    }
    

    /**
     * Gets on_downgrade_credited_amount
     *
     * @return \Wallee\Sdk\Model\PersistableCurrencyAmount[]
     */
    public function getOnDowngradeCreditedAmount()
    {
        return $this->container['on_downgrade_credited_amount'];
    }

    /**
     * Sets on_downgrade_credited_amount
     *
     * @param \Wallee\Sdk\Model\PersistableCurrencyAmount[] $on_downgrade_credited_amount The amount charged to the customer when a subscription is downgraded.
     *
     * @return $this
     */
    public function setOnDowngradeCreditedAmount($on_downgrade_credited_amount)
    {
        $this->container['on_downgrade_credited_amount'] = $on_downgrade_credited_amount;

        return $this;
    }
    

    /**
     * Gets on_upgrade_credited_amount
     *
     * @return \Wallee\Sdk\Model\PersistableCurrencyAmount[]
     */
    public function getOnUpgradeCreditedAmount()
    {
        return $this->container['on_upgrade_credited_amount'];
    }

    /**
     * Sets on_upgrade_credited_amount
     *
     * @param \Wallee\Sdk\Model\PersistableCurrencyAmount[] $on_upgrade_credited_amount The amount charged to the customer when a subscription is upgraded.
     *
     * @return $this
     */
    public function setOnUpgradeCreditedAmount($on_upgrade_credited_amount)
    {
        $this->container['on_upgrade_credited_amount'] = $on_upgrade_credited_amount;

        return $this;
    }
    

    /**
     * Gets setup_fee
     *
     * @return \Wallee\Sdk\Model\PersistableCurrencyAmount[]
     */
    public function getSetupFee()
    {
        return $this->container['setup_fee'];
    }

    /**
     * Sets setup_fee
     *
     * @param \Wallee\Sdk\Model\PersistableCurrencyAmount[] $setup_fee The amount charged to the customer once when they subscribe to a subscription.
     *
     * @return $this
     */
    public function setSetupFee($setup_fee)
    {
        $this->container['setup_fee'] = $setup_fee;

        return $this;
    }
    

    /**
     * Gets type
     *
     * @return \Wallee\Sdk\Model\ProductFeeType
     */
    public function getType()
    {
        return $this->container['type'];
    }

    /**
     * Sets type
     *
     * @param \Wallee\Sdk\Model\ProductFeeType $type The type of the fee.
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->container['type'] = $type;

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


