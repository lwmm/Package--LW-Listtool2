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

namespace lwListtool\Model\ApprovalRights\CommandResolver;

class getApprovalRightsObject extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \LwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\ApprovalRights\\";
        $this->ObjectClass = $this->baseNamespace."Object\\approvalRights";
    }
    
    public function getInstance($command)
    {
        return new getApprovalRightsObject($command);
    }
    
    public function resolve()
    {
        $object = new $this->ObjectClass();
        
        $user = array();
        $object->setAssignedUserArray($user);
        
        $intranets = array();
        $object->setAssignedIntranetsArray($intranets);

        $object->setAuthObject($this->dic->getLwAuth());
        $object->setInAuthObject($this->dic->getLwInAuth());
        $object->setListConfigration($this->command->getParameterByKey('listConfig'));
        
        $this->command->getResponse()->setDataByKey('approvalRightsObject', $object);
        return $this->command->getResponse();       
    }

}