<?php

namespace LwListtool\View;

class ConfigurationForm extends \LWmvc\View
{
    public function __construct($type)
    {
        parent::__construct('edit');
        $this->dic = new \LwListtool\Services\dic();
        $this->view = new \lw_view(dirname(__FILE__).'/templates/ConfigurationForm.tpl.phtml');
        $this->systemConfiguration = $this->dic->getConfiguration();
    }

    public function render()
    {
        $this->view->actionUrl = $this->systemConfiguration['url']['client']."admin.php?obj=content&cmd=open&oid=".$this->view->entity->getId()."&pcmd=save";
        $this->view->backUrl = $this->systemConfiguration['url']['client']."admin.php?obj=content";
        $this->view->rightslink = '<a href="#" onClick="openNewWindow(\'' . $this->systemConfiguration['url']['client']."admin.php?obj=content&cmd=open&oid=".$this->view->entity->getId()."&pcmd=assignIntranets&ltid=".$this->view->entity->getId().'\');">Rechtezuweisung</a>';
        $this->view->entity->renderView($this->view);
        return $this->view->render();    
    }
    
    public function setAssignedUser($array)
    {
        $this->view->users = $array;
    }
    
    public function setAssignedIntranets($array)
    {
        $this->view->intranets = $array;
    }
}