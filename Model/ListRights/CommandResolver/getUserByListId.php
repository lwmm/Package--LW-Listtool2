<?php

namespace lwListtool\Model\ListRights\CommandResolver;

class getUserByListid extends \LWmvc\Model\CommandResolver
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
        return new getUserByListid($command);
    }
    
    public function resolve()
    {
        $result = $this->getQueryHandler()->getUserByListid($this->command->getParameterByKey('listId'));
        $this->command->getResponse()->setDataByKey('UserArray', $result);
        return $this->command->getResponse();       
    }
}