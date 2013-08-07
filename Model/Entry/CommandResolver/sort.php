<?php

namespace LwListtool\Model\Entry\CommandResolver;

class sort extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \lwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\Entry\\";
        $this->ObjectClass = $this->baseNamespace."Object\\entry";
    }
    
    public function getInstance($command)
    {
        return new sort($command);
    }
    
    public function resolve()
    {
        $array = $this->command->getDataByKey("postArray");
        $neworder = explode(":", $array['neworder']);
        $order = 1;
        foreach($neworder as $id) {
            if (strlen(trim($id))>0) {
                $ok = $this->getCommandHandler()->saveSequence($id, $order);
                $order++;
            }
        }
        $this->command->getResponse()->setParameterByKey('sorted', true);
        return $this->command->getResponse();
    }
}