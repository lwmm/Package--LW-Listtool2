<?php

namespace lwListtool\Model\ApprovalRights\DataHandler;

class CommandHandler extends \LWmvc\Model\DataCommandHandler
{
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function saveAssignedApprovalAdmins($listId, $adminIds)
    {
        $this->deleteAssignedApprovalAdmins($listId);
        
        foreach($adminIds as $id => $a){
            
            $this->db->setStatement("INSERT INTO t:lw_intra_assign (object_type, object_id, right_type, right_id) VALUES (:objectType, :objectId, :rightType, :rightId) ");
            $this->db->bindParameter("objectType", "s", "listtool_cbox");
            $this->db->bindParameter("objectId", "i", $listId);
            $this->db->bindParameter("rightType", "s", "user_approval_admin");
            $this->db->bindParameter("rightId", "i", $id);
            
            $this->db->pdbquery();            
        }
        
        return true;
    }
    
    private function deleteAssignedApprovalAdmins($listId)
    {
        $this->db->setStatement("DELETE FROM t:lw_intra_assign WHERE object_id = :objectId AND right_type = :rightType ");
        $this->db->bindParameter("objectId", "i", $listId);
        $this->db->bindParameter("rightType", "s", "user_approval_admin");
        
        return $this->db->pdbquery();
    }
}