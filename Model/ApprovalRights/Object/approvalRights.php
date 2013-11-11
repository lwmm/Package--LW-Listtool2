<?php

namespace lwListtool\Model\ApprovalRights\Object;

class approvalRights extends \LWmvc\Model\Entity
{

    public function __construct($id = false)
    {
        parent::__construct($id);
        $this->dic = new \lwListtool\Services\dic();
    }

    public function setAuthObject($auth)
    {
        $this->auth = $auth;
    }

    public function setInAuthObject($InAuth)
    {
        $this->inAuth = $InAuth;
    }

    public function setListConfigration($config)
    {
        $this->listConfig = $config;
    }
    
    public function setListId($listId)
    {
        $this->listId = $listId;
    }

    /**
     *              /F010/
     * 
     * Prüfung ob der eingeloggte Intranet-User auch über 
     * Genehmigungsadministratorrechte verfügt.
     */
    public function isAssigned()
    {
        $db = $this->dic->getDbObject();
        
        $db->setStatement("SELECT * FROM t:lw_intra_assign WHERE right_type = :rightType AND object_id = :objectId AND right_id = :rightId ");
        $db->bindParameter("rightType", "s", "approval_admin");
        $db->bindParameter("rightId", "i", $this->inAuth->getUserData("id"));
        $db->bindParameter("objectId", "i", $this->listId);
        
        $result = $db->pselect1();
        
        if(empty($result)){
            return false;
        }
        
        return true;
    }
    
    /**
     *              /F010/ 
     * 
     * Es wird geprüft ob für den eingeloggten Benuzter das 
     * Genehmigungsystem zugänglich gemacht werden darf.
     */
    public function isApprovalAllowed()
    {
        $type = $this->listConfig->getValueByKey('listtooltype');
        if ($this->auth->isLoggedIn() && $type != "intranet") {
            return true;
        }
        if ($this->inAuth->isLoggedIn() && $type != "backend" && $this->isAssigned()) {
            return true;
        }
        return false;
    }

}