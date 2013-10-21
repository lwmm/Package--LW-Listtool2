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

namespace lwListtool\Model\ApprovalRights\DataHandler;

class QueryHandler extends \LWmvc\Model\DataQueryHandler
{
    public function __construct(\lw_db $db)
    {
        $this->db = $db;
        $this->type = "lw_listtool2";
    }
 
    public function getAllAssignedApprovalAdminsByListIs($listId)
    {
        $array = array();
        
        $this->db->setStatement("SELECT right_id FROM t:lw_intra_assign WHERE object_id = :objectId AND right_type = :rightType ");
        $this->db->bindParameter("objectId", "i", $listId);
        $this->db->bindParameter("rightType", "s", "user_approval_admin");
        
        $result = $this->db->pselect();

        foreach($result as $entry){
            $array[$entry["right_id"]] = true;
        }

        return $array;
    }
}