<?php

namespace LwListtool\Domain\Configuration\Object;

class configuration extends \LWddd\Entity
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