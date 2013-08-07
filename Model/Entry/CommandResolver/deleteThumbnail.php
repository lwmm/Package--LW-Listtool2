<?php

namespace LwListtool\Model\Entry\CommandResolver;

class deleteThumbnail extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \LwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\Entry\\";
        $this->ObjectClass = $this->baseNamespace."Object\\entry";
    }
    
    public function getInstance($command)
    {
        return new deleteThumbnail($command);
    }
    
    public function resolve()
    {
        $config = $this->dic->getConfiguration();
        $this->getCommandHandler()->setFilePath($config['path']['listtool']);
        $thumbnailId = $this->command->getParameterByKey("id");

        $ok = $this->getCommandHandler()->deleteThumbnail($thumbnailId);
        if ($ok) {
            $this->command->getResponse()->setParameterByKey('thumbnail deleted', true);
        }
        else {
            $this->command->getResponse()->setDataByKey('error', 'error deleting thumbnail');
            $this->command->getResponse()->setParameterByKey('error', true);
        }                    
        return $this->command->getResponse();
    }
}