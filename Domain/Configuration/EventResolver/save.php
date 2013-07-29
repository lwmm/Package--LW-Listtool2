<?php

/* * ************************************************************************
 *  Copyright notice
 *
 *  Copyright 2013 Logic Works GmbH
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *  
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *  
 * ************************************************************************* */

namespace LwListtool\Domain\Configuration\EventResolver;

class save extends \LWddd\DomainEventResolver
{

    protected $event;

    public function __construct($event)
    {
        parent::__construct($event);
        $this->baseNamespace = "\\LwListtool\\Domain\\Configuration\\";
        $this->ObjectClass = $this->baseNamespace . "Object\\configuration";
    }

    public function getInstance($event)
    {
        return new save($event);
    }

    public function resolve()
    {
        try {
            $dataValueObject = new \LWddd\ValueObject($this->event->getDataByKey('postArray'));
            $parameter['langParams'] = $dataValueObject->getValueByKey("lw_i18n");
            $parameter['name'] = $dataValueObject->getValueByKey("name");
            $parameter['listtooltype'] = $dataValueObject->getValueByKey("listtooltype");
            $parameter['template'] = $dataValueObject->getValueByKey("template");
            $parameter['sorting'] = $dataValueObject->getValueByKey("sorting");
            $parameter['suffix_type'] = $dataValueObject->getValueByKey("suffix_type");
            $parameter['suffix'] = $dataValueObject->getValueByKey("suffix");
            $parameter['secured'] = $dataValueObject->getValueByKey("secured");
            $parameter['language'] = $dataValueObject->getValueByKey("language");
            $parameter['borrow'] = $dataValueObject->getValueByKey("borrow");
            $parameter['showcss'] = $dataValueObject->getValueByKey("showcss");
            $parameter['linktype'] = $dataValueObject->getValueByKey("linktype");
            $parameter['publishedoption'] = $dataValueObject->getValueByKey("publishedoption");
            $parameter['showId'] = $dataValueObject->getValueByKey("showId");
            $parameter['showUser'] = $dataValueObject->getValueByKey("showUser");
            $parameter['showDate'] = $dataValueObject->getValueByKey("showDate");
            $parameter['showLastDate'] = $dataValueObject->getValueByKey("showLastDate");
            $parameter['showFreeDate'] = $dataValueObject->getValueByKey("showFreeDate");
            $parameter['showTime'] = $dataValueObject->getValueByKey("showTime");
            $parameter['showDescription'] = $dataValueObject->getValueByKey("showDescription");
            $parameter['showName'] = $dataValueObject->getValueByKey("showName");
            $parameter['showFirstUser'] = $dataValueObject->getValueByKey("showFirstUser");
            $parameter['showLastUser'] = $dataValueObject->getValueByKey("showLastUser");
            $parameter['showFreeDate'] = $dataValueObject->getValueByKey("showFreeDate");
            $parameter['showFileDate'] = $dataValueObject->getValueByKey("showFileDate");
            $parameter['title_description'] = $dataValueObject->getValueByKey("title_description");
            $parameter['title_name'] = $dataValueObject->getValueByKey("title_name");
            $parameter['readableby'] = $dataValueObject->getValueByKey("readableby");
            $parameter['title_link'] = $dataValueObject->getValueByKey("title_link");
            $parameter['title_download'] = $dataValueObject->getValueByKey("title_download");
            $parameter['archive'] = $dataValueObject->getValueByKey("archive");
            $parameter['showKeyWords'] = $dataValueObject->getValueByKey("showKeyWords");
            $parameter['showAdditionalInfo'] = $dataValueObject->getValueByKey("showAdditionalInfo");
            $parameter['showThumbnail'] = $dataValueObject->getValueByKey("showThumbnail");
            $content = false;
            $result = $this->getCommandHandler()->savePluginData($this->event->getParameterByKey('id'), $parameter, $content);
            $this->event->getResponse()->setParameterByKey('saved', true);
        } catch (\LWddd\validationErrorsException $e) {
            $this->event->getResponse()->setDataByKey('error', $e->getErrors());
            $this->event->getResponse()->setParameterByKey('error', true);
        }
        return $this->event->getResponse();
    }

}
