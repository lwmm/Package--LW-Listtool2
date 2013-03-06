<?php

namespace LwListtool\Domain\Entry\EventResolver;

class getEntryEntityFromPostArray extends \LWddd\DomainEventResolver
{
    public function __construct($event)
    {
        parent::__construct($event);
        $this->dic = new \LwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Domain\\Entry\\";
        $this->ObjectClass = $this->baseNamespace."Object\\entry";
    }
    
    public function getInstance($event)
    {
        return new getEntryEntityFromPostArray($event);
    }
    
    public function resolve()
    {
        $entity = $this->buildEntityFromArray($this->event->getDataByKey('postArray'));
        $entity->unsetLoaded();
        $this->event->getResponse()->setDataByKey('EntryEntity', $entity);
        return $this->event->getResponse();       
    }
    
}