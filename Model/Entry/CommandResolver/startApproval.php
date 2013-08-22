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
        if($this->command->getParameterByKey("enddate") == 0){
            $this->command->getResponse()->setParameterByKey('errordate', 1);
        }
        else if($this->command->getParameterByKey("enddate") <= date("Ymd")){
            $this->command->getResponse()->setParameterByKey('errordate', 2);
        }else{
            $ok = $this->getCommandHandler()->startApprovalEntity($this->command->getParameterByKey("id"), $this->command->getParameterByKey("approvalUserId"), $this->command->getParameterByKey("enddate"));
            if ($ok) {
                $this->command->getResponse()->setParameterByKey('startApproval', true);
            }
            else {
                $this->command->getResponse()->setDataByKey('error', 'error startApproval');
                $this->command->getResponse()->setParameterByKey('error', true);
            }     
        }
        return $this->command->getResponse();
    }
}