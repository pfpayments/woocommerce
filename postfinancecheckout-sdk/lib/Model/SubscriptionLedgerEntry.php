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
 * SubscriptionLedgerEntry model
 *
 * @category    Class
 * @description The subscription ledger entry represents a single change on the subscription balance.
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class SubscriptionLedgerEntry implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'SubscriptionLedgerEntry';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'aggregated_tax_rate' => 'float',
        'amount_excluding_tax' => 'float',
        'amount_including_tax' => 'float',
        'component_reference_name' => 'string',
        'component_reference_sku' => 'string',
        'created_by' => 'int',
        'created_on' => '\DateTime',
        'discount_including_tax' => 'float',
        'external_id' => 'string',
        'fee_type' => '\Wallee\Sdk\Model\ProductFeeType',
        'id' => 'int',
        'linked_space_id' => 'int',
        'planned_purge_date' => '\DateTime',
        'pro_rata_calculated' => 'bool',
        'quantity' => 'float',
        'state' => '\Wallee\Sdk\Model\SubscriptionLedgerEntryState',
        'subscription_metric_id' => 'int',
        'subscription_version' => 'int',
        'tax_amount' => 'float',
        'taxes' => '\Wallee\Sdk\Model\Tax[]',
        'title' => 'string',
        'version' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'aggregated_tax_rate' => null,
        'amount_excluding_tax' => null,
        'amount_including_tax' => null,
        'component_reference_name' => null,
        'component_reference_sku' => null,
        'created_by' => 'int64',
        'created_on' => 'date-time',
        'discount_including_tax' => null,
        'external_id' => null,
        'fee_type' => null,
        'id' => 'int64',
        'linked_space_id' => 'int64',
        'planned_purge_date' => 'date-time',
        'pro_rata_calculated' => null,
        'quantity' => null,
        'state' => null,
        'subscription_metric_id' => 'int64',
        'subscription_version' => 'int64',
        'tax_amount' => null,
        'taxes' => null,
        'title' => null,
        'version' => 'int32'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'aggregated_tax_rate' => 'aggregatedTaxRate',
        'amount_excluding_tax' => 'amountExcludingTax',
        'amount_including_tax' => 'amountIncludingTax',
        'component_reference_name' => 'componentReferenceName',
        'component_reference_sku' => 'componentReferenceSku',
        'created_by' => 'createdBy',
        'created_on' => 'createdOn',
        'discount_including_tax' => 'discountIncludingTax',
        'external_id' => 'externalId',
        'fee_type' => 'feeType',
        'id' => 'id',
        'linked_space_id' => 'linkedSpaceId',
        'planned_purge_date' => 'plannedPurgeDate',
        'pro_rata_calculated' => 'proRataCalculated',
        'quantity' => 'quantity',
        'state' => 'state',
        'subscription_metric_id' => 'subscriptionMetricId',
        'subscription_version' => 'subscriptionVersion',
        'tax_amount' => 'taxAmount',
        'taxes' => 'taxes',
        'title' => 'title',
        'version' => 'version'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'aggregated_tax_rate' => 'setAggregatedTaxRate',
        'amount_excluding_tax' => 'setAmountExcludingTax',
        'amount_including_tax' => 'setAmountIncludingTax',
        'component_reference_name' => 'setComponentReferenceName',
        'component_reference_sku' => 'setComponentReferenceSku',
        'created_by' => 'setCreatedBy',
        'created_on' => 'setCreatedOn',
        'discount_including_tax' => 'setDiscountIncludingTax',
        'external_id' => 'setExternalId',
        'fee_type' => 'setFeeType',
        'id' => 'setId',
        'linked_space_id' => 'setLinkedSpaceId',
        'planned_purge_date' => 'setPlannedPurgeDate',
        'pro_rata_calculated' => 'setProRataCalculated',
        'quantity' => 'setQuantity',
        'state' => 'setState',
        'subscription_metric_id' => 'setSubscriptionMetricId',
        'subscription_version' => 'setSubscriptionVersion',
        'tax_amount' => 'setTaxAmount',
        'taxes' => 'setTaxes',
        'title' => 'setTitle',
        'version' => 'setVersion'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'aggregated_tax_rate' => 'getAggregatedTaxRate',
        'amount_excluding_tax' => 'getAmountExcludingTax',
        'amount_including_tax' => 'getAmountIncludingTax',
        'component_reference_name' => 'getComponentReferenceName',
        'component_reference_sku' => 'getComponentReferenceSku',
        'created_by' => 'getCreatedBy',
        'created_on' => 'getCreatedOn',
        'discount_including_tax' => 'getDiscountIncludingTax',
        'external_id' => 'getExternalId',
        'fee_type' => 'getFeeType',
        'id' => 'getId',
        'linked_space_id' => 'getLinkedSpaceId',
        'planned_purge_date' => 'getPlannedPurgeDate',
        'pro_rata_calculated' => 'getProRataCalculated',
        'quantity' => 'getQuantity',
        'state' => 'getState',
        'subscription_metric_id' => 'getSubscriptionMetricId',
        'subscription_version' => 'getSubscriptionVersion',
        'tax_amount' => 'getTaxAmount',
        'taxes' => 'getTaxes',
        'title' => 'getTitle',
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
        
        $this->container['aggregated_tax_rate'] = isset($data['aggregated_tax_rate']) ? $data['aggregated_tax_rate'] : null;
        
        $this->container['amount_excluding_tax'] = isset($data['amount_excluding_tax']) ? $data['amount_excluding_tax'] : null;
        
        $this->container['amount_including_tax'] = isset($data['amount_including_tax']) ? $data['amount_including_tax'] : null;
        
        $this->container['component_reference_name'] = isset($data['component_reference_name']) ? $data['component_reference_name'] : null;
        
        $this->container['component_reference_sku'] = isset($data['component_reference_sku']) ? $data['component_reference_sku'] : null;
        
        $this->container['created_by'] = isset($data['created_by']) ? $data['created_by'] : null;
        
        $this->container['created_on'] = isset($data['created_on']) ? $data['created_on'] : null;
        
        $this->container['discount_including_tax'] = isset($data['discount_including_tax']) ? $data['discount_including_tax'] : null;
        
        $this->container['external_id'] = isset($data['external_id']) ? $data['external_id'] : null;
        
        $this->container['fee_type'] = isset($data['fee_type']) ? $data['fee_type'] : null;
        
        $this->container['id'] = isset($data['id']) ? $data['id'] : null;
        
        $this->container['linked_space_id'] = isset($data['linked_space_id']) ? $data['linked_space_id'] : null;
        
        $this->container['planned_purge_date'] = isset($data['planned_purge_date']) ? $data['planned_purge_date'] : null;
        
        $this->container['pro_rata_calculated'] = isset($data['pro_rata_calculated']) ? $data['pro_rata_calculated'] : null;
        
        $this->container['quantity'] = isset($data['quantity']) ? $data['quantity'] : null;
        
        $this->container['state'] = isset($data['state']) ? $data['state'] : null;
        
        $this->container['subscription_metric_id'] = isset($data['subscription_metric_id']) ? $data['subscription_metric_id'] : null;
        
        $this->container['subscription_version'] = isset($data['subscription_version']) ? $data['subscription_version'] : null;
        
        $this->container['tax_amount'] = isset($data['tax_amount']) ? $data['tax_amount'] : null;
        
        $this->container['taxes'] = isset($data['taxes']) ? $data['taxes'] : null;
        
        $this->container['title'] = isset($data['title']) ? $data['title'] : null;
        
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

        if (!is_null($this->container['component_reference_sku']) && (mb_strlen($this->container['component_reference_sku']) > 100)) {
            $invalidProperties[] = "invalid value for 'component_reference_sku', the character length must be smaller than or equal to 100.";
        }

        if (!is_null($this->container['title']) && (mb_strlen($this->container['title']) > 150)) {
            $invalidProperties[] = "invalid value for 'title', the character length must be smaller than or equal to 150.";
        }

        if (!is_null($this->container['title']) && (mb_strlen($this->container['title']) < 1)) {
            $invalidProperties[] = "invalid value for 'title', the character length must be bigger than or equal to 1.";
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
     * Gets aggregated_tax_rate
     *
     * @return float
     */
    public function getAggregatedTaxRate()
    {
        return $this->container['aggregated_tax_rate'];
    }

    /**
     * Sets aggregated_tax_rate
     *
     * @param float $aggregated_tax_rate The total tax rate applied to the ledger entry, calculated from the rates of all tax lines.
     *
     * @return $this
     */
    public function setAggregatedTaxRate($aggregated_tax_rate)
    {
        $this->container['aggregated_tax_rate'] = $aggregated_tax_rate;

        return $this;
    }
    

    /**
     * Gets amount_excluding_tax
     *
     * @return float
     */
    public function getAmountExcludingTax()
    {
        return $this->container['amount_excluding_tax'];
    }

    /**
     * Sets amount_excluding_tax
     *
     * @param float $amount_excluding_tax The leger entry's amount with discounts applied, excluding taxes.
     *
     * @return $this
     */
    public function setAmountExcludingTax($amount_excluding_tax)
    {
        $this->container['amount_excluding_tax'] = $amount_excluding_tax;

        return $this;
    }
    

    /**
     * Gets amount_including_tax
     *
     * @return float
     */
    public function getAmountIncludingTax()
    {
        return $this->container['amount_including_tax'];
    }

    /**
     * Sets amount_including_tax
     *
     * @param float $amount_including_tax The leger entry's amount with discounts applied, including taxes.
     *
     * @return $this
     */
    public function setAmountIncludingTax($amount_including_tax)
    {
        $this->container['amount_including_tax'] = $amount_including_tax;

        return $this;
    }
    

    /**
     * Gets component_reference_name
     *
     * @return string
     */
    public function getComponentReferenceName()
    {
        return $this->container['component_reference_name'];
    }

    /**
     * Sets component_reference_name
     *
     * @param string $component_reference_name 
     *
     * @return $this
     */
    public function setComponentReferenceName($component_reference_name)
    {
        $this->container['component_reference_name'] = $component_reference_name;

        return $this;
    }
    

    /**
     * Gets component_reference_sku
     *
     * @return string
     */
    public function getComponentReferenceSku()
    {
        return $this->container['component_reference_sku'];
    }

    /**
     * Sets component_reference_sku
     *
     * @param string $component_reference_sku 
     *
     * @return $this
     */
    public function setComponentReferenceSku($component_reference_sku)
    {
        if (!is_null($component_reference_sku) && (mb_strlen($component_reference_sku) > 100)) {
            throw new \InvalidArgumentException('invalid length for $component_reference_sku when calling SubscriptionLedgerEntry., must be smaller than or equal to 100.');
        }

        $this->container['component_reference_sku'] = $component_reference_sku;

        return $this;
    }
    

    /**
     * Gets created_by
     *
     * @return int
     */
    public function getCreatedBy()
    {
        return $this->container['created_by'];
    }

    /**
     * Sets created_by
     *
     * @param int $created_by The ID of the user the ledger entry was created by.
     *
     * @return $this
     */
    public function setCreatedBy($created_by)
    {
        $this->container['created_by'] = $created_by;

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
     * @param \DateTime $created_on The date and time when the object was created.
     *
     * @return $this
     */
    public function setCreatedOn($created_on)
    {
        $this->container['created_on'] = $created_on;

        return $this;
    }
    

    /**
     * Gets discount_including_tax
     *
     * @return float
     */
    public function getDiscountIncludingTax()
    {
        return $this->container['discount_including_tax'];
    }

    /**
     * Sets discount_including_tax
     *
     * @param float $discount_including_tax The discount allocated to the ledger entry, including taxes.
     *
     * @return $this
     */
    public function setDiscountIncludingTax($discount_including_tax)
    {
        $this->container['discount_including_tax'] = $discount_including_tax;

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
     * @param string $external_id A client generated nonce which identifies the entity to be created. Subsequent creation requests with the same external ID will not create new entities but return the initially created entity instead.
     *
     * @return $this
     */
    public function setExternalId($external_id)
    {
        $this->container['external_id'] = $external_id;

        return $this;
    }
    

    /**
     * Gets fee_type
     *
     * @return \Wallee\Sdk\Model\ProductFeeType
     */
    public function getFeeType()
    {
        return $this->container['fee_type'];
    }

    /**
     * Sets fee_type
     *
     * @param \Wallee\Sdk\Model\ProductFeeType $fee_type 
     *
     * @return $this
     */
    public function setFeeType($fee_type)
    {
        $this->container['fee_type'] = $fee_type;

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
     * Gets planned_purge_date
     *
     * @return \DateTime
     */
    public function getPlannedPurgeDate()
    {
        return $this->container['planned_purge_date'];
    }

    /**
     * Sets planned_purge_date
     *
     * @param \DateTime $planned_purge_date The date and time when the object is planned to be permanently removed. If the value is empty, the object will not be removed.
     *
     * @return $this
     */
    public function setPlannedPurgeDate($planned_purge_date)
    {
        $this->container['planned_purge_date'] = $planned_purge_date;

        return $this;
    }
    

    /**
     * Gets pro_rata_calculated
     *
     * @return bool
     */
    public function getProRataCalculated()
    {
        return $this->container['pro_rata_calculated'];
    }

    /**
     * Sets pro_rata_calculated
     *
     * @param bool $pro_rata_calculated 
     *
     * @return $this
     */
    public function setProRataCalculated($pro_rata_calculated)
    {
        $this->container['pro_rata_calculated'] = $pro_rata_calculated;

        return $this;
    }
    

    /**
     * Gets quantity
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->container['quantity'];
    }

    /**
     * Sets quantity
     *
     * @param float $quantity The number of items that were consumed.
     *
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->container['quantity'] = $quantity;

        return $this;
    }
    

    /**
     * Gets state
     *
     * @return \Wallee\Sdk\Model\SubscriptionLedgerEntryState
     */
    public function getState()
    {
        return $this->container['state'];
    }

    /**
     * Sets state
     *
     * @param \Wallee\Sdk\Model\SubscriptionLedgerEntryState $state The object's current state.
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->container['state'] = $state;

        return $this;
    }
    

    /**
     * Gets subscription_metric_id
     *
     * @return int
     */
    public function getSubscriptionMetricId()
    {
        return $this->container['subscription_metric_id'];
    }

    /**
     * Sets subscription_metric_id
     *
     * @param int $subscription_metric_id 
     *
     * @return $this
     */
    public function setSubscriptionMetricId($subscription_metric_id)
    {
        $this->container['subscription_metric_id'] = $subscription_metric_id;

        return $this;
    }
    

    /**
     * Gets subscription_version
     *
     * @return int
     */
    public function getSubscriptionVersion()
    {
        return $this->container['subscription_version'];
    }

    /**
     * Sets subscription_version
     *
     * @param int $subscription_version The subscription version that the ledger entry belongs to.
     *
     * @return $this
     */
    public function setSubscriptionVersion($subscription_version)
    {
        $this->container['subscription_version'] = $subscription_version;

        return $this;
    }
    

    /**
     * Gets tax_amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->container['tax_amount'];
    }

    /**
     * Sets tax_amount
     *
     * @param float $tax_amount The sum of all taxes applied to the ledger entry.
     *
     * @return $this
     */
    public function setTaxAmount($tax_amount)
    {
        $this->container['tax_amount'] = $tax_amount;

        return $this;
    }
    

    /**
     * Gets taxes
     *
     * @return \Wallee\Sdk\Model\Tax[]
     */
    public function getTaxes()
    {
        return $this->container['taxes'];
    }

    /**
     * Sets taxes
     *
     * @param \Wallee\Sdk\Model\Tax[] $taxes A set of tax lines, each of which specifies a tax applied to the ledger entry.
     *
     * @return $this
     */
    public function setTaxes($taxes)
    {
        $this->container['taxes'] = $taxes;

        return $this;
    }
    

    /**
     * Gets title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->container['title'];
    }

    /**
     * Sets title
     *
     * @param string $title The title that indicates what the ledger entry is about.
     *
     * @return $this
     */
    public function setTitle($title)
    {
        if (!is_null($title) && (mb_strlen($title) > 150)) {
            throw new \InvalidArgumentException('invalid length for $title when calling SubscriptionLedgerEntry., must be smaller than or equal to 150.');
        }
        if (!is_null($title) && (mb_strlen($title) < 1)) {
            throw new \InvalidArgumentException('invalid length for $title when calling SubscriptionLedgerEntry., must be bigger than or equal to 1.');
        }

        $this->container['title'] = $title;

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


