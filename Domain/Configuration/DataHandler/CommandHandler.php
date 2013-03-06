<?php

namespace LwListtool\Domain\Configuration\DataHandler;

class CommandHandler
{
    private $db;
    private $pluginRepository;
    
    public function __construct($db)
    {
        $this->dic = new \LwListtool\Services\dic();
        $this->pluginRepository = $this->dic->getPluginRepository();
    }

    public function savePluginData($id, $parameter, $content)
    {
        return $this->pluginRepository->savePluginData('lw_listtool2', $id, $parameter, $content);
    }
    
    public function deletePluginData($id)
    {
        return $this->pluginRepository->deleteEntryByContainer($id);
    }
}