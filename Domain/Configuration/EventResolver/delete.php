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

namespace LwListtool\Domain\Configuration\EventResolver;

class delete extends \LWddd\DomainEventResolver
{
    protected $event;
    
    public function __construct($event)
    {
        parent::__construct($event);
        $this->baseNamespace = "\\LwListtool\\Domain\\Configuration\\";
        $this->ObjectClass = $this->baseNamespace."Object\\configuration";
    }
    
    public function getInstance($event)
    {
        return new delete($event);
    }
    
    public function resolve()
    {
        try {
            $result = $this->getCommandHandler()->deletePluginData($this->event->getParameterByKey('id'));
            $this->event->getResponse()->setParameterByKey('deleted', true);
        }
        catch (\LWddd\validationErrorsException $e) {
            $this->event->getResponse()->setDataByKey('error', $e->getErrors());
            $this->event->getResponse()->setParameterByKey('error', true);
        }        
        return $this->event->getResponse();       
    }
}