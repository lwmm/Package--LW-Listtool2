<?php

namespace LwListtool\Domain\Entry\EventResolver;

class deleteThumbnail extends \LWddd\DomainEventResolver
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
        return new deleteThumbnail($event);
    }
    
    public function resolve()
    {
        $config = $this->dic->getConfiguration();
        $this->getCommandHandler()->setFilePath($config['path']['listtool']);
        $thumbnailId = $this->event->getParameterByKey("id");

        $ok = $this->getCommandHandler()->deleteThumbnail($thumbnailId);
        if ($ok) {
            $this->event->getResponse()->setParameterByKey('thumbnail deleted', true);
        }
        else {
            $this->event->getResponse()->setDataByKey('error', 'error deleting thumbnail');
            $this->event->getResponse()->setParameterByKey('error', true);
        }                    
        return $this->event->getResponse();
    }
}