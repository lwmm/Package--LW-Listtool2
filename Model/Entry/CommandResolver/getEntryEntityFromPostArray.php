<?php

namespace LwListtool\Model\Entry\CommandResolver;

class getEntryEntityFromPostArray extends \LWmvc\Model\CommandResolver
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
        return new getEntryEntityFromPostArray($command);
    }
    
    public function resolve()
    {
        $dto = new \LWmvc\Model\DTO($this->command->getDataByKey("postArray"));
        $entity = \LWmvc\Model\EntityFactory::buildEntity($this->ObjectClass, $dto);        
        $entity->unsetLoaded();
        $this->command->getResponse()->setDataByKey('EntryEntity', $entity);
        return $this->command->getResponse();       
    }
    
}