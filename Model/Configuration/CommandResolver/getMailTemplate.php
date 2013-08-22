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

/*
 $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getMailTemplate', array("templateName"=>'newListoolFileMailtext'));
 $template = $this->listConfig = $response->getDataByKey('template');
 */

namespace LwListtool\Model\Configuration\CommandResolver;

class getMailTemplate extends \LWmvc\Model\CommandResolver
{
    protected $command;
    
    public function __construct($command)
    {
        parent::__construct($command);
        $this->baseNamespace = "\\LwListtool\\Model\\Configuration\\";
        $this->ObjectClass = $this->baseNamespace."Object\\configuration";
    }
    
    public function getInstance($command)
    {
        return new getMailTemplate($command);
    }
    
    public function resolve()
    {
        $templateName = $this->command->getParameterByKey('templateName');
        if ($templateName == 'newListoolFileMailtext' || $templateName == 'editListoolFileMailtext' || $templateName == 'startApprovalListoolMailtext' || $templateName == 'remindApprovalListoolMailtext' || $templateName == 'releaseApprovalListoolMailtext') {
            $template = $this->getQueryHandler()->loadTemplateByName($templateName);
        }
        else{
            $template = "";
        }
        $this->command->getResponse()->setDataByKey('template', $template);
        return $this->command->getResponse();        
        
    }
}