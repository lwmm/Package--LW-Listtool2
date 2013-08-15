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
        $listId = $this->command->getParameterByKey('listId');
        $filename = $this->command->getParameterByKey('filename');
        $entryname =$this->command->getParameterByKey('entryname');
        $cmd =$this->command->getParameterByKey('cmd');

        $config = $this->dic->getConfiguration();               
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getConfigurationEntityById', array("id"=> $listId));
        $listConfig = $response->getDataByKey('ConfigurationEntity');
        $lang = $listConfig->getValueByKey('language');
        
        $listname = $this->getQueryHandler()->getListnameByListId($listId, $lang);
        
        if($cmd == "add"){
            $emailTemplateName = "newListoolFileMailtext";
        }else{
            $emailTemplateName = "editListoolFileMailtext";
        }
        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getMailTemplate', array("templateName"=> $emailTemplateName));
        $template = $this->listConfig = $response->getDataByKey('template');
        $template = str_replace("{_listurl_}", \lw_page::getInstance()->getUrl(), $template);
        $template = str_replace("{_filename_}", $filename, $template);
        $template = str_replace("{_listname_}", $listname, $template);
        $template = str_replace("{_entryname_}", $entryname, $template);
        
        $subject = trim(substr($template, 0, strpos($template, PHP_EOL)));
        $content = trim(str_replace($subject, "", $template));        

        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getAllReadersByPageId', array("pageId"=>\lw_page::getInstance()->getId()));
        $users = $response->getDataByKey('UserArray');
        $mailer = new \LwMailer\Controller\LwMailer($config["mailConfig"], $config);
        
        $sendEmails = array();
        foreach($users as $userId){
            $email = $this->getQueryHandler()->getEmailByInUserId($userId);
            
            if(!empty($email)){
                if(!array_key_exists($email, $sendEmails)){
                    $mailInformationArray = array(
                       "toMail"    => $email,
                       "subject"   => $subject,
                       "message"   => $content
                   );

                   $mailer->sendMail($mailInformationArray);
                   $sendEmails[$email] = true;
                }
            }
        }
        return true;
    }
}