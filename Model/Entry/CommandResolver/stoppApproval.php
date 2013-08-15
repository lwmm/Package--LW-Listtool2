<?php

namespace LwListtool\Model\Entry\CommandResolver;

class stoppApproval extends \LWmvc\Model\CommandResolver
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
        return new stoppApproval($command);
    }
    
    public function resolve()
    {
        $ok = $this->getCommandHandler()->stoppApprovalEntity($this->command->getParameterByKey("id"));
        if ($ok) {
            $this->command->getResponse()->setParameterByKey('stoppApproval', true);
        }
        else {
            $this->command->getResponse()->setDataByKey('error', 'error stoppApproval');
            $this->command->getResponse()->setParameterByKey('error', true);
        }                    
        return $this->command->getResponse();
    }
}