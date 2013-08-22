<?php

namespace LwListtool\Model\Notification\CommandResolver;

class getAllAssignedUserEmails extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \lwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\Notification\\";
    }
    
    public function getInstance($command)
    {
        return new getAllAssignedUserEmails($command);
    }
    
    public function resolve()
    {
        $emails = array();
        $intranets = $this->getQueryHandler()->getIntranetsByListid($this->command->getParameterByKey('listId'));
        $users = $this->getQueryHandler()->getUserByListid($this->command->getParameterByKey('listId'));

        foreach ($intranets as $intranet) {
            $intraUser = $this->getQueryHandler()->getUserByIntranetId($intranet["id"]);
            foreach ($intraUser as $inUser) {
                array_push($users, $inUser);
            }
        }
        
        foreach ($users as $user) {
            if (!empty($user["email"])) {
                $emails[$user["email"]] = true;
            }
        }
        
        $this->command->getResponse()->setDataByKey('emails', $emails);
        return $this->command->getResponse();
    }
}