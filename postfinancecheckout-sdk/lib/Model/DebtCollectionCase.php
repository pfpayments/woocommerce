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
 * DebtCollectionCase model
 *
 * @category    Class
 * @description The debt collection case represents a try to collect the money from the debtor.
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class DebtCollectionCase implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'DebtCollectionCase';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'amount' => 'float',
        'billing_address' => '\Wallee\Sdk\Model\Address',
        'closed_on' => '\DateTime',
        'collector_configuration' => '\Wallee\Sdk\Model\DebtCollectorConfiguration',
        'contract_date' => '\DateTime',
        'created_on' => '\DateTime',
        'creator' => 'int',
        'currency' => 'string',
        'due_date' => '\DateTime',
        'environment' => '\Wallee\Sdk\Model\DebtCollectionEnvironment',
        'external_id' => 'string',
        'failed_on' => '\DateTime',
        'failure_reason' => '\Wallee\Sdk\Model\FailureReason',
        'id' => 'int',
        'labels' => '\Wallee\Sdk\Model\Label[]',
        'language' => 'string',
        'line_items' => '\Wallee\Sdk\Model\LineItem[]',
        'linked_space_id' => 'int',
        'next_attempt_on' => '\DateTime',
        'planned_purge_date' => '\DateTime',
        'processing_started_on' => '\DateTime',
        'processing_timeout_on' => '\DateTime',
        'reference' => 'string',
        'review_started_on' => '\DateTime',
        'reviewed_on' => '\DateTime',
        'reviewer' => 'int',
        'source' => '\Wallee\Sdk\Model\DebtCollectionCaseSource',
        'source_entity_id' => 'int',
        'space_view_id' => 'int',
        'state' => '\Wallee\Sdk\Model\DebtCollectionCaseState',
        'version' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'amount' => null,
        'billing_address' => null,
        'closed_on' => 'date-time',
        'collector_configuration' => null,
        'contract_date' => 'date-time',
        'created_on' => 'date-time',
        'creator' => 'int64',
        'currency' => null,
        'due_date' => 'date-time',
        'environment' => null,
        'external_id' => null,
        'failed_on' => 'date-time',
        'failure_reason' => null,
        'id' => 'int64',
        'labels' => null,
        'language' => null,
        'line_items' => null,
        'linked_space_id' => 'int64',
        'next_attempt_on' => 'date-time',
        'planned_purge_date' => 'date-time',
        'processing_started_on' => 'date-time',
        'processing_timeout_on' => 'date-time',
        'reference' => null,
        'review_started_on' => 'date-time',
        'reviewed_on' => 'date-time',
        'reviewer' => 'int64',
        'source' => null,
        'source_entity_id' => 'int64',
        'space_view_id' => 'int64',
        'state' => null,
        'version' => 'int32'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'amount' => 'amount',
        'billing_address' => 'billingAddress',
        'closed_on' => 'closedOn',
        'collector_configuration' => 'collectorConfiguration',
        'contract_date' => 'contractDate',
        'created_on' => 'createdOn',
        'creator' => 'creator',
        'currency' => 'currency',
        'due_date' => 'dueDate',
        'environment' => 'environment',
        'external_id' => 'externalId',
        'failed_on' => 'failedOn',
        'failure_reason' => 'failureReason',
        'id' => 'id',
        'labels' => 'labels',
        'language' => 'language',
        'line_items' => 'lineItems',
        'linked_space_id' => 'linkedSpaceId',
        'next_attempt_on' => 'nextAttemptOn',
        'planned_purge_date' => 'plannedPurgeDate',
        'processing_started_on' => 'processingStartedOn',
        'processing_timeout_on' => 'processingTimeoutOn',
        'reference' => 'reference',
        'review_started_on' => 'reviewStartedOn',
        'reviewed_on' => 'reviewedOn',
        'reviewer' => 'reviewer',
        'source' => 'source',
        'source_entity_id' => 'sourceEntityId',
        'space_view_id' => 'spaceViewId',
        'state' => 'state',
        'version' => 'version'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'amount' => 'setAmount',
        'billing_address' => 'setBillingAddress',
        'closed_on' => 'setClosedOn',
        'collector_configuration' => 'setCollectorConfiguration',
        'contract_date' => 'setContractDate',
        'created_on' => 'setCreatedOn',
        'creator' => 'setCreator',
        'currency' => 'setCurrency',
        'due_date' => 'setDueDate',
        'environment' => 'setEnvironment',
        'external_id' => 'setExternalId',
        'failed_on' => 'setFailedOn',
        'failure_reason' => 'setFailureReason',
        'id' => 'setId',
        'labels' => 'setLabels',
        'language' => 'setLanguage',
        'line_items' => 'setLineItems',
        'linked_space_id' => 'setLinkedSpaceId',
        'next_attempt_on' => 'setNextAttemptOn',
        'planned_purge_date' => 'setPlannedPurgeDate',
        'processing_started_on' => 'setProcessingStartedOn',
        'processing_timeout_on' => 'setProcessingTimeoutOn',
        'reference' => 'setReference',
        'review_started_on' => 'setReviewStartedOn',
        'reviewed_on' => 'setReviewedOn',
        'reviewer' => 'setReviewer',
        'source' => 'setSource',
        'source_entity_id' => 'setSourceEntityId',
        'space_view_id' => 'setSpaceViewId',
        'state' => 'setState',
        'version' => 'setVersion'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'amount' => 'getAmount',
        'billing_address' => 'getBillingAddress',
        'closed_on' => 'getClosedOn',
        'collector_configuration' => 'getCollectorConfiguration',
        'contract_date' => 'getContractDate',
        'created_on' => 'getCreatedOn',
        'creator' => 'getCreator',
        'currency' => 'getCurrency',
        'due_date' => 'getDueDate',
        'environment' => 'getEnvironment',
        'external_id' => 'getExternalId',
        'failed_on' => 'getFailedOn',
        'failure_reason' => 'getFailureReason',
        'id' => 'getId',
        'labels' => 'getLabels',
        'language' => 'getLanguage',
        'line_items' => 'getLineItems',
        'linked_space_id' => 'getLinkedSpaceId',
        'next_attempt_on' => 'getNextAttemptOn',
        'planned_purge_date' => 'getPlannedPurgeDate',
        'processing_started_on' => 'getProcessingStartedOn',
        'processing_timeout_on' => 'getProcessingTimeoutOn',
        'reference' => 'getReference',
        'review_started_on' => 'getReviewStartedOn',
        'reviewed_on' => 'getReviewedOn',
        'reviewer' => 'getReviewer',
        'source' => 'getSource',
        'source_entity_id' => 'getSourceEntityId',
        'space_view_id' => 'getSpaceViewId',
        'state' => 'getState',
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
        
        $this->container['amount'] = isset($data['amount']) ? $data['amount'] : null;
        
        $this->container['billing_address'] = isset($data['billing_address']) ? $data['billing_address'] : null;
        
        $this->container['closed_on'] = isset($data['closed_on']) ? $data['closed_on'] : null;
        
        $this->container['collector_configuration'] = isset($data['collector_configuration']) ? $data['collector_configuration'] : null;
        
        $this->container['contract_date'] = isset($data['contract_date']) ? $data['contract_date'] : null;
        
        $this->container['created_on'] = isset($data['created_on']) ? $data['created_on'] : null;
        
        $this->container['creator'] = isset($data['creator']) ? $data['creator'] : null;
        
        $this->container['currency'] = isset($data['currency']) ? $data['currency'] : null;
        
        $this->container['due_date'] = isset($data['due_date']) ? $data['due_date'] : null;
        
        $this->container['environment'] = isset($data['environment']) ? $data['environment'] : null;
        
        $this->container['external_id'] = isset($data['external_id']) ? $data['external_id'] : null;
        
        $this->container['failed_on'] = isset($data['failed_on']) ? $data['failed_on'] : null;
        
        $this->container['failure_reason'] = isset($data['failure_reason']) ? $data['failure_reason'] : null;
        
        $this->container['id'] = isset($data['id']) ? $data['id'] : null;
        
        $this->container['labels'] = isset($data['labels']) ? $data['labels'] : null;
        
        $this->container['language'] = isset($data['language']) ? $data['language'] : null;
        
        $this->container['line_items'] = isset($data['line_items']) ? $data['line_items'] : null;
        
        $this->container['linked_space_id'] = isset($data['linked_space_id']) ? $data['linked_space_id'] : null;
        
        $this->container['next_attempt_on'] = isset($data['next_attempt_on']) ? $data['next_attempt_on'] : null;
        
        $this->container['planned_purge_date'] = isset($data['planned_purge_date']) ? $data['planned_purge_date'] : null;
        
        $this->container['processing_started_on'] = isset($data['processing_started_on']) ? $data['processing_started_on'] : null;
        
        $this->container['processing_timeout_on'] = isset($data['processing_timeout_on']) ? $data['processing_timeout_on'] : null;
        
        $this->container['reference'] = isset($data['reference']) ? $data['reference'] : null;
        
        $this->container['review_started_on'] = isset($data['review_started_on']) ? $data['review_started_on'] : null;
        
        $this->container['reviewed_on'] = isset($data['reviewed_on']) ? $data['reviewed_on'] : null;
        
        $this->container['reviewer'] = isset($data['reviewer']) ? $data['reviewer'] : null;
        
        $this->container['source'] = isset($data['source']) ? $data['source'] : null;
        
        $this->container['source_entity_id'] = isset($data['source_entity_id']) ? $data['source_entity_id'] : null;
        
        $this->container['space_view_id'] = isset($data['space_view_id']) ? $data['space_view_id'] : null;
        
        $this->container['state'] = isset($data['state']) ? $data['state'] : null;
        
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
     * Gets amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->container['amount'];
    }

    /**
     * Sets amount
     *
     * @param float $amount The sum of all unpaid item prices in the case's currency. The amount can no longer be changed once the case has been reviewed.
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->container['amount'] = $amount;

        return $this;
    }
    

    /**
     * Gets billing_address
     *
     * @return \Wallee\Sdk\Model\Address
     */
    public function getBillingAddress()
    {
        return $this->container['billing_address'];
    }

    /**
     * Sets billing_address
     *
     * @param \Wallee\Sdk\Model\Address $billing_address The billing address that identifies the debtor.
     *
     * @return $this
     */
    public function setBillingAddress($billing_address)
    {
        $this->container['billing_address'] = $billing_address;

        return $this;
    }
    

    /**
     * Gets closed_on
     *
     * @return \DateTime
     */
    public function getClosedOn()
    {
        return $this->container['closed_on'];
    }

    /**
     * Sets closed_on
     *
     * @param \DateTime $closed_on The date and time when the case was closed.
     *
     * @return $this
     */
    public function setClosedOn($closed_on)
    {
        $this->container['closed_on'] = $closed_on;

        return $this;
    }
    

    /**
     * Gets collector_configuration
     *
     * @return \Wallee\Sdk\Model\DebtCollectorConfiguration
     */
    public function getCollectorConfiguration()
    {
        return $this->container['collector_configuration'];
    }

    /**
     * Sets collector_configuration
     *
     * @param \Wallee\Sdk\Model\DebtCollectorConfiguration $collector_configuration The configuration that the case is processed with.
     *
     * @return $this
     */
    public function setCollectorConfiguration($collector_configuration)
    {
        $this->container['collector_configuration'] = $collector_configuration;

        return $this;
    }
    

    /**
     * Gets contract_date
     *
     * @return \DateTime
     */
    public function getContractDate()
    {
        return $this->container['contract_date'];
    }

    /**
     * Sets contract_date
     *
     * @param \DateTime $contract_date The date and time when the contract with the debtor was signed.
     *
     * @return $this
     */
    public function setContractDate($contract_date)
    {
        $this->container['contract_date'] = $contract_date;

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
     * Gets creator
     *
     * @return int
     */
    public function getCreator()
    {
        return $this->container['creator'];
    }

    /**
     * Sets creator
     *
     * @param int $creator The ID of the user the case was created by.
     *
     * @return $this
     */
    public function setCreator($creator)
    {
        $this->container['creator'] = $creator;

        return $this;
    }
    

    /**
     * Gets currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->container['currency'];
    }

    /**
     * Sets currency
     *
     * @param string $currency The three-letter code (ISO 4217 format) of the case's currency.
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->container['currency'] = $currency;

        return $this;
    }
    

    /**
     * Gets due_date
     *
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->container['due_date'];
    }

    /**
     * Sets due_date
     *
     * @param \DateTime $due_date The date and time when the claim was due.
     *
     * @return $this
     */
    public function setDueDate($due_date)
    {
        $this->container['due_date'] = $due_date;

        return $this;
    }
    

    /**
     * Gets environment
     *
     * @return \Wallee\Sdk\Model\DebtCollectionEnvironment
     */
    public function getEnvironment()
    {
        return $this->container['environment'];
    }

    /**
     * Sets environment
     *
     * @param \Wallee\Sdk\Model\DebtCollectionEnvironment $environment The environment in which the case is processed.
     *
     * @return $this
     */
    public function setEnvironment($environment)
    {
        $this->container['environment'] = $environment;

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
     * @param \DateTime $failed_on The date and time when the case failed.
     *
     * @return $this
     */
    public function setFailedOn($failed_on)
    {
        $this->container['failed_on'] = $failed_on;

        return $this;
    }
    

    /**
     * Gets failure_reason
     *
     * @return \Wallee\Sdk\Model\FailureReason
     */
    public function getFailureReason()
    {
        return $this->container['failure_reason'];
    }

    /**
     * Sets failure_reason
     *
     * @param \Wallee\Sdk\Model\FailureReason $failure_reason The reason for the failure of the case.
     *
     * @return $this
     */
    public function setFailureReason($failure_reason)
    {
        $this->container['failure_reason'] = $failure_reason;

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
     * Gets labels
     *
     * @return \Wallee\Sdk\Model\Label[]
     */
    public function getLabels()
    {
        return $this->container['labels'];
    }

    /**
     * Sets labels
     *
     * @param \Wallee\Sdk\Model\Label[] $labels The labels providing additional information about the object.
     *
     * @return $this
     */
    public function setLabels($labels)
    {
        $this->container['labels'] = $labels;

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
     * Gets line_items
     *
     * @return \Wallee\Sdk\Model\LineItem[]
     */
    public function getLineItems()
    {
        return $this->container['line_items'];
    }

    /**
     * Sets line_items
     *
     * @param \Wallee\Sdk\Model\LineItem[] $line_items The line items that are subject of this debt collection case.
     *
     * @return $this
     */
    public function setLineItems($line_items)
    {
        $this->container['line_items'] = $line_items;

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
     * Gets next_attempt_on
     *
     * @return \DateTime
     */
    public function getNextAttemptOn()
    {
        return $this->container['next_attempt_on'];
    }

    /**
     * Sets next_attempt_on
     *
     * @param \DateTime $next_attempt_on The date and time when the next attempt at processing the case will be made.
     *
     * @return $this
     */
    public function setNextAttemptOn($next_attempt_on)
    {
        $this->container['next_attempt_on'] = $next_attempt_on;

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
     * Gets processing_started_on
     *
     * @return \DateTime
     */
    public function getProcessingStartedOn()
    {
        return $this->container['processing_started_on'];
    }

    /**
     * Sets processing_started_on
     *
     * @param \DateTime $processing_started_on The date and time when the processing of the case was started.
     *
     * @return $this
     */
    public function setProcessingStartedOn($processing_started_on)
    {
        $this->container['processing_started_on'] = $processing_started_on;

        return $this;
    }
    

    /**
     * Gets processing_timeout_on
     *
     * @return \DateTime
     */
    public function getProcessingTimeoutOn()
    {
        return $this->container['processing_timeout_on'];
    }

    /**
     * Sets processing_timeout_on
     *
     * @param \DateTime $processing_timeout_on The date and time when the processing of the case times out.
     *
     * @return $this
     */
    public function setProcessingTimeoutOn($processing_timeout_on)
    {
        $this->container['processing_timeout_on'] = $processing_timeout_on;

        return $this;
    }
    

    /**
     * Gets reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->container['reference'];
    }

    /**
     * Sets reference
     *
     * @param string $reference A unique reference to identify the debt collection case in communication with the debtor.
     *
     * @return $this
     */
    public function setReference($reference)
    {
        $this->container['reference'] = $reference;

        return $this;
    }
    

    /**
     * Gets review_started_on
     *
     * @return \DateTime
     */
    public function getReviewStartedOn()
    {
        return $this->container['review_started_on'];
    }

    /**
     * Sets review_started_on
     *
     * @param \DateTime $review_started_on The date and time when the review of the case was started.
     *
     * @return $this
     */
    public function setReviewStartedOn($review_started_on)
    {
        $this->container['review_started_on'] = $review_started_on;

        return $this;
    }
    

    /**
     * Gets reviewed_on
     *
     * @return \DateTime
     */
    public function getReviewedOn()
    {
        return $this->container['reviewed_on'];
    }

    /**
     * Sets reviewed_on
     *
     * @param \DateTime $reviewed_on The date and time when the case was reviewed.
     *
     * @return $this
     */
    public function setReviewedOn($reviewed_on)
    {
        $this->container['reviewed_on'] = $reviewed_on;

        return $this;
    }
    

    /**
     * Gets reviewer
     *
     * @return int
     */
    public function getReviewer()
    {
        return $this->container['reviewer'];
    }

    /**
     * Sets reviewer
     *
     * @param int $reviewer The ID of the user the case was reviewed by.
     *
     * @return $this
     */
    public function setReviewer($reviewer)
    {
        $this->container['reviewer'] = $reviewer;

        return $this;
    }
    

    /**
     * Gets source
     *
     * @return \Wallee\Sdk\Model\DebtCollectionCaseSource
     */
    public function getSource()
    {
        return $this->container['source'];
    }

    /**
     * Sets source
     *
     * @param \Wallee\Sdk\Model\DebtCollectionCaseSource $source The source of the case stating the origin of the claim.
     *
     * @return $this
     */
    public function setSource($source)
    {
        $this->container['source'] = $source;

        return $this;
    }
    

    /**
     * Gets source_entity_id
     *
     * @return int
     */
    public function getSourceEntityId()
    {
        return $this->container['source_entity_id'];
    }

    /**
     * Sets source_entity_id
     *
     * @param int $source_entity_id The ID of the object that is the source of the case. Only defined if the case was created by an internal process.
     *
     * @return $this
     */
    public function setSourceEntityId($source_entity_id)
    {
        $this->container['source_entity_id'] = $source_entity_id;

        return $this;
    }
    

    /**
     * Gets space_view_id
     *
     * @return int
     */
    public function getSpaceViewId()
    {
        return $this->container['space_view_id'];
    }

    /**
     * Sets space_view_id
     *
     * @param int $space_view_id The ID of the space view this object is linked to.
     *
     * @return $this
     */
    public function setSpaceViewId($space_view_id)
    {
        $this->container['space_view_id'] = $space_view_id;

        return $this;
    }
    

    /**
     * Gets state
     *
     * @return \Wallee\Sdk\Model\DebtCollectionCaseState
     */
    public function getState()
    {
        return $this->container['state'];
    }

    /**
     * Sets state
     *
     * @param \Wallee\Sdk\Model\DebtCollectionCaseState $state The object's current state.
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->container['state'] = $state;

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


