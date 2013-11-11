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
   
    /**
     *              /F010/
     * 
     * Die Intranet-User IDs werden gespeichert, die in der
     * Listtool-Konfiguration als Genehmigungsadministrator
     * markiert wurden.
     * 
     * Die Rechtezuweiseung muss für jedes eingebundene
     * Listtool einzelnd vorgenommen werden.
     */
    public function resolve()
    {
        $existingApprovalAdmins = $this->getQueryHandler()->getAllAssignedApprovalAdminsByListIds($this->command->getParameterByKey('listId'));

        foreach($existingApprovalAdmins as $adminID => $flagg){
            if(!array_key_exists($adminID, $this->command->getParameterByKey('adminIds'))){
                $this->getCommandHandler()->deleteAssignedApprovalAdminById($this->command->getParameterByKey('listId'), $adminID);
            }
        }

        $result = $this->getCommandHandler()->saveAssignedApprovalAdmins($this->command->getParameterByKey('listId'),$this->command->getParameterByKey('adminIds'));
        $this->command->getResponse()->setParameterByKey('approvalAdminsSaved', $result);
        return $this->command->getResponse();       
    }
}