<?php

namespace LwListtool\Domain\Entry\EventResolver;

class sort extends \LWddd\DomainEventResolver
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
        return new sort($event);
    }
    
    public function resolve()
    {
        $array = $this->event->getDataByKey("postArray");
        $neworder = explode(":", $array['neworder']);
        $order = 1;
        foreach($neworder as $id) {
            if (strlen(trim($id))>0) {
                $ok = $this->getCommandHandler()->saveSequence($id, $order);
                $order++;
            }
        }
        $this->event->getResponse()->setParameterByKey('sorted', true);
        return $this->event->getResponse();
    }
}