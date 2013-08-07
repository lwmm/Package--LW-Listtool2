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

namespace LwListtool\Controller;

class ContentoryBackend extends \LWmvc\Controller
{
    public function __construct($cmd, $oid)
    {
        parent::__construct($cmd, $oid);
        $this->request = \lw_registry::getInstance()->getEntry("request");
        $this->config = \lw_registry::getInstance()->getEntry("config");
    }
    
    public function execute()
    {
        $PathTest = new \LwListtool\Services\PathTest($this->config);
        if (!$PathTest->PathExistsAndIsWriteable()) {
            $View = new \LwListtool\View\PathError();
            return $this->returnRenderedView($View);
        }
        $PathTest->checkPathSecurity();
        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getConfigurationEntityById', array("id"=>$this->getContentObjectId()));
        $this->listConfig = $response->getDataByKey('ConfigurationEntity');
        
        if (!$this->listConfig) {
            die("List isn't configured!");
        }
        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getListRightsObject', array("listId"=>$this->getContentObjectId(), "listConfig"=>$this->listConfig));
        $this->listRights = $response->getDataByKey('rightsObject');

        $method = $this->getCommand()."Action";
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        else {
            die("command doesn't exist");
        }
    }    

    protected function deleteListAction($error = false)
    {
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getListEntriesAggregate', array("configuration"=>$this->listConfig, "listId"=>$this->getContentObjectId(), "listRights"=>$this->listRights));
        $listEntriesAggregate = $response->getDataByKey('listEntriesAggregate');

        foreach($listEntriesAggregate as $entry) {
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'delete', array("id"=>$entry->getValueByKey("id"), "listId"=>$this->getContentObjectId()));
        }
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'delete', array("id"=>$this->getContentObjectId()));
        return $response;
    }
    
    protected function showFormAction($error = false)
    {
        if ($error) {
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getConfigurationEntityFromArray', array(), array("postArray"=>$this->request->getPostArray()));
            $entity = $response->getDataByKey('ConfigurationEntity');
            $entity->setId($this->getContentObjectId());
        }
        else {
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getConfigurationEntityById', array("id"=>$this->getContentObjectId()));
            $entity = $response->getDataByKey('ConfigurationEntity');
            $entity->setId($this->getContentObjectId());
        }
        $formView = new \LwListtool\View\ConfigurationForm('edit');
        $formView->setEntity($entity);
        $formView->setErrors($error);
        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getIntranetsByListId', array("listId"=>$this->getContentObjectId()));
        $formView->setAssignedIntranets($response->getDataByKey('IntranetsArray'));

        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getUserByListId', array("listId"=>$this->getContentObjectId()));
        $formView->setAssignedUser($response->getDataByKey('UserArray'));
        
        return $this->returnRenderedView($formView);
    }    

    protected function saveAction()
    {
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'save', array("id"=>$this->getContentObjectId()), array("postArray"=>$this->request->getPostArray()));
        if ($response->getParameterByKey("error")) {
            return $this->editFormAction($response->getDataByKey("error"));
        }
        return $this->buildReloadResponse(array("cmd"=>"showForm"));
    }
    
    protected function deleteAction()
    {
        return $this->buildDeleteAction('Configuration', $this->getContentObjectId());
    }
    
    protected function assignIntranetsAction()
    {
        $config = \lw_registry::getInstance()->getEntry("config");
        include_once($config['path']['server'].'c_backend/intranetassignments/agent_intranetassignments.class.php');
        $assign = new \agent_intranetassignments();

        $assign->setObject('listtool_cbox', $this->getContentObjectId());
        $assign->setAction(\lw_object::buildUrl(array("pcmd" => "saveAssignIntranets", "ltid" => $this->getContentObjectId())));
        $assign->execute();
    }
    
    protected function saveAssignIntranetsAction()
    {
        $config = \lw_registry::getInstance()->getEntry("config");
        include_once($config['path']['server'] . 'c_backend/intranetassignments/agent_intranetassignments.class.php');
        $assign = new \agent_intranetassignments();
        $assign->setDelegate($this);
        $assign->setObject('listtool_cbox', $this->getContentObjectId());

        $temp = $this->request->getPostArray();
        $assign->setAssignedUsers($temp['user']);
        $assign->setAssignedIntranets($temp['intranet']);
        $assign->saveObject();

        return $this->buildReloadResponse(array("pcmd"=>"assignIntranets", "ltid" => $this->getContentObjectId()));
    }
}