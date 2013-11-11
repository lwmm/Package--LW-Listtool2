<?php 

namespace LwListtool\Model\ApprovalRights\CommandResolver;

class getAssignedApprovalAdmins extends \LWmvc\Model\CommandResolver
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
        return new getAssignedApprovalAdmins($command);
    }
    
    public function resolve()
    {
        $result = $this->getQueryHandler()->getAllAssignedApprovalAdminsByListIds($this->command->getParameterByKey('listId'));
        $this->command->getResponse()->setDataByKey('approvalAdminIds', $result);
        return $this->command->getResponse();       
    }
}