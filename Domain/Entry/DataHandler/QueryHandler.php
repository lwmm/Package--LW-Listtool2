<?php

namespace LwListtool\Domain\Entry\DataHandler;

class QueryHandler 
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
        return $this->db->pselect();
    }
    
    public function loadEntryById($id, $listId)
    {
        $this->db->setStatement("SELECT * FROM t:lw_master WHERE lw_object = :type AND category_id = :category AND id = :id");
        $this->db->bindParameter("type", "s", $this->type);
        $this->db->bindParameter("category", "i", $listId);
        $this->db->bindParameter("id", "i", $id);
        return $this->db->pselect1();
    }
}