<?php

namespace LwListtool\View;

class StatusApproval extends \LWmvc\View\View
{
    public function __construct()
    {
        parent::__construct();
        $this->dic = new \LwListtool\Services\dic();
        $this->systemConfiguration = $this->dic->getConfiguration();
        $this->view = new \lw_view(dirname(__FILE__).'/templates/StatusApproval.tpl.phtml');
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
    
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }   
    
    public function setResults($results)
    {
        $this->results = $results;
    }
    
    public function setLanguagePhrases($langPhrases)
    {
        $this->langPhrases = $langPhrases;
    }

    public function render()
    {
        $this->view->mediaUrl = $this->systemConfiguration['url']['media'];
        $this->view->langPhrases = $this->langPhrases;
        $this->view->entry = $this->entity;
        $this->view->error = $this->error;
        $this->view->stoppApprovalUrl = \lw_page::getInstance()->getUrl(array("cmd" => "stoppApproval", "id" => $this->entity->getValueByKey('id')));
        $this->view->remindApprovalUrl = \lw_page::getInstance()->getUrl(array("cmd" => "sendReminderApprovalMail", "id" => $this->entity->getValueByKey('id')));
        $this->view->approveEntryUrl = \lw_page::getInstance()->getUrl(array("cmd" => "approveEntry", "id" => $this->entity->getValueByKey('id')));
        $this->view->results = $this->results;
        $this->view->colorArray = array("#78ffa4", "#ff7878", "#788eff", "#aeff78", "#cb78ff", "#fffa78", "#ffbb78", "#d4ff78", "#b8f5fb", "#ab78ff", "#00CBFF");
        
        if ($this->configuration->getValueByKey('language') == "de") {
            $this->view->lang = "de";
        }
        else {
            $this->view->lang = "en";
        }
        
        $form = $this->view->render();
        $popupView = new \LwListtool\View\Popup();
        $popupView->setForm($form);
        return $popupView->render();
    }
}
