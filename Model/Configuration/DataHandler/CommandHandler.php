<?php

namespace LwListtool\Model\Configuration\DataHandler;

class CommandHandler extends \LWmvc\Model\DataCommandHandler
{
    public function __construct($db)
    {
        $this->dic = new \LwListtool\Services\dic();
        $this->setPluginRepository($this->dic->getPluginRepository());
    }

    public function setPluginRepository($pluginRepository)
    {
        $this->pluginRepository = $pluginRepository;
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
        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ApprovalRights', 'saveAssignedApprovalAdmins', array("listId"=>$id, "adminIds" => $parameter['approval_admin']));
        
        unset($parameter['langParams']);
        unset($parameter['approval_admin']);
        
        return $this->pluginRepository->savePluginData('lw_listtool2', $id, $parameter, $content);
    }
    
    public function deletePluginData($id)
    {
        return $this->pluginRepository->deleteEntryByContainer($id);
    }
}