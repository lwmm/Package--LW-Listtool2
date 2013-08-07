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

class Frontend extends \LWmvc\Controller
{
    public function __construct($cmd, $oid)
    {
        parent::__construct($cmd, $oid);
        $this->dic = new \LwListtool\Services\dic();
        $this->response = \lw_registry::getInstance()->getEntry("response");
        $this->request = \lw_registry::getInstance()->getEntry("request");
        $this->lwi18nQH = new \LwI18n\Model\queryHandler($this->dic->getDbObject());
        $this->config = $this->dic->getConfiguration();
    }
    
    public function execute()
    {
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getConfigurationEntityById', array("id"=>$this->getContentObjectId()));
        $this->listConfig = $response->getDataByKey('ConfigurationEntity');
        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getListRightsObject', array("listId"=>$this->getContentObjectId(), "listConfig"=>$this->listConfig));
        $this->listRights = $response->getDataByKey('rightsObject');

        $method = $this->getCommand()."Action";
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        else {
            die("command ".$method." doesn't exist");
        }
    }    
    
    protected function showListAction($error = false)
    {
        if ($this->listRights->isReadAllowed()) {
            $this->response->useJQuery();
            $this->response->useJQueryUI();
            
            $result = $this->lwi18nQH->getAllEntriesForCategoryAndLang("lw_listtool2", $this->listConfig->getValueByKey("language"));
            $temp = array();
            foreach($result as $value) {
                $temp[$value["lw_key"]] = $value["text"];
            }

            $view = new \LwListtool\View\ListtoolList();
            $view->setConfiguration($this->listConfig);
            $view->setListRights($this->listRights);
            $view->setListId($this->getContentObjectId());
            $view->setLanguagePhrases($temp);
            $view->init();
        
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getListEntriesCollection', array("configuration"=>$this->listConfig, "listId"=>$this->getContentObjectId(), "listRights"=>$this->listRights));
            $view->setCollection($response->getDataByKey('listEntriesCollection'));

            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getIsDeletableSpecification');
            $view->setIsDeletableSpecification($response->getDataByKey('isDeletableSpecification'));
            return $this->returnRenderedView($view);    
        }
        else {
            $response = \LWddd\Response::getInstance();
            $response->setOutputByKey('output', "<!-- Listtool not allowed -->");
            return $response;           
        }
     }
     
