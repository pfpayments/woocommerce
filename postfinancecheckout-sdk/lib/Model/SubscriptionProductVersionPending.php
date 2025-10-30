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
 * SubscriptionProductVersionPending model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class SubscriptionProductVersionPending implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'SubscriptionProductVersion.Pending';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'id' => 'int',
        'version' => 'int',
        'billing_cycle' => 'string',
        'comment' => 'string',
        'default_currency' => 'string',
        'enabled_currencies' => 'string[]',
        'minimal_number_of_periods' => 'int',
        'name' => 'map[string,string]',
        'number_of_notice_periods' => 'int',
        'product' => 'int',
        'state' => '\Wallee\Sdk\Model\SubscriptionProductVersionState',
        'tax_calculation' => '\Wallee\Sdk\Model\TaxCalculation'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'id' => 'int64',
        'version' => 'int64',
        'billing_cycle' => null,
        'comment' => null,
        'default_currency' => null,
        'enabled_currencies' => null,
        'minimal_number_of_periods' => 'int32',
        'name' => null,
        'number_of_notice_periods' => 'int32',
        'product' => 'int64',
        'state' => null,
        'tax_calculation' => null
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'id' => 'id',
        'version' => 'version',
        'billing_cycle' => 'billingCycle',
        'comment' => 'comment',
        'default_currency' => 'defaultCurrency',
        'enabled_currencies' => 'enabledCurrencies',
        'minimal_number_of_periods' => 'minimalNumberOfPeriods',
        'name' => 'name',
        'number_of_notice_periods' => 'numberOfNoticePeriods',
        'product' => 'product',
        'state' => 'state',
        'tax_calculation' => 'taxCalculation'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'id' => 'setId',
        'version' => 'setVersion',
        'billing_cycle' => 'setBillingCycle',
        'comment' => 'setComment',
        'default_currency' => 'setDefaultCurrency',
        'enabled_currencies' => 'setEnabledCurrencies',
        'minimal_number_of_periods' => 'setMinimalNumberOfPeriods',
        'name' => 'setName',
        'number_of_notice_periods' => 'setNumberOfNoticePeriods',
        'product' => 'setProduct',
        'state' => 'setState',
        'tax_calculation' => 'setTaxCalculation'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'id' => 'getId',
        'version' => 'getVersion',
        'billing_cycle' => 'getBillingCycle',
        'comment' => 'getComment',
        'default_currency' => 'getDefaultCurrency',
        'enabled_currencies' => 'getEnabledCurrencies',
        'minimal_number_of_periods' => 'getMinimalNumberOfPeriods',
        'name' => 'getName',
        'number_of_notice_periods' => 'getNumberOfNoticePeriods',
        'product' => 'getProduct',
        'state' => 'getState',
        'tax_calculation' => 'getTaxCalculation'
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
        
        $this->container['id'] = isset($data['id']) ? $data['id'] : null;
        
        $this->container['version'] = isset($data['version']) ? $data['version'] : null;
        
        $this->container['billing_cycle'] = isset($data['billing_cycle']) ? $data['billing_cycle'] : null;
        
        $this->container['comment'] = isset($data['comment']) ? $data['comment'] : null;
        
        $this->container['default_currency'] = isset($data['default_currency']) ? $data['default_currency'] : null;
        
        $this->container['enabled_currencies'] = isset($data['enabled_currencies']) ? $data['enabled_currencies'] : null;
        
        $this->container['minimal_number_of_periods'] = isset($data['minimal_number_of_periods']) ? $data['minimal_number_of_periods'] : null;
        
        $this->container['name'] = isset($data['name']) ? $data['name'] : null;
        
        $this->container['number_of_notice_periods'] = isset($data['number_of_notice_periods']) ? $data['number_of_notice_periods'] : null;
        
        $this->container['product'] = isset($data['product']) ? $data['product'] : null;
        
        $this->container['state'] = isset($data['state']) ? $data['state'] : null;
        
        $this->container['tax_calculation'] = isset($data['tax_calculation']) ? $data['tax_calculation'] : null;
        
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['id'] === null) {
            $invalidProperties[] = "'id' can't be null";
        }
        if ($this->container['version'] === null) {
            $invalidProperties[] = "'version' can't be null";
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
     * @param int $id The ID is the primary key of the entity. The ID identifies the entity uniquely.
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->container['id'] = $id;

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
     * @param int $version The version number indicates the version of the entity. The version is incremented whenever the entity is changed.
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->container['version'] = $version;

        return $this;
    }
    

    /**
     * Gets billing_cycle
     *
     * @return string
     */
    public function getBillingCycle()
    {
        return $this->container['billing_cycle'];
    }

    /**
     * Sets billing_cycle
     *
     * @param string $billing_cycle The recurring period of time, typically monthly or annually, for which a subscriber is charged.
     *
     * @return $this
     */
    public function setBillingCycle($billing_cycle)
    {
        $this->container['billing_cycle'] = $billing_cycle;

        return $this;
    }
    

    /**
     * Gets comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->container['comment'];
    }

    /**
     * Sets comment
     *
     * @param string $comment A comment that describes the product version and why it was created. It is not disclosed to the subscriber.
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->container['comment'] = $comment;

        return $this;
    }
    

    /**
     * Gets default_currency
     *
     * @return string
     */
    public function getDefaultCurrency()
    {
        return $this->container['default_currency'];
    }

    /**
     * Sets default_currency
     *
     * @param string $default_currency The three-letter code (ISO 4217 format) of the product version's default currency.
     *
     * @return $this
     */
    public function setDefaultCurrency($default_currency)
    {
        $this->container['default_currency'] = $default_currency;

        return $this;
    }
    

    /**
     * Gets enabled_currencies
     *
     * @return string[]
     */
    public function getEnabledCurrencies()
    {
        return $this->container['enabled_currencies'];
    }

    /**
     * Sets enabled_currencies
     *
     * @param string[] $enabled_currencies The three-letter codes (ISO 4217 format) of the currencies that the product version supports.
     *
     * @return $this
     */
    public function setEnabledCurrencies($enabled_currencies)
    {
        $this->container['enabled_currencies'] = $enabled_currencies;

        return $this;
    }
    

    /**
     * Gets minimal_number_of_periods
     *
     * @return int
     */
    public function getMinimalNumberOfPeriods()
    {
        return $this->container['minimal_number_of_periods'];
    }

    /**
     * Sets minimal_number_of_periods
     *
     * @param int $minimal_number_of_periods The minimum number of periods the subscription will run before it can be terminated.
     *
     * @return $this
     */
    public function setMinimalNumberOfPeriods($minimal_number_of_periods)
    {
        $this->container['minimal_number_of_periods'] = $minimal_number_of_periods;

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
     * @param map[string,string] $name The localized name of the product that is displayed to the customer.
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->container['name'] = $name;

        return $this;
    }
    

    /**
     * Gets number_of_notice_periods
     *
     * @return int
     */
    public function getNumberOfNoticePeriods()
    {
        return $this->container['number_of_notice_periods'];
    }

    /**
     * Sets number_of_notice_periods
     *
     * @param int $number_of_notice_periods The number of periods the subscription will keep running after its termination was requested.
     *
     * @return $this
     */
    public function setNumberOfNoticePeriods($number_of_notice_periods)
    {
        $this->container['number_of_notice_periods'] = $number_of_notice_periods;

        return $this;
    }
    

    /**
     * Gets product
     *
     * @return int
     */
    public function getProduct()
    {
        return $this->container['product'];
    }

    /**
     * Sets product
     *
     * @param int $product The product that the version belongs to.
     *
     * @return $this
     */
    public function setProduct($product)
    {
        $this->container['product'] = $product;

        return $this;
    }
    

    /**
     * Gets state
     *
     * @return \Wallee\Sdk\Model\SubscriptionProductVersionState
     */
    public function getState()
    {
        return $this->container['state'];
    }

    /**
     * Sets state
     *
     * @param \Wallee\Sdk\Model\SubscriptionProductVersionState $state The object's current state.
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->container['state'] = $state;

        return $this;
    }
    

    /**
     * Gets tax_calculation
     *
     * @return \Wallee\Sdk\Model\TaxCalculation
     */
    public function getTaxCalculation()
    {
        return $this->container['tax_calculation'];
    }

    /**
     * Sets tax_calculation
     *
     * @param \Wallee\Sdk\Model\TaxCalculation $tax_calculation The way taxes are calculated for fees.
     *
     * @return $this
     */
    public function setTaxCalculation($tax_calculation)
    {
        $this->container['tax_calculation'] = $tax_calculation;

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


