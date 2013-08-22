<?php

namespace LwListtool\Model\Notification\DataHandler;

class QueryHandler extends \LWmvc\Model\DataQueryHandler
{
    public function __construct(\lw_db $db)
    {
        $this->db = $db;
    }
    
    public function getListnameByListId($lang)
    {
        $this->db->setStatement("SELECT * FROM t:lw_i18n WHERE category = :category AND lw_key = 'lang_listtitle' AND language = :lang ");
        $this->db->bindParameter("category", "s", "lw_listtool2");
        $this->db->bindParameter("lang", "s", $lang);
        $result = $this->db->pselect1();

        return $result["text"];
    }
    
    public function getEmailByInUserId($uid)
    {
        $this->db->setStatement("SELECT email FROM t:lw_in_user WHERE id = :id ");
        $this->db->bindParameter("id", "i", $uid);        
        $result = $this->db->pselect1();

        return $result["email"];
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
}