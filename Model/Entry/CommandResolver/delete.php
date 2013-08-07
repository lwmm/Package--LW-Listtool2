<?php

namespace LwListtool\Model\Entry\CommandResolver;

class delete extends \LWmvc\Model\CommandResolver
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
        return new delete($command);
    }
    
    public function resolve()
    {
        $config = $this->dic->getConfiguration();
        $this->getCommandHandler()->setFilePath($config['path']['listtool']);
        $ok = $this->getCommandHandler()->deleteEntity($this->command->getParameterByKey("id"), $this->command->getParameterByKey("listId"));
        if ($ok) {
            $this->command->getResponse()->setParameterByKey('deleted', true);
        }
        else {
            $this->command->getResponse()->setDataByKey('error', 'error deleting');
            $this->command->getResponse()->setParameterByKey('error', true);
        }                    
        return $this->command->getResponse();
    }
}