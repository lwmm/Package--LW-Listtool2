<?php

namespace lwListtool\Domain\ListRights\EventResolver;

class getIntranetsByListId extends \LWddd\DomainEventResolver
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
        return new getIntranetsByListId($event);
    }
    
    public function resolve()
    {
        $result = $this->getQueryHandler()->getIntranetsByListId($this->event->getParameterByKey('listId'));
        $this->event->getResponse()->setDataByKey('IntranetsArray', $result);
        return $this->event->getResponse();       
    }
}