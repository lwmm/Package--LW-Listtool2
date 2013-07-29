<?php

namespace LwListtool\Domain\Entry\EventResolver;

class getListEntriesAggregate extends \LWddd\DomainEventResolver
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
        return new getListEntriesAggregate($event);
    }
    
    public function resolve()
    {
        $conf = $this->event->getParameterByKey("configuration");
        $listRights = $this->event->getParameterByKey("listRights");
        $items = $this->getQueryHandler()->loadAllEntriesByListId($this->event->getParameterByKey("listId"), $conf->getValueByKey("sorting"), $listRights->isWriteAllowed());
        $aggregate = $this->buildAggregateFromQueryResult($items, true);        
        $this->event->getResponse()->setDataByKey('listEntriesAggregate', $aggregate);
        return $this->event->getResponse();        
    }
    
}
