<?php

namespace LwListtool\Domain\Entry\EventResolver;

class borrow extends \LWddd\DomainEventResolver
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
        return new borrow($event);
    }
    
    public function resolve()
    {
        $ok = $this->getCommandHandler()->borrowEntity($this->event->getParameterByKey("id"), $this->event->getParameterByKey("borrowerId"));
        if ($ok) {
            $this->event->getResponse()->setParameterByKey('borrowed', true);
        }
        else {
            $this->event->getResponse()->setDataByKey('error', 'error borrowing');
            $this->event->getResponse()->setParameterByKey('error', true);
        }                    
        return $this->event->getResponse();
    }
}