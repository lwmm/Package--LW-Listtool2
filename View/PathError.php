<?php

namespace LwListtool\View;

class PathError extends \LWmvc\View
{
    public function __construct($type)
    {
        parent::__construct('edit');
    }

    public function render()
    {
        $this->view = new \lw_view(dirname(__FILE__).'/templates/PathError.tpl.phtml');
        $this->view->backUrl = $this->systemConfiguration['url']['client']."admin.php?obj=content";
        return $this->view->render();    
    }
}