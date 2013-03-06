<?php

namespace lwListtool\Domain\ListRights\EventResolver;

class getUserByListid extends \LWddd\DomainEventResolver
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
        return new getUserByListid($event);
    }
    
    public function resolve()
    {
        $result = $this->getQueryHandler()->getUserByListid($this->event->getParameterByKey('listId'));
        $this->event->getResponse()->setDataByKey('UserArray', $result);
        return $this->event->getResponse();       
    }
}