<?php

namespace LwListtool\View;

class Popup extends \LWmvc\View
{
    public function __construct()
    {
        parent::__construct('edit');
        $this->dic = new \LwListtool\Services\dic();
        $this->view = new \lw_view(dirname(__FILE__).'/templates/Popup.tpl.phtml');
    }

    public function setForm($form)
    {
        $this->view->form = $form;
    }
    
    public function render()
    {
        $this->dic->getLwResponse()->usejQuery();
        $this->dic->getLwResponse()->usejQueryUI();
        $this->view->jquery = $this->dic->getLwResponse()->getJQueryIncludes();
        return $this->view->render();
    }
}