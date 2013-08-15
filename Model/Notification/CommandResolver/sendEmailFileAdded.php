<?php

namespace LwListtool\Model\Notification\CommandResolver;

class sendEmailFileAdded extends \LWmvc\Model\CommandResolver
{
    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \lwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\Notification\\";
    }
    
    public function getInstance($command)
    {
        return new sendEmailFileAdded($command);
    }
    
    public function resolve()
    {
        $listId = $this->command->getParameterByKey('listId');
        $filename = $this->command->getParameterByKey('filename');
        $entryname =$this->command->getParameterByKey('entryname');

        $config = $this->dic->getConfiguration();               
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getConfigurationEntityById', array("id"=> $listId));
        $listConfig = $response->getDataByKey('ConfigurationEntity');
        $lang = $listConfig->getValueByKey('language');
        
        $listname = $this->getQueryHandler()->getListnameByListId($listId, $lang);
        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'Configuration', 'getMailTemplate', array("templateName"=>'newListoolFileMailtext'));
        $template = $this->listConfig = $response->getDataByKey('template');
        $subject = trim(substr($template, 0, strpos($template, PHP_EOL)));
        $content = substr(str_replace($subject, "", $template), 4);
        $content = str_replace("{_listurl_}", \lw_page::getInstance()->getUrl(), $content);
        $content = str_replace("{_filename_}", $filename, $content);
        $content = str_replace("{_listname_}", $listname, $content);
        $content = str_replace("{_entryname_}", $entryname, $content);

        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute('LwListtool', 'ListRights', 'getAllReadersByPageId', array("pageId"=>\lw_page::getInstance()->getId()));
        $users = $response->getDataByKey('UserArray');
        $mailer = new \LwMailer\Controller\LwMailer($config["mailConfig"], $config);
        
        foreach($users as $userId){
            $email = $this->getQueryHandler()->getEmailByInUserId($userId);
            
            if(!empty($email)){
                $mailInformationArray = array(
                   "toMail"    => $email,
                   "subject"   => $subject,
                   "message"   => $content
               );
                
               $mailer->sendMail($mailInformationArray);
            }
        }
        
        return true;
    }
}