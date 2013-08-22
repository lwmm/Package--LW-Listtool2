<?php

namespace LwListtool\Model\Entry\CommandResolver;

class voteApproval extends \LWmvc\Model\CommandResolver
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
        return new voteApproval($command);
    }
    
    public function resolve()
    {
        $array = $this->command->getDataByKey('postArray');
        if($array["lt_vote"] == 0 && $array["lt_comment"] == ""){
            $this->command->getResponse()->setParameterByKey('errorvote', 1);
        }else if($array["lt_vote"] == 0 && strlen($array["lt_comment"]) > 255){
            $this->command->getResponse()->setParameterByKey('errorvote', 2);
        }
        else{
            $ok = $this->getCommandHandler()->addVoteApprovalEntry($this->command->getParameterByKey("id"), $this->command->getParameterByKey("approvalUserId"), $this->command->getParameterByKey("listId"), $array);
            if ($ok) {
                $this->command->getResponse()->setParameterByKey('voteApproval', true);
            }
            else {
                $this->command->getResponse()->setDataByKey('error', 'error voteApproval');
                $this->command->getResponse()->setParameterByKey('error', true);
            }     
        }
        return $this->command->getResponse();
    }
}