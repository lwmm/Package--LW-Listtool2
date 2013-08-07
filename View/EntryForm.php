<?php

namespace LwListtool\View;

class EntryForm extends \LWmvc\View\View
{
    public function __construct($type)
    {
        parent::__construct($type);
        $this->dic = new \LwListtool\Services\dic();
        $this->systemConfiguration = $this->dic->getConfiguration();
        $this->view = new \lw_view(dirname(__FILE__).'/templates/EntryForm.tpl.phtml');
        $this->setEntryType($type);
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }    
    
    public function setEntryType($type)
    {
        if ($type != "file") {
            $type = "link";
            $this->view->typeSwitch = "1";
        }
        else {
            $this->view->typeSwitch = "0";
        }
        $this->entryType = $type;
    }
    
    public function setArchiveValues($archive, $archivedFiles)
    {
        $this->archive = $archive;
        $this->archivedFiles = $archivedFiles;
    }

    public function render()
    {
        $this->view->mediaUrl = $this->systemConfiguration['url']['media'];
        if ($this->view->entity->getId()<1) {
            $this->view->actionUrl = \lw_page::getInstance()->getUrl(array("cmd"=>"addEntry", "type" => $this->entryType));
            if ($this->entryType == "file") {
                $this->view->addFile = true;
            }
            else {
                $this->view->addLink = true;
            }
        }
        else {
            $this->view->actionUrl = \lw_page::getInstance()->getUrl(array("cmd"=>"saveEntry", "id"=>$this->view->entity->getId()));
        }
        
        if ($this->configuration->getValueByKey('language') == "de") {
            $this->view->lang = "de";
        }
        else {
            $this->view->lang = "en";
        }
        
        if($this->archive) {
            $this->view->archive = true;
            $this->view->archivedFiles = $this->archivedFiles;
        }
        $this->view->baseUrl = \lw_page::getInstance()->getUrl(array("cmd" => "downloadEntry", "id" => $this->view->entity->getId()));
        
        $this->view->deleteThumbnailUrl = \lw_page::getInstance()->getUrl(array("cmd" => "deleteEntryThumbnail"));
        $this->view->entryType = $this->entryType;
        
        $this->view->isWriteAllowed = true;
        $this->view->entry = $this->view->entity;
        $form = $this->view->render();
        $popupView = new \LwListtool\View\Popup();
        $popupView->setForm($form);
        return $popupView->render();
    }
}
