<?php

namespace LwListtool\Model\Entry\CommandResolver;

class startApproval extends \LWmvc\Model\CommandResolver
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
        return new startApproval($command);
    }
    
    public function resolve()
    {
        $ok = $this->getCommandHandler()->startApprovalEntity($this->command->getParameterByKey("id"), $this->command->getParameterByKey("approvalUserId"));
        if ($ok) {
            $this->command->getResponse()->setParameterByKey('startApproval', true);
        }
        else {
            $this->command->getResponse()->setDataByKey('error', 'error startApproval');
            $this->command->getResponse()->setParameterByKey('error', true);
        }                    
        return $this->command->getResponse();
    }
}