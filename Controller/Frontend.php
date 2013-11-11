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
        $this->auth = $this->dic->getLwAuth();
        $this->response = \lw_registry::getInstance()->getEntry("response");
        $this->request = \lw_registry::getInstance()->getEntry("request");
        $this->lwi18nQH = new \LwI18n\Model\queryHandler($this->dic->getDbObject());
        $this->config = $this->dic->getConfiguration();
        $this->featureCollection = \lw_registry::getInstance()->getEntry("FeatureCollection");
    }
    
    public function execute()
    {
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getConfigurationEntityById', array("id"=>$this->getContentObjectId()));
        $this->listConfig = $response->getDataByKey('ConfigurationEntity');
        $this->useApprovalSystemListConfig = $this->listConfig->getValueByKey('approval');
        $this->useEmailNotificationSystemListConfig = $this->listConfig->getValueByKey('notification');
        $this->useBorrowSystemListConfig = $this->listConfig->getValueByKey('borrow');
        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getListRightsObject', array("listId"=>$this->getContentObjectId(), "listConfig"=>$this->listConfig));
        $this->listRights = $response->getDataByKey('rightsObject');
        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ApprovalRights', 'getApprovalRightsObject', array("listId"=>$this->getContentObjectId(), "listConfig"=>$this->listConfig));
        $this->approvalRights = $response->getDataByKey('approvalRightsObject');

        $result = $this->lwi18nQH->getAllEntriesForCategoryAndLang("lw_listtool2", $this->listConfig->getValueByKey("language"));
        $this->lang = array();
        foreach($result as $value) {
            $this->lang[$value["lw_key"]] = $value["text"];
        }
        
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

            $view = new \LwListtool\View\ListtoolList();
            $view->setConfiguration($this->listConfig);
            $view->setListRights($this->listRights);
            $view->setApprovalRights($this->approvalRights);
            $view->setListId($this->getContentObjectId());
            $view->setLanguagePhrases($this->lang);
            $view->setApprovalSystemUsage($this->useApprovalSystemListConfig);
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
            
            if ($this->request->getAlnum("type") == "file" && $this->request->getInt("published") == 1 ) {
                if($this->featureCollection->getFeature("LwListtool_EmailNotification")->isActive() && $this->useEmailNotificationSystemListConfig){
                    $opt2file = $this->request->getFileData('opt2file');
                    \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Notification', 'sendNotificationMail', array("listId"=> $this->getContentObjectId(), "filename" => $opt2file['name'], "entryname" => $this->request->getAlnum("name"),"cmd" => "addFile"));
                }
            }
            return $this->buildReloadResponse(array("cmd"=>"showList", "reloadParent"=>1));
        }
        return $this->showListAction();
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
      
            if(!$entity->isInApproval() && !$entity->isApproved()){ # /F030/ + /F080/ Bearbeitung verweigern, wenn sich die Datei im Genehmigungsverfahren befindet oder genehmigt worden ist.
                if($this->useBorrowSystemListConfig && ($this->auth->isLoggedIn() || $entity->isInUserBorrower())) {
                    $allowEdit = true;
                }else if(!$this->useBorrowSystemListConfig){
                    $allowEdit = true;
                }else{
                    $allowEdit = false;
                }

                if($allowEdit){
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
        }
        return $this->showListAction();
     }
     
    protected function saveEntryAction()
    {
       if ($this->listRights->isWriteAllowed()) {
           $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
           $entity = $response->getDataByKey('EntryEntity');

           if(!$entity->isInApproval() && !$entity->isApproved()){ # /F030/ + /F080/ Bearbeitung verweigern, wenn sich die Datei im Genehmigungsverfahren befindet oder genehmitgt worden ist.
               $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'save', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId(), "configuration" => $this->listConfig), array('postArray'=>$this->request->getPostArray(), 'opt1file'=>$this->request->getFileData('opt1file'), 'opt2file'=>$this->request->getFileData('opt2file')));
               if ($response->getParameterByKey("error")) {
                   return $this->showEditEntryFormAction($response->getDataByKey("error"));
               }
               
               if ($entity->isFile() && $this->request->getInt("published") == 1) {
                    if($this->featureCollection->getFeature("LwListtool_EmailNotification")->isActive() && $this->useEmailNotificationSystemListConfig){
                        \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Notification', 'sendNotificationMail', array("listId"=> $this->getContentObjectId(), "filename" => $entity->getValueByKey('opt2file'), "entryname" => $entity->getValueByKey("name"),"cmd" => "editFile"));
                    }
                }
           }            
           return $this->buildReloadResponse(array("cmd"=>"showList", "reloadParent"=>1));
       }
       return $this->showListAction();
    }
    
    protected function deleteEntryThumbnailAction()
    {
       if ($this->listRights->isWriteAllowed()) {
           $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
           $entity = $response->getDataByKey('EntryEntity');
           
           if(!$entity->isInApproval() && !$entity->isApproved()){ # /F030/ Bearbeitung verweigern, wenn sich die Datei im Genehmigungsverfahren befindet
               $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'deleteThumbnail', array("id"=>$this->request->getInt("id")), array());
               if ($response->getParameterByKey("error")) {
                   return $this->showEditEntryFormAction($response->getDataByKey("error"));
               }
           }
           return $this->buildReloadResponse(array("cmd"=>"showList", "reloadParent"=>1));
       }
       return $this->showListAction();
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
           $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
           $entity = $response->getDataByKey('EntryEntity');
           
           if(!$entity->isInApproval()) {
               $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'delete', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));

               if ($entity->isFile() && $entity->getValueByKey('published') == 1) {
                    if($this->featureCollection->getFeature("LwListtool_EmailNotification")->isActive() && $this->useEmailNotificationSystemListConfig){
                        \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Notification', 'sendNotificationMail', array("listId"=> $this->getContentObjectId(), "filename" => $entity->getValueByKey('opt2file'), "entryname" => $entity->getValueByKey("name"),"cmd" => "deleteFile"));
                    }
                }
           }
           
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
                $extension = \lw_io::getFileExtension($entity->getValueByKey('opt2file'));
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
    
    /**
     *              /F020/
     * 
     * Laden des Formulars, um die Gültigkeitsdauer eines Genehmigungsverfahrens
     * festzulegen und dieses einzuleiten.
     */
    public function showStartApprovalEntryFormAction($error = false)
    {
        # Prüfung der Nutzerrechte und Systemeinstellungen
        if ($this->listRights->isWriteAllowed() && $this->approvalRights->isApprovalAllowed() && $this->featureCollection->getFeature("LwListtool_ApprovalSystem")->isActive() && $this->useApprovalSystemListConfig) {
            
            # Eintrag anhand der ID laden
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
            $entity = $response->getDataByKey('EntryEntity');
            
            # Prüfung ob für den Eintrag ein Genehmigungsverfahren eingeleiteten werden kann
            if(!$entity->isInApproval() && !$entity->isApproved()){

                $formView = new \LwListtool\View\StartApprovalForm();
                $formView->setEntity($entity);
                $formView->setConfiguration($this->listConfig);
                $formView->setError($error);

                $response = $this->returnRenderedView($formView);
                $response->setParameterByKey('die', 1);
                return $response;
            }
        }
        # Ist die Prüfung der Nutzerrechte, Systemeinstellungen und des Eintragsstatus fehlgeschladen
        # wird die Liste angezeigt.   ( Verdacht auf Url-Manipulation )     
        return $this->showListAction();
    }

    /**
     *              /F100/ + /F110/ + /F120/
     * 
     * Genehmigungsstatistik laden und im Statusdialog anzeigen.
     */
    public function showApprovalEntryStatusAction()
    {
        if ($this->listRights->isWriteAllowed() && $this->approvalRights->isApprovalAllowed() && $this->featureCollection->getFeature("LwListtool_ApprovalSystem")->isActive() && $this->useApprovalSystemListConfig) {
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
            $entity = $response->getDataByKey('EntryEntity');

            if($entity->isInApproval() && !$entity->isApproved() && ($this->auth->isLoggedIn() || $entity->isInUserApprovalStarter()) ){                
                $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getApprovalStatistics', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
                $results = $response->getDataByKey('results'); 

                $formView = new \LwListtool\View\StatusApproval();
                $formView->setEntity($entity);
                $formView->setConfiguration($this->listConfig);
                $formView->setResults($results);
                $formView->setLanguagePhrases($this->lang);

                $response = $this->returnRenderedView($formView);
                $response->setParameterByKey('die', 1);
                return $response;
            }
        }
        return $this->showListAction();
    }
    
    /**
     *              /F040/
     * 
     * Laden des Formulars für die Abstimmung. 
     */
    public function showApprovalEntryVoteAction($error = false)
    {
        if ($this->listRights->isWriteAllowed()/* /F040/ nur mit zugewiesenen Schreibrechten darf abgestimmt werden */ && $this->featureCollection->getFeature("LwListtool_ApprovalSystem")->isActive() && $this->useApprovalSystemListConfig && !$this->auth->isLoggedIn()) {   
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
            $entity = $response->getDataByKey('EntryEntity');
            
            # /F140/ ist der Gültigkeitszeitraum überschritten, dann darf keine Abstimmung mehr angenommen werden
            if($entity->isInApproval() && !$entity->isApproved() && $entity->isVoteAllowed() && !$entity->hasLoggedInInUserVoted()){
                $formView = new \LwListtool\View\VoteApproval();
                $formView->setEntity($entity);
                $formView->setError($error);
                $formView->setConfiguration($this->listConfig);

                $response = $this->returnRenderedView($formView);
                $response->setParameterByKey('die', 1);
                return $response;
            }
        }
         return $this->showListAction();
    }

    /**
     *              /F020/
     * 
     * Die Datei wird in den Status "in Genehmigung" versetzt. 
     */
    protected function startApprovalAction()
    {
        if ($this->listRights->isWriteAllowed() && $this->approvalRights->isApprovalAllowed() && $this->featureCollection->getFeature("LwListtool_ApprovalSystem")->isActive() && $this->useApprovalSystemListConfig) {            
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
            $entity = $response->getDataByKey('EntryEntity');
            
            if(!$entity->isInApproval() && !$entity->isApproved()){
                
                # Ausführung des Befehls, die Datei in den Status "in Genehmigung" zu versetzen
                $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'startApproval', array("id"=>$entity->getValueByKey("id"), "approvalUserId"=> $this->dic->getLwInAuth()->getUserdata("id"), "enddate" => $this->request->getInt("enddate")));
                
                if($response->getParameterByKey("errordate")){
                    # Liegt das im Formular eingebene Enddatum nicht in der Zukunft, dann wird das Formular neu geladen und
                    # die entsprechende Fehlermeldung ausgeben.
                    return $this->showStartApprovalEntryFormAction($response->getParameterByKey("errordate"));
                }
                
                # Versendung einer Informationsmail, dass für eine Datei ein Genehmigungsverfahren eingeleitet wurde.
                \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Notification', 'sendNotificationMail', array("listId"=> $this->getContentObjectId(), "filename" => $entity->getValueByKey("opt2file"), "entryname" => $entity->getValueByKey("name"),"cmd" => "startApproval"));
                
                # Dialog des Formulars schließen und Liste neu anzeigen.
                return $this->buildReloadResponse(array("cmd"=>"showList", "reloadParent"=>1));
            }
        }
        return $this->showListAction();
    }
    
    /**
     *              /F130/ + /F150/ + /F170/
     * 
     * Genehmigungsverfahren beenden. 
     */
    protected function stoppApprovalAction()
    {
        if ($this->listRights->isWriteAllowed() && $this->approvalRights->isApprovalAllowed() && $this->featureCollection->getFeature("LwListtool_ApprovalSystem")->isActive() && $this->useApprovalSystemListConfig) {
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
            $entity = $response->getDataByKey('EntryEntity');
            
            # Prüfung ob der Eintrag genehmigt werden kann und ob der Genehmigungsstarter bzw. ein Backend-Administrator eingeloggt ist
            if($entity->isInApproval() && !$entity->isApproved() && ($this->auth->isLoggedIn() || $entity->isInUserApprovalStarter())){
                
                # Genehmigungsverfahren stoppen und die Datei wieder freigeben.
                $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'stoppApproval', array("id"=>$this->request->getInt("id"), "approvalUserId"=> $this->dic->getLwInAuth()->getUserdata("id")));
                
                # Versendung einer Informationsmail, dass für eine Datei ein Genehmigungsverfahren beendet wurde und die Datei wieder bearbeitet werden kann.. 
                \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Notification', 'sendNotificationMail', array("listId"=> $this->getContentObjectId(), "filename" => $entity->getValueByKey("opt2file"), "entryname" => $entity->getValueByKey("name"),"cmd" => "stoppApproval"));
                return $this->buildReloadResponse(array("cmd"=>"showList", "reloadParent"=>1));
            }
        }
        return $this->showListAction();
    }
    
    /**
     *              /F040/
     * 
     * Stimmabgabe speichern. 
     */
    protected function voteApprovalAction()
    {
        if ($this->listRights->isWriteAllowed() && $this->featureCollection->getFeature("LwListtool_ApprovalSystem")->isActive() && $this->useApprovalSystemListConfig && !$this->auth->isLoggedIn()) {
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
            $entity = $response->getDataByKey('EntryEntity');
            
            if($entity->isInApproval() && !$entity->isApproved() && $entity->isVoteAllowed() && !$entity->hasLoggedInInUserVoted()){
                $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'voteApproval', array("id"=>$this->request->getInt("id"), "approvalUserId"=> $this->dic->getLwInAuth()->getUserdata("id"), "listId"=>$this->getContentObjectId()), array("postArray" => $this->request->getPostArray()));            
                if($response->getParameterByKey("errorvote")){
                    
                    # /F060/ Wurde mit "Nein" gestimmt und kein Kommentar oder ein zu langer Kommentar geschrieben, dann 
                    # wird das Formular für die Stimmabgabe neue geladen und die entsprechende Fehlermeldung angezeigt.
                    return $this->showApprovalEntryVoteAction($response->getParameterByKey("errorvote"));
                }
                return $this->buildReloadResponse(array("cmd"=>"showList", "reloadParent"=>1));
            }
        }
        return $this->showListAction();
    }
    
    /**
     *              /F090/
     * 
     * Erinnerungsmail verschicken. 
     */
    public function sendReminderApprovalMailAction()
    {
        if ($this->listRights->isWriteAllowed() && $this->approvalRights->isApprovalAllowed() && $this->featureCollection->getFeature("LwListtool_ApprovalSystem")->isActive() && $this->useApprovalSystemListConfig) {
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
            $entity = $response->getDataByKey('EntryEntity');
            
            if($entity->isInApproval() && !$entity->isApproved() && ($this->auth->isLoggedIn() || $entity->isInUserApprovalStarter()) ){
                # Versendung der Erinnerungsmail.
                \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Notification', 'sendNotificationMail', array("entryid" => $entity->getValueByKey("id") ,"listId"=> $this->getContentObjectId(), "filename" => $entity->getValueByKey("opt2file"), "entryname" => $entity->getValueByKey("name"),"cmd" => "remindApproval"));
                
                # Datum speichern, wann die Erinnerungsmail verschickt wurde.
                \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'setDateSendApprovalReminder', array("id"=> $entity->getValueByKey("id")));

                # Dialoag der Genehmigungsstatistik neu laden.
                return $this->showApprovalEntryStatusAction();
            }
        }
        return $this->showListAction();
    }
    
    /**
     *              /F021/
     * 
     * Eine Datei als genehmigt markieren bei 75% oder mehr "Ja-Stimmen". 
     */
    public function approveEntryAction()
    {
        if ($this->listRights->isWriteAllowed() && $this->approvalRights->isApprovalAllowed() && $this->featureCollection->getFeature("LwListtool_ApprovalSystem")->isActive() && $this->useApprovalSystemListConfig) {
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getEntryEntityById', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
            $entity = $response->getDataByKey('EntryEntity');
            
            # Prüfung ob der Eintrag genehmigt werden kann und ob der Genehmigungsstarter bzw. ein Backend-Administrator eingeloggt ist
            if($entity->isInApproval() && !$entity->isApproved() && ($this->auth->isLoggedIn() || $entity->isInUserApprovalStarter()) ){
                
                # Statistik des Genehmigungsverfahrens laden
                $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getApprovalStatistics', array("id"=>$this->request->getInt("id"), "listId"=>$this->getContentObjectId()));
                $results = $response->getDataByKey('results'); 
                
                # /F070/ + /F160/ es müssen minimum 75% "Ja"-Stimmen abgegeben worden sein
                if($results["voted_yes_percent"] >= 75 && ( date("YmdHis") >= $entity->getValueByKey('opt7number') || $results["participant_quote"] == 100 ) ){
                    \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'setEntryApproved', array("id"=>$this->request->getInt("id")));
                     return $this->buildReloadResponse(array("cmd"=>"showList", "reloadParent"=>1));
                }
            }
        }
        return $this->showListAction();
    }
}