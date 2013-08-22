<?php

namespace LwListtool\Model\Entry\DataHandler;

class QueryHandler extends \LWmvc\Model\DataQueryHandler
{
    public function __construct(\lw_db $db)
    {
        $this->db = $db;
        $this->type = "lw_listtool2";
    }
    
    public function loadAllEntriesByListId($listId, $sorting, $writeAllowed)
    {
        if (!$sorting) {
            $sorting = "name";
        }
        if (!$writeAllowed) {
            $where = " AND published = 1 ";
        }
        $this->db->setStatement("SELECT * FROM t:lw_master WHERE lw_object = :type AND category_id = :category ".$where." ORDER BY :orderby ASC");
        $this->db->bindParameter("type", "s", $this->type);
        $this->db->bindParameter("category", "i", $listId);
        $this->db->bindParameter("orderby", "f", $sorting);
        $results = $this->db->pselect();
        foreach($results as $result) {
            $array[] = new \LWmvc\Model\DTO($result);
        }
        return $array;
    }
    
    public function loadEntryById($id, $listId)
    {
        $this->db->setStatement("SELECT * FROM t:lw_master WHERE lw_object = :type AND category_id = :category AND id = :id");
        $this->db->bindParameter("type", "s", $this->type);
        $this->db->bindParameter("category", "i", $listId);
        $this->db->bindParameter("id", "i", $id);
        $result = $this->db->pselect1();
        return new \LWmvc\Model\DTO($result);
    }
    
    public function getIntranetsByListId($listId)
    {
        $this->db->setStatement("SELECT t:lw_intranets.name, t:lw_intranets.id FROM t:lw_intra_assign, t:lw_intranets WHERE t:lw_intra_assign.object_type = :objecttype AND t:lw_intra_assign.object_id = :objectid AND t:lw_intra_assign.right_type = :righttype AND t:lw_intra_assign.right_id = t:lw_intranets.id ");
        $this->db->bindParameter('objecttype', 's', 'listtool_cbox');
        $this->db->bindParameter('righttype', 's', 'intranet');
        $this->db->bindParameter('objectid', 'i', $listId);
        return $this->db->pselect();
    }
        
    public function getUserByListId($listId)
    {
        $this->db->setStatement("SELECT t:lw_in_user.name, t:lw_in_user.id, t:lw_in_user.email FROM t:lw_intra_assign, t:lw_in_user WHERE t:lw_intra_assign.object_type = :objecttype AND t:lw_intra_assign.object_id = :objectid AND t:lw_intra_assign.right_type = :righttype AND t:lw_intra_assign.right_id = t:lw_in_user.id ");
        $this->db->bindParameter('objecttype', 's', 'listtool_cbox');
        $this->db->bindParameter('righttype', 's', 'user');
        $this->db->bindParameter('objectid', 'i', $listId);
        return $this->db->pselect();    
    }
    
    public function getUserByIntranetId($iid)
    {
        $this->db->setStatement("SELECT name, id, email FROM t:lw_in_user WHERE intranet_id = :iid ");
        $this->db->bindParameter("iid", "i", $iid);
        
        return $this->db->pselect();
    }
    
    public function getVotingsByEntryId($id, $listid)
    {
        $this->db->setStatement("SELECT opt1bool, opt1text, lw_first_date, lw_first_user  FROM t:lw_master WHERE lw_object = :lw_object AND opt1number = :opt1number AND category_id = :category_id ");
        $this->db->bindParameter("lw_object", "s", "lw_listtool2_vote");
        $this->db->bindParameter("opt1number", "i", $id);
        $this->db->bindParameter("category_id", "i", $listid);
        
        return $this->db->pselect();
    }
}