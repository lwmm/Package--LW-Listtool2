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
        $cH = new \LwI18n\Model\commandHandler($this->dic->getDbObject());
        foreach($parameter['langParams'] as $lang => $plugindata) {
            foreach($plugindata as $pluginname => $entries) {
                foreach($entries as $key => $text) {
                    $cH->save($pluginname, $lang, $key, $text);
                }
            }
        }
        unset($parameter['langParams']);
        
        return $this->pluginRepository->savePluginData('lw_listtool2', $id, $parameter, $content);
    }
    
    public function deletePluginData($id)
    {
        return $this->pluginRepository->deleteEntryByContainer($id);
    }
}