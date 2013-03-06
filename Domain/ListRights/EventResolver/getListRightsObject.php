<?php

/**************************************************************************
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
***************************************************************************/

namespace lwListtool\Domain\ListRights\EventResolver;

class getListRightsObject extends \LWddd\DomainEventResolver
{
    public function __construct($event)
    {
        parent::__construct($event);
        $this->dic = new \LwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Domain\\ListRights\\";
        $this->ObjectClass = $this->baseNamespace."Object\\listRights";
    }
    
    public function getInstance($event)
    {
        return new getListRightsObject($event);
    }
    
    public function resolve()
    {
        $object = new $this->ObjectClass();
        
        $user = $this->getQueryHandler()->getUserByListid($this->event->getParameterByKey('listId'));
        $object->setAssignedUserArray($user);
        
        $intranets = $this->getQueryHandler()->getIntranetsByListid($this->event->getParameterByKey('listId'));
        $object->setAssignedIntranetsArray($intranets);

        $object->setAuthObject($this->dic->getLwAuth());
        $object->setInAuthObject($this->dic->getLwInAuth());
        $object->setListConfigration($this->event->getParameterByKey('listConfig'));
        
        $this->event->getResponse()->setDataByKey('rightsObject', $object);
        return $this->event->getResponse();       
    }
}