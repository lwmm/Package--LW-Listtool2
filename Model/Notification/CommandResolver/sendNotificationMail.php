<?php

namespace LwListtool\Model\Notification\CommandResolver;

class sendNotificationMail extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \lwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\Notification\\";
    }
    
    public function getInstance($command)
    {
        return new sendNotificationMail($command);
    }
    
    public function resolve()
    {
        $emails = array();
        $listId = $this->command->getParameterByKey('listId');
        $filename = $this->command->getParameterByKey('filename');
        $entryname =$this->command->getParameterByKey('entryname');
        $entryid =$this->command->getParameterByKey('entryid');
        $cmd =$this->command->getParameterByKey('cmd');

        $config = $this->dic->getConfiguration();               
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getConfigurationEntityById', array("id"=> $listId));
        $listname = $response->getDataByKey('ConfigurationEntity')->getValueByKey('name');
        
        
        switch ($cmd) {
            case "addFile":
                $emailTemplateName = "newListoolFileMailtext";
                break;
            case "editFile":
                $emailTemplateName = "editListoolFileMailtext";
                break;
            case "deleteFile":
                $emailTemplateName = "deleteListoolFileMailtext";
                break;
            case "startApproval":
                $emailTemplateName = "startApprovalListoolMailtext";
                break;
            case "stoppApproval":
                $emailTemplateName = "releaseApprovalListoolMailtext";
                break;
            case "remindApproval":
                $emailTemplateName = "remindApprovalListoolMailtext";
                break;
        }
        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getMailTemplate', array("templateName"=> $emailTemplateName));
        $template = $response->getDataByKey('template');
        $template = str_replace("{_listurl_}", \lw_page::getInstance()->getUrl(), $template);
        $template = str_replace("{_filename_}", $filename, $template);
        $template = str_replace("{_listname_}", $listname, $template);
        $template = str_replace("{_entryname_}", $entryname, $template);
        
        $subject = trim(substr($template, 0, strpos($template, PHP_EOL)));
        $content = trim(str_replace($subject, "", $template));    
        
        if($cmd == "addFile" || $cmd == "editFile" || $cmd == "deleteFile"){
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getAllReadersByListId', array("listId"=>$listId));
            $users = $response->getDataByKey('UserArray');
            
            foreach($users as $userId){
                $email = $this->getQueryHandler()->getEmailByInUserId($userId);
                if(!empty($email)){
                    $emails[$email] = true;
                }
            }            
        }else if($cmd == "startApproval" ||  $cmd == "stoppApproval"){
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Notification', 'getAllAssignedUserEmails', array("listId"=> $listId));
            $emails = $response->getDataByKey('emails');
        }else if($cmd=="remindApproval"){
            $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Entry', 'getApprovalStatistics', array("id"=>$entryid, "listId"=>$listId));
            $emails = $response->getDataByKey('emailsOfNotVotedUsers');
        }

        $mailer = new \LwMailer\Controller\LwMailer($config["mailConfig"], $config);
        foreach($emails as $email => $flagg){
            $mailInformationArray = array(
               "toMail"    => $email,
               "subject"   => $subject,
               "message"   => $content
           );

           $mailer->sendMail($mailInformationArray);
        }
        return true;
    }
}