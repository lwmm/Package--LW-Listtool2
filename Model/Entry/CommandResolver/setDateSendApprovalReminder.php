<?php

namespace LwListtool\Model\Entry\CommandResolver;

class setDateSendApprovalReminder extends \LWmvc\Model\CommandResolver
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
        return new setDateSendApprovalReminder($command);
    }
    
    public function resolve()
    {
        $ok = $this->getCommandHandler()->setDateSendApprovalReminder($this->command->getParameterByKey("id"));
        if ($ok) {
            $this->command->getResponse()->setParameterByKey('approvalReminder', true);
        }
        else {
            $this->command->getResponse()->setDataByKey('error', 'error setDateSendApprovalReminder');
            $this->command->getResponse()->setParameterByKey('error', true);
        }     

        return $this->command->getResponse();
    }
}