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

class Datafeed extends \RexFeed\Google\Collection
{
    protected $collection_key = 'targets';

    /**
     * @var DatafeedFetchSchedule
     */
    public $fetchSchedule;

    /**
     * @var DatafeedFormat
     */
    public $format;

    /**
     * @var DatafeedTarget
     */
    public $targets;

    /**
     * @var string
     */
    public $attributeLanguage;
    /**
     * @var string
     */
    public $contentType;

    /**
     * @var string
     */
    public $fileName;
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $kind;
    /**
     * @var string
     */
    public $name;
    /**
     * @param string
     */
    public function setAttributeLanguage($attributeLanguage)
    {
        $this->attributeLanguage = $attributeLanguage;
    }
    /**
     * @return string
     */
    public function getAttributeLanguage()
    {
        return $this->attributeLanguage;
    }
    /**
     * @param string
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }
    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }
    /**
     * @param DatafeedFetchSchedule
     */
    public function setFetchSchedule(DatafeedFetchSchedule $fetchSchedule)
    {
        $this->fetchSchedule = $fetchSchedule;
    }
    /**
     * @return DatafeedFetchSchedule
     */
    public function getFetchSchedule()
    {
        return $this->fetchSchedule;
    }
    /**
     * @param string
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }
    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
    /**
     * @param DatafeedFormat
     */
    public function setFormat(DatafeedFormat $format)
    {
        $this->format = $format;
    }
    /**
     * @return DatafeedFormat
     */
    public function getFormat()
    {
        return $this->format;
    }
    /**
     * @param string
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param string
     */
    public function setKind($kind)
    {
        $this->kind = $kind;
    }
    /**
     * @return string
     */
    public function getKind()
    {
        return $this->kind;
    }
    /**
     * @param string
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param DatafeedTarget[]
     */
    public function setTargets($targets)
    {
        $this->targets = $targets;
    }
    /**
     * @return DatafeedTarget[]
     */
    public function getTargets()
    {
        return $this->targets;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(Datafeed::class, 'RexFeed\\Google_Service_ShoppingContent_Datafeed');
