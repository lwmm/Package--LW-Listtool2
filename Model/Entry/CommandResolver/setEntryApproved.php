<?php

namespace LwListtool\Model\Entry\CommandResolver;

class setEntryApproved extends \LWmvc\Model\CommandResolver
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
        return new setEntryApproved($command);
    }
    
    public function resolve()
    {
        # /F021/ Datei als genehmigt markieren anhand der Eintrags-ID.
        $ok = $this->getCommandHandler()->setEntryApproved($this->command->getParameterByKey("id"));
        if ($ok) {
            $this->command->getResponse()->setParameterByKey('entryApproved', true);
        }
        else {
            $this->command->getResponse()->setDataByKey('error', 'error entryApproved');
            $this->command->getResponse()->setParameterByKey('error', true);
        }     

        return $this->command->getResponse();
    }
}