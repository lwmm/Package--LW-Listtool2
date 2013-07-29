<?php

namespace LwListtool\View;

class ListtoolVersionsList extends \LWmvc\View
{
    public function __construct($type)
    {
        parent::__construct($type);
        $this->dic = new \LwListtool\Services\dic();
        $this->systemConfiguration = $this->dic->getConfiguration();
        $this->view = new \lw_view(dirname(__FILE__).'/templates/VersionsList.tpl.phtml');
    }

    public function setListRights($rights)
    {
        $this->listRights = $rights;
    }
    
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }
    
    public function setListId($id)
    {
        $this->listId = $id;
    }
    
    public function render()
    {
        return $this->view->render();
    }
}
