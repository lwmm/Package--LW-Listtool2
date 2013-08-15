<?php 

/*
 $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getAllReadersByPageId', array("pageId"=>\lw_page::getInstance()->getId()));
 $users = $response->getDataByKey('UserArray');
 */

namespace LwListtool\Model\ListRights\CommandResolver;

class getAllReadersByPageId extends \LWmvc\Model\CommandResolver
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
        return new getAllReadersByPageId($command);
    }
    
    public function resolve()
    {
        $result = $this->getQueryHandler()->getAllReadersByPageId($this->command->getParameterByKey('pageId'));
        $this->command->getResponse()->setDataByKey('UserArray', $result);
        return $this->command->getResponse();       
    }
}