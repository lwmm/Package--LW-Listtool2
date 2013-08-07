<?php

namespace lwListtool\Model\ListRights\EventResolver;

class getRightsByListId extends \LWmvc\Model\CommandResolver
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
        return new getRightsByListId($command);
    }
    
    public function resolve()
    {
    }
}