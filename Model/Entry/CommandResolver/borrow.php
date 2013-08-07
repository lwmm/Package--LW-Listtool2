<?php

namespace LwListtool\Model\Entry\CommandResolver;

class borrow extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \LwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\Entry\\";
        $this->ObjectClass = $this->baseNamespace."Object\\entry";
    }
    
    public function getInstance($command)
    {
        return new borrow($command);
    }
    
    public function resolve()
    {
        $ok = $this->getCommandHandler()->borrowEntity($this->command->getParameterByKey("id"), $this->command->getParameterByKey("borrowerId"));
        if ($ok) {
            $this->command->getResponse()->setParameterByKey('borrowed', true);
        }
        else {
            $this->command->getResponse()->setDataByKey('error', 'error borrowing');
            $this->command->getResponse()->setParameterByKey('error', true);
        }                    
        return $this->command->getResponse();
    }
}