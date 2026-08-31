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
 * DunningCaseLevel model
 *
 * @category    Class
 * @description 
 * @package     PostFinanceCheckout\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class DunningCaseLevel implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'DunningCaseLevel';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'canceled_on' => '\DateTime',
        'created_on' => '\DateTime',
        'dunning_case' => '\PostFinanceCheckout\Sdk\Model\DunningCase',
        'environment' => '\PostFinanceCheckout\Sdk\Model\Environment',
        'failed_on' => '\DateTime',
        'flow_level' => 'int',
        'id' => 'int',
        'invoice' => 'int',
        'language' => 'string',
        'linked_space_id' => 'int',
        'linked_transaction' => 'int',
        'most_recent_level' => 'bool',
        'planned_purge_date' => '\DateTime',
        'state' => '\PostFinanceCheckout\Sdk\Model\DunningCaseLevelState',
        'succeeded_on' => '\DateTime',
        'timeout_on' => '\DateTime',
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
        'dunning_case' => null,
        'environment' => null,
        'failed_on' => 'date-time',
        'flow_level' => 'int64',
        'id' => 'int64',
        'invoice' => 'int64',
        'language' => null,
        'linked_space_id' => 'int64',
        'linked_transaction' => 'int64',
        'most_recent_level' => null,
        'planned_purge_date' => 'date-time',
        'state' => null,
        'succeeded_on' => 'date-time',
        'timeout_on' => 'date-time',
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
        'dunning_case' => 'dunningCase',
        'environment' => 'environment',
        'failed_on' => 'failedOn',
        'flow_level' => 'flowLevel',
        'id' => 'id',
        'invoice' => 'invoice',
        'language' => 'language',
        'linked_space_id' => 'linkedSpaceId',
        'linked_transaction' => 'linkedTransaction',
        'most_recent_level' => 'mostRecentLevel',
        'planned_purge_date' => 'plannedPurgeDate',
        'state' => 'state',
        'succeeded_on' => 'succeededOn',
        'timeout_on' => 'timeoutOn',
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
        'dunning_case' => 'setDunningCase',
        'environment' => 'setEnvironment',
        'failed_on' => 'setFailedOn',
        'flow_level' => 'setFlowLevel',
        'id' => 'setId',
        'invoice' => 'setInvoice',
        'language' => 'setLanguage',
        'linked_space_id' => 'setLinkedSpaceId',
        'linked_transaction' => 'setLinkedTransaction',
        'most_recent_level' => 'setMostRecentLevel',
        'planned_purge_date' => 'setPlannedPurgeDate',
        'state' => 'setState',
        'succeeded_on' => 'setSucceededOn',
        'timeout_on' => 'setTimeoutOn',
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
        'dunning_case' => 'getDunningCase',
        'environment' => 'getEnvironment',
        'failed_on' => 'getFailedOn',
        'flow_level' => 'getFlowLevel',
        'id' => 'getId',
        'invoice' => 'getInvoice',
        'language' => 'getLanguage',
        'linked_space_id' => 'getLinkedSpaceId',
        'linked_transaction' => 'getLinkedTransaction',
        'most_recent_level' => 'getMostRecentLevel',
        'planned_purge_date' => 'getPlannedPurgeDate',
        'state' => 'getState',
        'succeeded_on' => 'getSucceededOn',
        'timeout_on' => 'getTimeoutOn',
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
        
        $this->container['dunning_case'] = isset($data['dunning_case']) ? $data['dunning_case'] : null;
        
        $this->container['environment'] = isset($data['environment']) ? $data['environment'] : null;
        
        $this->container['failed_on'] = isset($data['failed_on']) ? $data['failed_on'] : null;
        
        $this->container['flow_level'] = isset($data['flow_level']) ? $data['flow_level'] : null;
        
        $this->container['id'] = isset($data['id']) ? $data['id'] : null;
        
        $this->container['invoice'] = isset($data['invoice']) ? $data['invoice'] : null;
        
        $this->container['language'] = isset($data['language']) ? $data['language'] : null;
        
        $this->container['linked_space_id'] = isset($data['linked_space_id']) ? $data['linked_space_id'] : null;
        
        $this->container['linked_transaction'] = isset($data['linked_transaction']) ? $data['linked_transaction'] : null;
        
        $this->container['most_recent_level'] = isset($data['most_recent_level']) ? $data['most_recent_level'] : null;
        
        $this->container['planned_purge_date'] = isset($data['planned_purge_date']) ? $data['planned_purge_date'] : null;
        
        $this->container['state'] = isset($data['state']) ? $data['state'] : null;
        
        $this->container['succeeded_on'] = isset($data['succeeded_on']) ? $data['succeeded_on'] : null;
        
        $this->container['timeout_on'] = isset($data['timeout_on']) ? $data['timeout_on'] : null;
        
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
     * Gets dunning_case
     *
     * @return \PostFinanceCheckout\Sdk\Model\DunningCase
     */
    public function getDunningCase()
    {
        return $this->container['dunning_case'];
    }

    /**
     * Sets dunning_case
     *
     * @param \PostFinanceCheckout\Sdk\Model\DunningCase $dunning_case 
     *
     * @return $this
     */
    public function setDunningCase($dunning_case)
    {
        $this->container['dunning_case'] = $dunning_case;

        return $this;
    }
    

    /**
     * Gets environment
     *
     * @return \PostFinanceCheckout\Sdk\Model\Environment
     */
    public function getEnvironment()
    {
        return $this->container['environment'];
    }

    /**
     * Sets environment
     *
     * @param \PostFinanceCheckout\Sdk\Model\Environment $environment The environment used when rendering resources.
     *
     * @return $this
     */
    public function setEnvironment($environment)
    {
        $this->container['environment'] = $environment;

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
     * Gets flow_level
     *
     * @return int
     */
    public function getFlowLevel()
    {
        return $this->container['flow_level'];
    }

    /**
     * Sets flow_level
     *
     * @param int $flow_level 
     *
     * @return $this
     */
    public function setFlowLevel($flow_level)
    {
        $this->container['flow_level'] = $flow_level;

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
     * Gets invoice
     *
     * @return int
     */
    public function getInvoice()
    {
        return $this->container['invoice'];
    }

    /**
     * Sets invoice
     *
     * @param int $invoice 
     *
     * @return $this
     */
    public function setInvoice($invoice)
    {
        $this->container['invoice'] = $invoice;

        return $this;
    }
    

    /**
     * Gets language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->container['language'];
    }

    /**
     * Sets language
     *
     * @param string $language The language that is linked to the object.
     *
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->container['language'] = $language;

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
     * Gets most_recent_level
     *
     * @return bool
     */
    public function getMostRecentLevel()
    {
        return $this->container['most_recent_level'];
    }

    /**
     * Sets most_recent_level
     *
     * @param bool $most_recent_level 
     *
     * @return $this
     */
    public function setMostRecentLevel($most_recent_level)
    {
        $this->container['most_recent_level'] = $most_recent_level;

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
     * @return \PostFinanceCheckout\Sdk\Model\DunningCaseLevelState
     */
    public function getState()
    {
        return $this->container['state'];
    }

    /**
     * Sets state
     *
     * @param \PostFinanceCheckout\Sdk\Model\DunningCaseLevelState $state The object's current state.
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
     * Gets timeout_on
     *
     * @return \DateTime
     */
    public function getTimeoutOn()
    {
        return $this->container['timeout_on'];
    }

    /**
     * Sets timeout_on
     *
     * @param \DateTime $timeout_on 
     *
     * @return $this
     */
    public function setTimeoutOn($timeout_on)
    {
        $this->container['timeout_on'] = $timeout_on;

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


