<?php

namespace LwListtool\Model\Entry\CommandResolver;

class getListEntriesCollection extends \LWmvc\Model\CommandResolver
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
        return new getListEntriesCollection($command);
    }
    
    public function resolve()
    {
        $conf = $this->command->getParameterByKey("configuration");
        $listRights = $this->command->getParameterByKey("listRights");
        $items = $this->getQueryHandler()->loadAllEntriesByListId($this->command->getParameterByKey("listId"), $conf->getValueByKey("sorting"), $listRights->isWriteAllowed());
        $collection = \LWmvc\Model\EntityCollectionFactory::buildCollectionFromQueryResult($this->ObjectClass, $items);
        $this->command->getResponse()->setDataByKey('listEntriesCollection', $collection);
        return $this->command->getResponse();
    }
}