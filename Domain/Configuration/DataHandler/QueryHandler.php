<?php

namespace LwListtool\Domain\Configuration\DataHandler;

class QueryHandler 
{
    public function __construct(\lw_db $db)
    {
        $this->db = $db;
        $this->table = 'lw_master';
        $this->type = "lw_organisation";
        $this->dic = new \LwListtool\Services\dic();
        $this->pluginRepository = $this->dic->getPluginRepository();
    }
    
    public function loadObjectById($id)
    {
        $data = $this->pluginRepository->loadPluginData('lw_listtool2', $id);
        return $data['parameter'];
    }
}