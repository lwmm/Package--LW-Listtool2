<?php 

/*
 $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getAllReadersByPageId', array("pageId"=>\lw_page::getInstance()->getId()));
 $users = $response->getDataByKey('UserArray');
 */

namespace LwListtool\Model\ListRights\CommandResolver;

class getAllReadersByListId extends \LWmvc\Model\CommandResolver
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
        return new getAllReadersByListId($command);
    }
    
    public function resolve()
    {
        $result = $this->getQueryHandler()->getAllReadersByListId($this->command->getParameterByKey('listId'));
        $this->command->getResponse()->setDataByKey('UserArray', $result);
        return $this->command->getResponse();       
    }
}