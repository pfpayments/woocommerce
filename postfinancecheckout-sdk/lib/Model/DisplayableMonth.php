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
use \Wallee\Sdk\ObjectSerializer;

/**
 * DisplayableMonth model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      wallee AG
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 */
class DisplayableMonth
{
    /**
     * Possible values of this enum
     */
    const JANUARY = 'JANUARY';
    const FEBRUARY = 'FEBRUARY';
    const MARCH = 'MARCH';
    const APRIL = 'APRIL';
    const MAY = 'MAY';
    const JUNE = 'JUNE';
    const JULY = 'JULY';
    const AUGUST = 'AUGUST';
    const SEPTEMBER = 'SEPTEMBER';
    const OCTOBER = 'OCTOBER';
    const NOVEMBER = 'NOVEMBER';
    const DECEMBER = 'DECEMBER';
    
    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::JANUARY,
            self::FEBRUARY,
            self::MARCH,
            self::APRIL,
            self::MAY,
            self::JUNE,
            self::JULY,
            self::AUGUST,
            self::SEPTEMBER,
            self::OCTOBER,
            self::NOVEMBER,
            self::DECEMBER,
        ];
    }
}


