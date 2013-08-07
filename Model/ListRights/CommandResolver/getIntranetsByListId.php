<?php

namespace lwListtool\Model\ListRights\CommandResolver;

class getIntranetsByListId extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \lwListtool\Services\dic();
        $this->baseNamespace = "\\lwListtool\\Model\\ListRights\\";
        $this->ObjectClass = $this->baseNamespace."Object\\listRights";
    }
    
    public function getInstance($command)
    {
        return new getIntranetsByListId($command);
    }
    
    public function resolve()
    {
        $result = $this->getQueryHandler()->getIntranetsByListId($this->command->getParameterByKey('listId'));
        $this->command->getResponse()->setDataByKey('IntranetsArray', $result);
        return $this->command->getResponse();       
    }
}