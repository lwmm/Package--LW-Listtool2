<?php

namespace LwListtool\Model\Entry\CommandResolver;

class getEntryEntityById extends \LWmvc\Model\CommandResolver
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
        return new getEntryEntityById($command);
    }
    
    public function resolve()
    {
        $dto = $this->getQueryHandler()->loadEntryById($this->command->getParameterByKey("id"), $this->command->getParameterByKey("listId"));
        $entity = \LWmvc\Model\EntityFactory::buildEntity($this->ObjectClass, $dto, $this->command->getParameterByKey("id"));
        $this->command->getResponse()->setDataByKey('EntryEntity', $entity);
        return $this->command->getResponse();       
    }
}