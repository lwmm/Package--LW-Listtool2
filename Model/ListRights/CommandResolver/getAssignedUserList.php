<?php 

/*
 $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getAllReadersByPageId', array("pageId"=>\lw_page::getInstance()->getId()));
 $users = $response->getDataByKey('UserArray');
 */

namespace LwListtool\Model\ListRights\CommandResolver;

class getAssignedUserList extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \lwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\ListRights\\";
        $this->ObjectClass = $this->baseNamespace."Object\\listRights";
    }
    
    public function getInstance($command)
    {
        return new getAssignedUserList($command);
    }
    
    public function resolve()
    {
        $result = $this->getQueryHandler()->getAssignedUserList($this->command->getParameterByKey('users'));
        $this->command->getResponse()->setDataByKey('userList', $result);
        return $this->command->getResponse();       
    }
}