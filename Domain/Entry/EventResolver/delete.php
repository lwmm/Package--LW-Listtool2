<?php

namespace LwListtool\Domain\Entry\EventResolver;

class delete extends \LWddd\DomainEventResolver
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
        return new delete($event);
    }
    
    public function resolve()
    {
        $config = $this->dic->getConfiguration();
        $this->getCommandHandler()->setFilePath($config['path']['listtool']);
        $ok = $this->getCommandHandler()->deleteEntity($this->event->getParameterByKey("id"), $this->event->getParameterByKey("listId"));
        if ($ok) {
            $this->event->getResponse()->setParameterByKey('deleted', true);
        }
        else {
            $this->event->getResponse()->setDataByKey('error', 'error deleting');
            $this->event->getResponse()->setParameterByKey('error', true);
        }                    
        return $this->event->getResponse();
    }
}