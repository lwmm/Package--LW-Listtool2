<?php

namespace lwListtool\Domain\ListRights\EventResolver;

class getRightsByListId extends \LWddd\DomainEventResolver
{
    public function __construct($event)
    {
        parent::__construct($event);
        $this->dic = new \lwListtool\Services\dic();
        $this->baseNamespace = "\\lwListtool\\Domain\\ListRights\\";
        $this->ObjectClass = $this->baseNamespace."Object\\listRights";
    }
    
    public function getInstance($event)
    {
        return new getRightsByListId($event);
    }
    
    public function resolve()
    {
    }
}