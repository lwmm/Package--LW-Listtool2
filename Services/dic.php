<?php

namespace lwListtool\Services;

class dic
{
    public function __construct()
    {
        
    }
    
    public function getConfigurationRepository()
    {
        if (!$this->ConfigurationRepository) {
            $this->ConfigurationRepository = new \lwListtool\Domain\Configuration\Model\Repository();
        }
        return $this->ConfigurationRepository;
    }
  
    public function getEntryRepository()
    {
        if (!$this->EntryRepository) {
            $this->EntryRepository = new \lwListtool\Domain\Entry\Model\Repository();
        }
        return $this->EntryRepository;
    }
  
    public function getConfigurationByListId($id)
    {
        if (!$this->configuration[$id]) {
            $event = \LWddd\DomainEvent::getInstance('Configuration', 'getConfigurationEntityById');
            $event->setParameterByKey("id", $id);
            $response = \lwListtool\Domain\DomainEventDispatch::getInstance()->execute($event);
            $this->configuration[$id] = $response->getDataByKey('ConfigurationEntity');
        }
        return $this->configuration[$id];
    }
    
    public function getPluginRepository()
    {
        return \lw_registry::getInstance()->getEntry("repository")->plugins();
    }
    
    public function getDbObject()
    {
        return \lw_registry::getInstance()->getEntry("db");
    }
    
    public function getConfiguration()
    {
        return \lw_registry::getInstance()->getEntry("config");
    }
    
    public function getLwResponse()
    {
        return \lw_registry::getInstance()->getEntry("response");
    }
    
    public function getLwRequest()
    {
        return \lw_registry::getInstance()->getEntry("request");
    }

    public function getLwAuth()
    {
        return \lw_registry::getInstance()->getEntry("auth");
    }

    public function getLwInAuth()
    {
        return \lw_in_auth::getInstance();
    }
}