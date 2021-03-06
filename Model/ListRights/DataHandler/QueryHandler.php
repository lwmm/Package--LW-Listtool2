<?php

/**************************************************************************
*  Copyright notice
*
*  Copyright 2013 Logic Works GmbH
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*  http://www.apache.org/licenses/LICENSE-2.0
*  
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License.
*  
***************************************************************************/

namespace lwListtool\Model\ListRights\DataHandler;

class QueryHandler extends \LWmvc\Model\DataQueryHandler
{
    public function __construct(\lw_db $db)
    {
        $this->db = $db;
        $this->type = "lw_listtool2";
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
        $this->db->setStatement("SELECT t:lw_in_user.name, t:lw_in_user.id FROM t:lw_intra_assign, t:lw_in_user WHERE t:lw_intra_assign.object_type = :objecttype AND t:lw_intra_assign.object_id = :objectid AND t:lw_intra_assign.right_type = :righttype AND t:lw_intra_assign.right_id = t:lw_in_user.id ");
        $this->db->bindParameter('objecttype', 's', 'listtool_cbox');
        $this->db->bindParameter('righttype', 's', 'user');
        $this->db->bindParameter('objectid', 'i', $listId);
        return $this->db->pselect();    
    }
}