<?php

namespace LwListtool\Model\Notification\DataHandler;

class QueryHandler extends \LWmvc\Model\DataQueryHandler
{
    public function __construct(\lw_db $db)
    {
        $this->db = $db;
    }
    
    public function getListnameByListId($listId, $lang)
    {
        $this->db->setStatement("SELECT * FROM t:lw_i18n WHERE category = :category AND lw_key = 'lang_listtitle' AND language = :lang ");
        $this->db->bindParameter("category", "s", "lw_listtool2_".$listId);
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
}