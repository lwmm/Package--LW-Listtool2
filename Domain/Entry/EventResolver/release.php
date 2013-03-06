<?php

namespace LwListtool\Domain\Entry\EventResolver;

class release extends \LWddd\DomainEventResolver
{
    public function __construct($event)
    {
        parent::__construct($event);
        $this->dic = new \lwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Domain\\Entry\\";
        $this->ObjectClass = $this->baseNamespace."Object\\entry";
    }
    
    public function getInstance($event)
    {
        return new release($event);
    }
    
    public function resolve()
    {
        $ok = $this->getCommandHandler()->releaseEntity($this->event->getParameterByKey("id"));
        if ($ok) {
            $this->event->getResponse()->setParameterByKey('released', true);
        }
        else {
            $this->event->getResponse()->setDataByKey('error', 'error releasing');
            $this->event->getResponse()->setParameterByKey('error', true);
        }                    
        return $this->event->getResponse();
    }
}