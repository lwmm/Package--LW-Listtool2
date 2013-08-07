<?php

namespace LwListtool\Model\Entry\CommandResolver;

class release extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \lwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\Entry\\";
        $this->ObjectClass = $this->baseNamespace."Object\\entry";
    }
    
    public function getInstance($command)
    {
        return new release($command);
    }
    
    public function resolve()
    {
        $ok = $this->getCommandHandler()->releaseEntity($this->command->getParameterByKey("id"));
        if ($ok) {
            $this->command->getResponse()->setParameterByKey('released', true);
        }
        else {
            $this->command->getResponse()->setDataByKey('error', 'error releasing');
            $this->command->getResponse()->setParameterByKey('error', true);
        }                    
        return $this->command->getResponse();
    }
}