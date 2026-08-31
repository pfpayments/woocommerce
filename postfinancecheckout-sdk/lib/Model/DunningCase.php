<?php
/**
 * PostFinance Checkout SDK
 *
 * This library allows to interact with the PostFinance Checkout payment service.
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


namespace PostFinanceCheckout\Sdk\Model;

use \ArrayAccess;
use \PostFinanceCheckout\Sdk\ObjectSerializer;

/**
 * DunningCase model
 *
 * @category    Class
 * @description 
 * @package     PostFinanceCheckout\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class DunningCase implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'DunningCase';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'canceled_on' => '\DateTime',
        'created_on' => '\DateTime',
        'derecognized_on' => '\DateTime',
        'failed_on' => '\DateTime',
        'flow' => '\PostFinanceCheckout\Sdk\Model\DunningFlow',
        'id' => 'int',
        'initial_invoice' => '\PostFinanceCheckout\Sdk\Model\TransactionInvoice',
        'linked_space_id' => 'int',
        'linked_transaction' => 'int',
        'planned_purge_date' => '\DateTime',
        'state' => '\PostFinanceCheckout\Sdk\Model\DunningCaseState',
        'succeeded_on' => '\DateTime',
        'version' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'canceled_on' => 'date-time',
        'created_on' => 'date-time',
        'derecognized_on' => 'date-time',
        'failed_on' => 'date-time',
        'flow' => null,
        'id' => 'int64',
        'initial_invoice' => null,
        'linked_space_id' => 'int64',
        'linked_transaction' => 'int64',
        'planned_purge_date' => 'date-time',
        'state' => null,
        'succeeded_on' => 'date-time',
        'version' => 'int32'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'canceled_on' => 'canceledOn',
        'created_on' => 'createdOn',
        'derecognized_on' => 'derecognizedOn',
        'failed_on' => 'failedOn',
        'flow' => 'flow',
        'id' => 'id',
        'initial_invoice' => 'initialInvoice',
        'linked_space_id' => 'linkedSpaceId',
        'linked_transaction' => 'linkedTransaction',
        'planned_purge_date' => 'plannedPurgeDate',
        'state' => 'state',
        'succeeded_on' => 'succeededOn',
        'version' => 'version'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'canceled_on' => 'setCanceledOn',
        'created_on' => 'setCreatedOn',
        'derecognized_on' => 'setDerecognizedOn',
        'failed_on' => 'setFailedOn',
        'flow' => 'setFlow',
        'id' => 'setId',
        'initial_invoice' => 'setInitialInvoice',
        'linked_space_id' => 'setLinkedSpaceId',
        'linked_transaction' => 'setLinkedTransaction',
        'planned_purge_date' => 'setPlannedPurgeDate',
        'state' => 'setState',
        'succeeded_on' => 'setSucceededOn',
        'version' => 'setVersion'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'canceled_on' => 'getCanceledOn',
        'created_on' => 'getCreatedOn',
        'derecognized_on' => 'getDerecognizedOn',
        'failed_on' => 'getFailedOn',
        'flow' => 'getFlow',
        'id' => 'getId',
        'initial_invoice' => 'getInitialInvoice',
        'linked_space_id' => 'getLinkedSpaceId',
        'linked_transaction' => 'getLinkedTransaction',
        'planned_purge_date' => 'getPlannedPurgeDate',
        'state' => 'getState',
        'succeeded_on' => 'getSucceededOn',
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
     * @param mixed[]|null $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(?array $data = null)
    {
        
        $this->container['canceled_on'] = isset($data['canceled_on']) ? $data['canceled_on'] : null;
        
        $this->container['created_on'] = isset($data['created_on']) ? $data['created_on'] : null;
        
        $this->container['derecognized_on'] = isset($data['derecognized_on']) ? $data['derecognized_on'] : null;
        
        $this->container['failed_on'] = isset($data['failed_on']) ? $data['failed_on'] : null;
        
        $this->container['flow'] = isset($data['flow']) ? $data['flow'] : null;
        
        $this->container['id'] = isset($data['id']) ? $data['id'] : null;
        
        $this->container['initial_invoice'] = isset($data['initial_invoice']) ? $data['initial_invoice'] : null;
        
        $this->container['linked_space_id'] = isset($data['linked_space_id']) ? $data['linked_space_id'] : null;
        
        $this->container['linked_transaction'] = isset($data['linked_transaction']) ? $data['linked_transaction'] : null;
        
        $this->container['planned_purge_date'] = isset($data['planned_purge_date']) ? $data['planned_purge_date'] : null;
        
        $this->container['state'] = isset($data['state']) ? $data['state'] : null;
        
        $this->container['succeeded_on'] = isset($data['succeeded_on']) ? $data['succeeded_on'] : null;
        
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
     * Gets canceled_on
     *
     * @return \DateTime
     */
    public function getCanceledOn()
    {
        return $this->container['canceled_on'];
    }

    /**
     * Sets canceled_on
     *
     * @param \DateTime $canceled_on 
     *
     * @return $this
     */
    public function setCanceledOn($canceled_on)
    {
        $this->container['canceled_on'] = $canceled_on;

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
     * Gets derecognized_on
     *
     * @return \DateTime
     */
    public function getDerecognizedOn()
    {
        return $this->container['derecognized_on'];
    }

    /**
     * Sets derecognized_on
     *
     * @param \DateTime $derecognized_on 
     *
     * @return $this
     */
    public function setDerecognizedOn($derecognized_on)
    {
        $this->container['derecognized_on'] = $derecognized_on;

        return $this;
    }
    

    /**
     * Gets failed_on
     *
     * @return \DateTime
     */
    public function getFailedOn()
    {
        return $this->container['failed_on'];
    }

    /**
     * Sets failed_on
     *
     * @param \DateTime $failed_on 
     *
     * @return $this
     */
    public function setFailedOn($failed_on)
    {
        $this->container['failed_on'] = $failed_on;

        return $this;
    }
    

    /**
     * Gets flow
     *
     * @return \PostFinanceCheckout\Sdk\Model\DunningFlow
     */
    public function getFlow()
    {
        return $this->container['flow'];
    }

    /**
     * Sets flow
     *
     * @param \PostFinanceCheckout\Sdk\Model\DunningFlow $flow 
     *
     * @return $this
     */
    public function setFlow($flow)
    {
        $this->container['flow'] = $flow;

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
     * Gets initial_invoice
     *
     * @return \PostFinanceCheckout\Sdk\Model\TransactionInvoice
     */
    public function getInitialInvoice()
    {
        return $this->container['initial_invoice'];
    }

    /**
     * Sets initial_invoice
     *
     * @param \PostFinanceCheckout\Sdk\Model\TransactionInvoice $initial_invoice 
     *
     * @return $this
     */
    public function setInitialInvoice($initial_invoice)
    {
        $this->container['initial_invoice'] = $initial_invoice;

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
     * Gets linked_transaction
     *
     * @return int
     */
    public function getLinkedTransaction()
    {
        return $this->container['linked_transaction'];
    }

    /**
     * Sets linked_transaction
     *
     * @param int $linked_transaction The payment transaction this object is linked to.
     *
     * @return $this
     */
    public function setLinkedTransaction($linked_transaction)
    {
        $this->container['linked_transaction'] = $linked_transaction;

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
     * Gets state
     *
     * @return \PostFinanceCheckout\Sdk\Model\DunningCaseState
     */
    public function getState()
    {
        return $this->container['state'];
    }

    /**
     * Sets state
     *
     * @param \PostFinanceCheckout\Sdk\Model\DunningCaseState $state The object's current state.
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->container['state'] = $state;

        return $this;
    }
    

    /**
     * Gets succeeded_on
     *
     * @return \DateTime
     */
    public function getSucceededOn()
    {
        return $this->container['succeeded_on'];
    }

    /**
     * Sets succeeded_on
     *
     * @param \DateTime $succeeded_on 
     *
     * @return $this
     */
    public function setSucceededOn($succeeded_on)
    {
        $this->container['succeeded_on'] = $succeeded_on;

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


