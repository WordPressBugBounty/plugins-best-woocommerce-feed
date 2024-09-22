<?php

/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
namespace RexFeed\Google\Service\ShoppingContent;

class DeliveryAreaPostalCodeRange extends \RexFeed\Google\Model
{
    /**
     * @var string
     */
    public $firstPostalCode;
    /**
     * @var string
     */
    public $lastPostalCode;
    /**
     * @param string
     */
    public function setFirstPostalCode($firstPostalCode)
    {
        $this->firstPostalCode = $firstPostalCode;
    }
    /**
     * @return string
     */
    public function getFirstPostalCode()
    {
        return $this->firstPostalCode;
    }
    /**
     * @param string
     */
    public function setLastPostalCode($lastPostalCode)
    {
        $this->lastPostalCode = $lastPostalCode;
    }
    /**
     * @return string
     */
    public function getLastPostalCode()
    {
        return $this->lastPostalCode;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(DeliveryAreaPostalCodeRange::class, 'RexFeed\\Google_Service_ShoppingContent_DeliveryAreaPostalCodeRange');