     protected function addEntryAction()
     {
        if ($this->listRights->isWriteAllowed()) {

            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'add', array("listId"=>$this->getContentObjectId(), "configuration" => $this->listConfig), array('postArray'=>$this->request->getPostArray(), 'opt1file'=>$this->request->getFileData('opt1file'), 'opt2file'=>$this->request->getFileData('opt2file')));
            if ($response->getParameterByKey("error")) {
                if ($this->request->getAlnum("type") == "file") {
                    return $this->showAddFileFormAction($response->getDataByKey("error"));
                } 
                else {
                    return $this->showAddLinkFormAction($response->getDataByKey("error"));
                }
            }
            return $this->buildReloadResponse(array("cmd"=>"showList", "reloadParent"=>1));
        }
     }

     protected function showEditEntryFormAction($error=false)
     {
         if ($this->listRights->isWriteAllowed()) {
            $formView = new \LwListtool\View\EntryForm('edit');
            if ($error) {
                $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityFromPostArray', array(), array("postArray"=>$this->request->getPostArray()));
            }
            else {
                $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
            }
            $entity = $response->getDataByKey('EntryEntity');
            $entity->setId($this->request->getInt("id"));
            
            $dir = \lw_directory::getInstance($this->config["path"]["listtool"]."archive/");
            $files = $dir->getDirectoryContents('file');
            $archivedFiles = array();
            foreach ($files as $file) {
                if(strstr($file->getName(), "_item_" . $this->request->getInt("id") . ".file")) {
                    $archivedFiles[] = $file->getName();
                }
            }

            $formView->setArchiveValues($this->listConfig->getValueByKey("archive"), $archivedFiles);
            $formView->setEntity($entity);
            $formView->setConfiguration($this->listConfig);
            if ($entity->isFile()) {
                $formView->setEntryType('file');
            }
            else {
                $formView->setEntryType('link');
            }
            $formView->setErrors($error);
            $response = $this->returnRenderedView($formView);
            $response->setParameterByKey('die', 1);
            return $response;
        }
     }
     
    protected function saveEntryAction()
    {
       if ($this->listRights->isWriteAllowed()) {
           $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'save', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId(), "configuration" => $this->listConfig), array('postArray'=>$this->request->getPostArray(), 'opt1file'=>$this->request->getFileData('opt1file'), 'opt2file'=>$this->request->getFileData('opt2file')));
           if ($response->getParameterByKey("error")) {
               return $this->showEditEntryFormAction($response->getDataByKey("error"));
           }
           return $this->buildReloadResponse(array("cmd"=>"showList", "reloadParent"=>1));
       }
    }
    
    protected function deleteEntryThumbnailAction()
    {
       if ($this->listRights->isWriteAllowed()) {
           $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'deleteThumbnail', array("id"=>$this->request->getInt("id")), array());
           if ($response->getParameterByKey("error")) {
               return $this->showEditEntryFormAction($response->getDataByKey("error"));
           }
           return $this->buildReloadResponse(array("cmd"=>"showList", "reloadParent"=>1));
       }
    }
     
    protected function showAddFileFormAction($error=false)
    {
       if ($this->listRights->isWriteAllowed()) {
           return $this->buildAddForm('file', $error);
       }
    }
     
    protected function showAddLinkFormAction($error=false)
    {
        if ($this->listRights->isWriteAllowed()) {
            return $this->buildAddForm('link', $error);
        }
    }
     
    protected function buildAddForm($type, $error=false)
    {
       if ($this->listRights->isWriteAllowed()) {
           $formView = new \LwListtool\View\EntryForm("add");
           $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityFromPostArray', array(), array("postArray"=>$this->request->getPostArray()));
           $formView->setConfiguration($this->listConfig);
           $formView->setEntity($response->getDataByKey('EntryEntity'));
           $formView->setEntryType($type);
           $formView->setErrors($error);
           $response = $this->returnRenderedView($formView);
           $response->setParameterByKey('die', 1);
           return $response;
       }
    }
     
    protected function deleteEntryAction()
    {
       if ($this->listRights->isWriteAllowed()) {
           $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'delete', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
           return $this->buildReloadResponse(array("cmd"=>"showList"));
       }
    }
     
    protected function borrowEntryAction()
    {
       if ($this->listRights->isWriteAllowed()) {
           $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'borrow', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId(), "borrowerId"=>$this->dic->getLwInAuth()->getUserdata("id")));
           return $this->buildReloadResponse(array("cmd"=>"showList"));
       }
    }
     
    protected function releaseEntryAction()
    {
       if ($this->listRights->isWriteAllowed()) {
           $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'release', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId(), "borrowerId"=>$this->dic->getLwInAuth()->getUserdata("id")));
           return $this->buildReloadResponse(array("cmd"=>"showList"));
       }
    }
     
    protected function showThumbnailAction()
    {
       if ($this->listRights->isReadAllowed()) {
           $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
           $file = $response->getDataByKey('EntryEntity')->getThumbnailPath();
           if (is_file($file)) {
               header("Content-type: ".\lw_io::getMimeType(\lw_io::getFileExtension($file)));
               readfile($file);
               exit();
           }
           die("not existing");
       }
       die("not allowed");
    }
     
    public function downloadEntryAction()
    {
        if ($this->listRights->isReadAllowed()) {
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
            $entity = $response->getDataByKey('EntryEntity');
            if($this->request->getAlnum("filedate")) {
                $file = $entity->getFilePath($this->request->getAlnum("filedate"));
            } else {
                $file = $entity->getFilePath();
            }
            if (is_file($file)) {
                $extension = \lw_io::getFileExtension($data['opt2file']);
                $mimeType = \lw_io::getMimeType($extension);
                if (strlen($mimeType) < 1) {
                    $mimeType = "application/octet-stream";
                }
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: " . $mimeType);
                header("Content-disposition: attachment; filename=\"".$entity->getValueByKey('opt2file')."\"");
                readfile($file);
                exit();
            }
            die("not existing");
        }
        die("not allowed");
    }
    
    public function sortEntriesAction()
    {
        if ($this->listRights->isWriteAllowed()) {
            $this->response->useJQuery();
            $this->response->useJQueryUI();

            $view = new \LwListtool\View\Sortlist();
            $view->setConfiguration($this->listConfig);
            $view->setListRights($this->listRights);
            $view->init();
        
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getListEntriesCollection', array("configuration"=>$this->listConfig, "listId"=>$this->getContentObjectId(), "listRights"=>$this->listRights));
            $view->setCollection($response->getDataByKey('listEntriesCollection'));

            $response = $this->returnRenderedView($view);
            $response->setParameterByKey('die', 1);
            return $response;
        }
        else {
           die("not allowed");
        }
    }
    
    public function saveSortingAction()
    {
        if ($this->listRights->isWriteAllowed()) {
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'sort', array("listId"=>$this->getContentObjectId()), array("postArray" => $this->request->getPostArray()));
            return $this->buildReloadResponse(array("cmd"=>"showList", "reloadParent"=>1));
        }
    }
    
    public function showVersionsAction()
    {
        if (\lw_registry::getInstance()->getEntry('FeatureCollection')->getFeature('LwListtoolVersioning')->isActive()) {
            $view = new \LwListtool\View\ListtoolVersionsList();
            $view->setConfiguration($this->listConfig);
            $view->setListRights($this->listRights);
            $view->setListId($this->getContentObjectId());
            return $this->returnRenderedView($view);     
       }
       return showListAction();
    }
}
