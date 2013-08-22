<?php

namespace LwListtool\View;

class StartApprovalForm extends \LWmvc\View\View
{
    public function __construct()
    {
        parent::__construct();
        $this->dic = new \LwListtool\Services\dic();
        $this->systemConfiguration = $this->dic->getConfiguration();
        $this->view = new \lw_view(dirname(__FILE__).'/templates/StartApprovalForm.tpl.phtml');
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
    
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }   
    
    public function setError($error)
    {
        $this->error = $error;
    }

    public function render()
    {
        $this->view->actionUrl = \lw_page::getInstance()->getUrl(array("cmd" => "startApproval", "id" => $this->entity->getValueByKey('id')));
        $this->view->mediaUrl = $this->systemConfiguration['url']['media'];
        $this->view->entry = $this->entity;
        $this->view->error = $this->error;
        
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
