<?php 

namespace LwListtool\Model\ApprovalRights\CommandResolver;

class saveAssignedApprovalAdmins extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \lwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\ApprovalRights\\";
        $this->ObjectClass = $this->baseNamespace."Object\\approvalRights";
    }
    
    public function getInstance($command)
    {
        return new saveAssignedApprovalAdmins($command);
    }
    
    public function resolve()
    {
        $result = $this->getCommandHandler()->saveAssignedApprovalAdmins($this->command->getParameterByKey('listId'),$this->command->getParameterByKey('adminIds'));
        $this->command->getResponse()->setParameterByKey('approvalAdminsSaved', $result);
        return $this->command->getResponse();       
    }
}