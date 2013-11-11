<?php
/**
 *              /F010/
 * 
 * Das Objekt für die Genehmigungsrechte wird geladen.
 */

namespace lwListtool\Model\ApprovalRights\CommandResolver;

class getApprovalRightsObject extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \LwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\ApprovalRights\\";
        $this->ObjectClass = $this->baseNamespace."Object\\approvalRights";
    }
    
    public function getInstance($command)
    {
        return new getApprovalRightsObject($command);
    }
    
    public function resolve()
    {
        $object = new $this->ObjectClass();
        $object->setAuthObject($this->dic->getLwAuth());
        $object->setInAuthObject($this->dic->getLwInAuth());
        $object->setListConfigration($this->command->getParameterByKey('listConfig'));
        $object->setListId($this->command->getParameterByKey('listId'));
        
        $this->command->getResponse()->setDataByKey('approvalRightsObject', $object);
        return $this->command->getResponse();       
    }

}