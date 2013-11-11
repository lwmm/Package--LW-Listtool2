<?php 

namespace LwListtool\Model\ApprovalRights\CommandResolver;

class checkExistanceOfAssignedApprovalAdmins extends \LWmvc\Model\CommandResolver
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
        return new checkExistanceOfAssignedApprovalAdmins($command);
    }
   
    public function resolve()
    {
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getAllReadersByListId', array("listId"=>$this->command->getParameterByKey('listId')));
        $users = $response->getDataByKey('UserArray');
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getAssignedUserList', array("users"=>$users));
        $assignedUsers = $response->getDataByKey('userList');

        foreach($assignedUsers as $user){
            $assignedUsersIDs[$user["id"]] = true;
        }

        $existingApprovalAdmins = $this->getQueryHandler()->getAllAssignedApprovalAdminsByListIds($this->command->getParameterByKey('listId'));

        #die aktuell zugewiesenen Genehmigungsadministratoren mit den gespeicherten vergleichen
        #und ggf. alte und nicht mehr zugewiesene Administratoren entfernen.
        foreach($existingApprovalAdmins as $adminID => $flagg){
            if(!array_key_exists($adminID, $assignedUsersIDs)){
                $this->getCommandHandler()->deleteAssignedApprovalAdminById($this->command->getParameterByKey('listId'), $adminID);
            }
        }

        $this->command->getResponse()->setParameterByKey('approvalAdminsChecked', true);
        return $this->command->getResponse();       
    }
}