<?php

namespace LwListtool\Model\Configuration\DataHandler;

class QueryHandler extends \LWmvc\Model\DataQueryHandler
{
    public function __construct(\lw_db $db)
    {
        //$this->db = $db;
        //$this->table = 'lw_master';
        //$this->type = "lw_organisation";
        $this->dic = new \LwListtool\Services\dic();
        $this->setPluginRepository($this->dic->getPluginRepository());
    }
    
    public function setPluginRepository($pluginRepository)
    {
        $this->pluginRepository = $pluginRepository;
    }
    
    public function loadObjectById($id)
    {
        $data = $this->pluginRepository->loadPluginData('lw_listtool2', $id);
        return $data['parameter'];
    }
}