<?php

/* * ************************************************************************
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
 * ************************************************************************* */

namespace lwListtool\Model\ApprovalRights\Object;

class approvalRights extends \LWmvc\Model\Entity
{

    public function __construct($id = false)
    {
        parent::__construct($id);
        $this->dic = new \lwListtool\Services\dic();
    }

    public function setAssignedUserArray($array)
    {
        $this->UserArray = $array;
    }

    public function setAssignedIntranetsArray($array)
    {
        $this->IntranetsArray = $array;
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


    public function isAssigned()
    {
        $db = $this->dic->getDbObject();
        
        $db->setStatement("SELECT * FROM t:lw_intra_assign WHERE right_type = :rightType AND right_id = :rightId");
        $db->bindParameter("rightType", "s", "user_approval_admin");
        $db->bindParameter("rightId", "i", $this->inAuth->getUserData("id"));
        
        $result = $db->pselect1();
        
        if(empty($result)){
            return false;
        }
        
        return true;
    }

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