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
 * PaymentAppProcessorCreationRequest model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class PaymentAppProcessorCreationRequest implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'PaymentAppProcessorCreationRequest';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'documentation_url' => 'string',
        'external_id' => 'string',
        'name' => 'string',
        'production_mode_url' => 'string',
        'svg_icon' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'documentation_url' => null,
        'external_id' => null,
        'name' => null,
        'production_mode_url' => null,
        'svg_icon' => null
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'documentation_url' => 'documentationUrl',
        'external_id' => 'externalId',
        'name' => 'name',
        'production_mode_url' => 'productionModeUrl',
        'svg_icon' => 'svgIcon'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'documentation_url' => 'setDocumentationUrl',
        'external_id' => 'setExternalId',
        'name' => 'setName',
        'production_mode_url' => 'setProductionModeUrl',
        'svg_icon' => 'setSvgIcon'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'documentation_url' => 'getDocumentationUrl',
        'external_id' => 'getExternalId',
        'name' => 'getName',
        'production_mode_url' => 'getProductionModeUrl',
        'svg_icon' => 'getSvgIcon'
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
        
        $this->container['documentation_url'] = isset($data['documentation_url']) ? $data['documentation_url'] : null;
        
        $this->container['external_id'] = isset($data['external_id']) ? $data['external_id'] : null;
        
        $this->container['name'] = isset($data['name']) ? $data['name'] : null;
        
        $this->container['production_mode_url'] = isset($data['production_mode_url']) ? $data['production_mode_url'] : null;
        
        $this->container['svg_icon'] = isset($data['svg_icon']) ? $data['svg_icon'] : null;
        
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['documentation_url'] === null) {
            $invalidProperties[] = "'documentation_url' can't be null";
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

        if ($this->container['svg_icon'] === null) {
            $invalidProperties[] = "'svg_icon' can't be null";
        }
        if ((mb_strlen($this->container['svg_icon']) > 10000)) {
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
     * @param string $documentation_url The documentation URL has to point to a description of how to configure and use the processor.
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
     * @param string $external_id The external ID identifies the processor within the external system. It has to be unique per space and for any subsequent update the same ID must be sent.
     *
     * @return $this
     */
    public function setExternalId($external_id)
    {
        if ((mb_strlen($external_id) > 40)) {
            throw new \InvalidArgumentException('invalid length for $external_id when calling PaymentAppProcessorCreationRequest., must be smaller than or equal to 40.');
        }
        if ((mb_strlen($external_id) < 1)) {
            throw new \InvalidArgumentException('invalid length for $external_id when calling PaymentAppProcessorCreationRequest., must be bigger than or equal to 1.');
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
     * @param string $name The name of the processor will be displayed within the user interfaces that the merchant is interacting with.
     *
     * @return $this
     */
    public function setName($name)
    {
        if ((mb_strlen($name) > 100)) {
            throw new \InvalidArgumentException('invalid length for $name when calling PaymentAppProcessorCreationRequest., must be smaller than or equal to 100.');
        }

        $this->container['name'] = $name;

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
     * @param string $production_mode_url The production mode URL has to point to a site on which the merchant can set up the production mode for the processor.
     *
     * @return $this
     */
    public function setProductionModeUrl($production_mode_url)
    {
        $this->container['production_mode_url'] = $production_mode_url;

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
     * @param string $svg_icon The SVG icon will be displayed to the user to represent this processor.
     *
     * @return $this
     */
    public function setSvgIcon($svg_icon)
    {
        if ((mb_strlen($svg_icon) > 10000)) {
            throw new \InvalidArgumentException('invalid length for $svg_icon when calling PaymentAppProcessorCreationRequest., must be smaller than or equal to 10000.');
        }

        $this->container['svg_icon'] = $svg_icon;

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


