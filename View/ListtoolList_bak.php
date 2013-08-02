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

namespace LwListtool\View;

class ListtoolList extends \LWmvc\View
{
    public function __construct()
    {
        parent::__construct('edit');
        $this->dic = new \LwListtool\Services\dic();
        $this->systemConfiguration = $this->dic->getConfiguration();
        $this->auth = $this->dic->getLwAuth();
        $this->inAuth = $this->dic->getLwInAuth();
    }

    public function setListRights($rights)
    {
        $this->listRights = $rights;
    }
    
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }
    
    public function setLanguagePhrases($langPhrases) {
        $this->langPhrases = $langPhrases;
    }
    
    public function setListId($id)
    {
        $this->listId = $id;
    }
    
    public function init()
    {
        if (filter_var($this->configuration->getValueByKey('template'), FILTER_VALIDATE_INT)) {
            die("page templates are not implemented yet"); //$template = $base . $this->repository->loadTemplateById($this->configuration->getValueByKey('template'));
        }
        else {
            $template = \lw_io::loadFile(dirname(__FILE__).'/listTemplates/'.$this->configuration->getValueByKey('template'));
        }        
        $this->view = new \lw_te($template);
    }    
    
    public function render()
    {
    	$listHasBorrowedItems = false;
        $this->view->reg("listtitle", $this->langPhrases["lang_listtitle"]);
        if ($this->listRights->isReadAllowed()) {
            $this->view->setIfVar('ltRead');
        }
        if ($this->listRights->isWriteAllowed()) {
            $this->view->setIfVar('ltWrite');
        }
        
        $blocktemplate = $this->view->getBlock("entry");
        foreach($this->view->aggregate as $entry)
        {
            $this->view->setIfVar('entries');
            $btpl = new \lw_te($blocktemplate);
            if ($entry->isLink()) {
                $btpl->setIfVar('link');
                $btpl->reg("opt3text", $entry->getValueByKey("opt3text"));
            }
            if ($entry->isFile() && $entry->hasFile()) {
                $btpl->setIfVar('file');
                $btpl->reg("downloadurl", \lw_page::getInstance()->getUrl(array("cmd"=>"downloadEntry", "id"=>$entry->getValueByKey("id"))));
                if (\lw_registry::getInstance()->getEntry('FeatureCollection')->getFeature('LwListtoolVersioning')->isActive()) {
                	$btpl->setIfVar('versioning');
                    $btpl->reg("versioningurl", \lw_page::getInstance()->getUrl(array("cmd"=>"showVersions", "id"=>$entry->getValueByKey("id"))));
                }
            }
            $btpl->reg("deleteurl", \lw_page::getInstance()->getUrl(array("cmd"=>"deleteEntry", "id"=>$entry->getValueByKey("id"))));
            $btpl->reg("id", $entry->getValueByKey("id"));
            
            if ($this->configuration->getValueByKey('showName') == 1) {
                $btpl->setIfVar("showname");
                $btpl->reg("name", $entry->getValueByKey("name"));
            }
            
            if ($this->configuration->getValueByKey('showDate') == 1) {
                $btpl->setIfVar("showdate");
                $btpl->reg("lw_first_date", $entry->getFirstDate());
                if ($this->configuration->getValueByKey('showTime') == 1) {
                    $btpl->setIfVar("showtime");
                    $btpl->reg("lw_first_time", $entry->getFirstTime());
                }
            }
            
            if ($this->configuration->getValueByKey('showLastDate') == 1) {
                $btpl->setIfVar("showlastdate");
                $btpl->reg("lw_last_date", $entry->getLastDate());
                if ($this->configuration->getValueByKey('showTime') == 1) {
                    $btpl->setIfVar("showtime");
                    $btpl->reg("lw_last_time", $entry->getLastTime());
                }
            }
            
            if ($this->configuration->getValueByKey('showFreeDate') == 1) {
                $btpl->setIfVar("showfreedate");
                $btpl->reg("free_date", $entry->getFreeDate());
                if ($this->configuration->getValueByKey('showTime') == 1) {
                    $btpl->setIfVar("showtime");
                    $btpl->reg("free_date", $entry->getFreeTime());
                }
            }    	

            if ($this->configuration->getValueByKey('showDescription') == 1) {
                $btpl->setIfVar("showdescription");
                $btpl->reg("description", html_entity_decode($entry->getValueByKey('description')));
            }
            
            
            
            
            if ($this->configuration->getValueByKey('showThumbnail') == 1) {
                $btpl->setIfVar("showthumbnail");
                
                if($entry->getValueByKey('opt1file') != ""){
                    $btpl->setIfVar("thumbnail_exists");
                    $btpl->reg("thumbnail_url", $entry->getThumbnailUrl());
                }
            }
            
            if ($this->configuration->getValueByKey('showFileDate') == 1) {
                $btpl->setIfVar("showfiledate");
                
               if($entry->getValueByKey('opt2file') != ""){
                    $btpl->setIfVar("file_exists");
                    $btpl->reg("file_date", $entry->getFileDate());
                    if ($this->configuration->getValueByKey('showTime') == 1) {
                        $btpl->setIfVar("showtime");
                        $btpl->reg("file_time", $entry->getFileTime());
                    }
                }
            }
            
            if ($this->configuration->getValueByKey('showKeyWords') == 1) {
                $btpl->setIfVar("showkeywords");
                $btpl->reg("keywords", html_entity_decode($entry->getValueByKey('opt2text')));
            }
            
            if ($this->configuration->getValueByKey('showAdditionalInfo') == 1) {
                $btpl->setIfVar("showadditionalinfo");
                $btpl->reg("additionalinfo", html_entity_decode($entry->getValueByKey('opt1text')));
            }
            
            if ($this->configuration->getValueByKey('showFirstUser') == 1) {
                $btpl->setIfVar("showfirstuser");
                if($entry->getFirstUserName() == ""){
                    $btpl->reg('firstuser', "Admin");
                }else{
                    $btpl->reg('firstuser', $entry->getFirstUserName());
                }
            }
            
            if ($this->configuration->getValueByKey('showLastUser') == 1) {
                $btpl->setIfVar("showlastuser");
                if($entry->getLastUserName() == ""){
                    $btpl->reg('lastuser', "Admin");
                }else{
                    $btpl->reg('lastuser', $entry->getLastUserName());
                }
            }
            
            
            
            $btpl->reg("published", $entry->getValueByKey("published"));
            
            if ($this->listRights->isReadAllowed()) {
                $btpl->setIfVar('ltRead');
            }
            
            if ($this->listRights->isWriteAllowed()) {
                $btpl->setIfVar('ltWrite');
            }
            
            if ($this->configuration->getValueByKey('borrow') == 1) {
                if ($entry->isBorrowed()) {
                    if ($this->auth->isLoggedIn() || $entry->isBorrower($this->inAuth->getUserdata("id"))) {
                    	$listHasBorrowedItems = true;
                        $btpl->setIfVar('showEditOptions');
                        $btpl->setIfVar('showReleaseLink');
                        $btpl->reg("releaseurl", \lw_page::getInstance()->getUrl(array("cmd"=>"releaseEntry", "id"=>$entry->getValueByKey("id"))));
                    }
                    else  {
                        $btpl->setIfVar('borrowed');
                        if($entry->getBorrowerName() == ""){
                            $btpl->reg('borrower', "Admin");
                        }else{
                            $btpl->reg('borrower', $entry->getBorrowerName().' <!-- borrower_id: '.$entry->getBorrowerId().' --> ');
                        }
                    }
                }
                else {
                    $btpl->setIfVar('borrow');
                    $btpl->reg("borrowurl", \lw_page::getInstance()->getUrl(array("cmd"=>"borrowEntry", "id"=>$entry->getValueByKey("id"))));
                }
            }
            else {
                $btpl->setIfVar('showEditOptions');
            }
            
            if ($this->configuration->getValueByKey('showId') == 1) {
                $btpl->setIfVar("showid");
            }   
            
            if ($this->configuration->getValueByKey('showUser') == 1) {
                $btpl->setIfVar("showuser");
                $btpl->reg("last_username", "username");
            }   
            
            if ($this->configuration->getValueByKey('linktype') == 1) {
                $btpl->setIfVar("columnlink");
            }   
            else {
                $btpl->setIfVar("namelink");
            }
            
            if ($this->configuration->getValueByKey('publishedoption') == 0) {
                $btpl->setIfVar("columnpublished");
            }   
            else {
                if ($entry->getValueByKey('published') == 0) {
                    $btpl->setIfVar("rowcolorpublished");
                }
            }
            
            $this->setTexts($btpl);
            
            $bout.= $btpl->parse();
        }
        
        if ($this->configuration->getValueByKey('showName') == 1) {
            $this->view->setIfVar("showname");
        }
        
         if ($this->configuration->getValueByKey('showThumbnail') == 1) {
            $this->view->setIfVar("showthumbnail");
        }
        
        if ($this->configuration->getValueByKey('showDate') == 1) {
            $this->view->setIfVar("showdate");
        }
        
        if ($this->configuration->getValueByKey('showLastDate') == 1) {
            $this->view->setIfVar("showlastdate");
        }
        
        if ($this->configuration->getValueByKey('showFreeDate') == 1) {
            $this->view->setIfVar("showfreedate");
        }
        
        if ($this->configuration->getValueByKey('showFileDate') == 1) {
            $this->view->setIfVar("showfiledate");
        }
        
        if ($this->configuration->getValueByKey('showKeyWords') == 1) {
            $this->view->setIfVar("showkeywords");
        }
        
        if ($this->configuration->getValueByKey('showAdditionalInfo') == 1) {
            $this->view->setIfVar("showadditionalinfo");
        }
        
        if ($this->configuration->getValueByKey('showFirstUser') == 1) {
            $this->view->setIfVar("showfirstuser");
        }
        
        if ($this->configuration->getValueByKey('showLastUser') == 1) {
            $this->view->setIfVar("showlastuser");
        }

        if ($this->configuration->getValueByKey('showDescription') == 1) {
            $this->view->setIfVar("showdescription");
        }
        
        if ($this->configuration->getValueByKey('showId') == 1) {
            $this->view->setIfVar("showid");
        }   
        
        if ($this->configuration->getValueByKey('showUser') == 1) {
            $this->view->setIfVar("showuser");
        }   
        
        if ($this->configuration->getValueByKey('publishedoption') == 0) {
            $this->view->setIfVar("columnpublished");
        }   
        
        if ($this->configuration->getValueByKey('sorting') == "opt1number" && $this->listRights->isWriteAllowed()) {
            $this->view->setIfVar("manualsorting");
        }   
        
        if ($this->configuration->getValueByKey('showcss') == 1) {
            $this->view->setIfVar("showcss");
        }   
        
        if ($this->configuration->getValueByKey('linktype') == 1) {
            $this->view->setIfVar("columnlink");
        }   
        else {
            $this->view->setIfVar("namelink");
        }
        
        $this->setTexts($this->view);
        
        if ($listHasBorrowedItems == true) {
        	$this->view->setIfVar('listHasBorrowedItems');
        }

        if (\lw_registry::getInstance()->getEntry('FeatureCollection')->getFeature('LwListtoolMarkDeleted')->isActive()) {
        	$this->view->setIfVar('markdeleted');
        }
        
        if (\lw_registry::getInstance()->getEntry('FeatureCollection')->getFeature('TempShowDeleted')->isActive()) {
        	$this->view->setIfVar('showdeleted');
        }
        
        $this->view->reg("listId", $this->listId);
        $this->view->putBlock("entry", $bout);
        if ($this->listRights->isWriteAllowed()) {
            $listtoolbase = new \LwListtool\View\ListtoolBase();
            return $listtoolbase->render()."\n".$this->view->parse();
        }
        else {
            return $this->view->parse();
        }
    }
    
    protected function setTexts($tpl)
    {
        $tpl->reg("lang_newfile", $this->langPhrases["lang_newfile"]);
        $tpl->reg("lang_newlink", $this->langPhrases["lang_newlink"]);
        $tpl->reg("lang_sortlist", $this->langPhrases["lang_sortlist"]);
        $tpl->reg("lang_name", $this->langPhrases["lang_name"]);
        $tpl->reg("lang_thumbnail", $this->langPhrases["lang_thumbnail"]);
        $tpl->reg("lang_filedate", $this->langPhrases["lang_filedate"]);
        $tpl->reg("lang_keywords", $this->langPhrases["lang_keywords"]);
        $tpl->reg("lang_additionalinfo", $this->langPhrases["lang_additionalinfo"]);
        $tpl->reg("lang_firstuser", $this->langPhrases["lang_firstuser"]);
        $tpl->reg("lang_lastuser", $this->langPhrases["lang_lastuser"]);
        
        $tpl->reg("lang_date", $this->langPhrases["lang_date"]);
        $tpl->reg("lang_lastdate", $this->langPhrases["lang_lastdate"]);
        $tpl->reg("lang_freedate", $this->langPhrases["lang_freedate"]);
        $tpl->reg("lang_published", $this->langPhrases["lang_published"]);
        
        $tpl->reg("lang_description", $this->langPhrases["lang_description"]);
        $tpl->reg("lang_user", $this->langPhrases["lang_user"]);

        $tpl->reg("lang_link", $this->langPhrases["lang_link"]);       
        $tpl->reg("lang_download", $this->langPhrases["lang_download"]);
        
        $tpl->reg("lang_edit", $this->langPhrases["lang_edit"]);
        $tpl->reg("lang_delete", $this->langPhrases["lang_delete"]);
        $tpl->reg("lang_release", '<span title="' . $this->langPhrases["lang_release_title"] . '">' . $this->langPhrases["lang_release"] . '</span>');
        $tpl->reg("lang_borrow", '<span title="' . $this->langPhrases["lang_borrow_title"] . '">' . $this->langPhrases["lang_borrow"] . '</span>');
        $tpl->reg("lang_reallydelete", $this->langPhrases["lang_reallydelete"]);
        $tpl->reg("lang_borrowedby", $this->langPhrases["lang_borrowedby"]);
        $tpl->reg("lang_noentries", $this->langPhrases["lang_noentries"]);
        
        if ($this->configuration->getValueByKey('language') == "de") {
            $tpl->reg("lang_borrow_message", '<strong>Achtung:</strong> ' . $this->langPhrases["lang_borrow_msg"]);
        }
        else {
            $tpl->reg("lang_borrow_message", '<strong>Attention:</strong> ' . $this->langPhrases["lang_borrow_msg"]);
        }        
    }
}
