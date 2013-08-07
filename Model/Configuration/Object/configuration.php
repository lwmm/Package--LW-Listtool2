<?php

namespace LwListtool\Model\Configuration\Object;

class configuration extends \LWmvc\Model\Entity
{
    public function __construct($id=false)
    {
        parent::__construct($id);
    }
    
    public function renderView($view)
    {
        $view->entity = $this;
    }    
}