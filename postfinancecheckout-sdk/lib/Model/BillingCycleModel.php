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
 * BillingCycleModel model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class BillingCycleModel implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'BillingCycleModel';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'billing_cycle_type' => '\Wallee\Sdk\Model\BillingCycleType',
        'customization' => '\Wallee\Sdk\Model\BillingDayCustomization',
        'day_of_month' => 'int',
        'month' => '\Wallee\Sdk\Model\DisplayableMonth',
        'number_of_periods' => 'int',
        'weekly_day' => '\Wallee\Sdk\Model\DisplayableDayOfWeek'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'billing_cycle_type' => null,
        'customization' => null,
        'day_of_month' => 'int32',
        'month' => null,
        'number_of_periods' => 'int32',
        'weekly_day' => null
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'billing_cycle_type' => 'billingCycleType',
        'customization' => 'customization',
        'day_of_month' => 'dayOfMonth',
        'month' => 'month',
        'number_of_periods' => 'numberOfPeriods',
        'weekly_day' => 'weeklyDay'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'billing_cycle_type' => 'setBillingCycleType',
        'customization' => 'setCustomization',
        'day_of_month' => 'setDayOfMonth',
        'month' => 'setMonth',
        'number_of_periods' => 'setNumberOfPeriods',
        'weekly_day' => 'setWeeklyDay'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'billing_cycle_type' => 'getBillingCycleType',
        'customization' => 'getCustomization',
        'day_of_month' => 'getDayOfMonth',
        'month' => 'getMonth',
        'number_of_periods' => 'getNumberOfPeriods',
        'weekly_day' => 'getWeeklyDay'
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
        
        $this->container['billing_cycle_type'] = isset($data['billing_cycle_type']) ? $data['billing_cycle_type'] : null;
        
        $this->container['customization'] = isset($data['customization']) ? $data['customization'] : null;
        
        $this->container['day_of_month'] = isset($data['day_of_month']) ? $data['day_of_month'] : null;
        
        $this->container['month'] = isset($data['month']) ? $data['month'] : null;
        
        $this->container['number_of_periods'] = isset($data['number_of_periods']) ? $data['number_of_periods'] : null;
        
        $this->container['weekly_day'] = isset($data['weekly_day']) ? $data['weekly_day'] : null;
        
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['billing_cycle_type'] === null) {
            $invalidProperties[] = "'billing_cycle_type' can't be null";
        }
        if ($this->container['number_of_periods'] === null) {
            $invalidProperties[] = "'number_of_periods' can't be null";
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
     * Gets billing_cycle_type
     *
     * @return \Wallee\Sdk\Model\BillingCycleType
     */
    public function getBillingCycleType()
    {
        return $this->container['billing_cycle_type'];
    }

    /**
     * Sets billing_cycle_type
     *
     * @param \Wallee\Sdk\Model\BillingCycleType $billing_cycle_type 
     *
     * @return $this
     */
    public function setBillingCycleType($billing_cycle_type)
    {
        $this->container['billing_cycle_type'] = $billing_cycle_type;

        return $this;
    }
    

    /**
     * Gets customization
     *
     * @return \Wallee\Sdk\Model\BillingDayCustomization
     */
    public function getCustomization()
    {
        return $this->container['customization'];
    }

    /**
     * Sets customization
     *
     * @param \Wallee\Sdk\Model\BillingDayCustomization $customization 
     *
     * @return $this
     */
    public function setCustomization($customization)
    {
        $this->container['customization'] = $customization;

        return $this;
    }
    

    /**
     * Gets day_of_month
     *
     * @return int
     */
    public function getDayOfMonth()
    {
        return $this->container['day_of_month'];
    }

    /**
     * Sets day_of_month
     *
     * @param int $day_of_month 
     *
     * @return $this
     */
    public function setDayOfMonth($day_of_month)
    {
        $this->container['day_of_month'] = $day_of_month;

        return $this;
    }
    

    /**
     * Gets month
     *
     * @return \Wallee\Sdk\Model\DisplayableMonth
     */
    public function getMonth()
    {
        return $this->container['month'];
    }

    /**
     * Sets month
     *
     * @param \Wallee\Sdk\Model\DisplayableMonth $month 
     *
     * @return $this
     */
    public function setMonth($month)
    {
        $this->container['month'] = $month;

        return $this;
    }
    

    /**
     * Gets number_of_periods
     *
     * @return int
     */
    public function getNumberOfPeriods()
    {
        return $this->container['number_of_periods'];
    }

    /**
     * Sets number_of_periods
     *
     * @param int $number_of_periods Billing Cycle type multiplied by Number of Periods defines billing cycle duration, e.g. 3 months. Monthly types require 1-12; weekly and yearly types require 1-9 periods; and daily types require 1-30.
     *
     * @return $this
     */
    public function setNumberOfPeriods($number_of_periods)
    {
        $this->container['number_of_periods'] = $number_of_periods;

        return $this;
    }
    

    /**
     * Gets weekly_day
     *
     * @return \Wallee\Sdk\Model\DisplayableDayOfWeek
     */
    public function getWeeklyDay()
    {
        return $this->container['weekly_day'];
    }

    /**
     * Sets weekly_day
     *
     * @param \Wallee\Sdk\Model\DisplayableDayOfWeek $weekly_day 
     *
     * @return $this
     */
    public function setWeeklyDay($weekly_day)
    {
        $this->container['weekly_day'] = $weekly_day;

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


