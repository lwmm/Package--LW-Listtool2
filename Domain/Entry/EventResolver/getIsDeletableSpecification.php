<?php

namespace LwListtool\Domain\Entry\EventResolver;

class getIsDeletableSpecification extends \LWddd\DomainEventResolver
{
    public function __construct($event)
    {
        parent::__construct($event);
        $this->dic = new \LwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Domain\\Entry\\";
    }
    
    public function getInstance($event)
    {
        return new getIsDeletableSpecification($event);
    }
    
    public function resolve()
    {
        $class = $this->baseNamespace.'Specification\isDeletable';
        $this->event->getResponse()->setDataByKey('isDeletableSpecification', $class::getInstance());
        return $this->event->getResponse();
    }
}