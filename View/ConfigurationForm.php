<?php

namespace LwListtool\View;

class ConfigurationForm extends \LWmvc\View
{
    public function __construct($type)
    {
        parent::__construct('edit');
        $this->dic = new \LwListtool\Services\dic();
        $this->view = new \lw_view(dirname(__FILE__).'/templates/ConfigurationForm.tpl.phtml');
        $this->systemConfiguration = $this->dic->getConfiguration();
        $this->response = new \LwListtool\Domain\Configuration\Service\Response();
        #print_r($this->reg);die();
    }

    public function render()
    {        
        $Lw18nController = new \LwI18n\Controller\I18nController($this->dic->getDbObject(), $this->response);
        $Lw18nController->execute( "lw_listtool2", "de", $this->fillPlaceHolderWithSelectedLanguage("de"));
        $Lw18nController->execute( "lw_listtool2", "en", $this->fillPlaceHolderWithSelectedLanguage("en"));
        
        $this->view->actionUrl = $this->systemConfiguration['url']['client']."admin.php?obj=content&cmd=open&oid=".$this->view->entity->getId()."&pcmd=save";
        $this->view->backUrl = $this->systemConfiguration['url']['client']."admin.php?obj=content";
        $this->view->rightslink = '<a href="#" onClick="openNewWindow(\'' . $this->systemConfiguration['url']['client']."admin.php?obj=content&cmd=open&oid=".$this->view->entity->getId()."&pcmd=assignIntranets&ltid=".$this->view->entity->getId().'\');">Rechtezuweisung</a>';
        $this->view->entity->renderView($this->view);
        
        $this->view->jqUI         = $this->systemConfiguration["url"]["media"] . "jquery/ui/jquery-ui-1.8.7.custom.min.js";
        $this->view->jqUIcss      = $this->systemConfiguration["url"]["media"] . "jquery/ui/css/smoothness/jquery-ui-1.8.7.custom.css";
        $this->view->bootstrapCSS = $this->systemConfiguration["url"]["media"] . "bootstrap/css/bootstrap.min.css";
        $this->view->bootstrapJS  = $this->systemConfiguration["url"]["media"] . "bootstrap/js/bootstrap.min.js";
        $this->view->de           = $this->response->getOutputByKey("i18n_de"); 
        $this->view->en           = $this->response->getOutputByKey("i18n_en"); 
        
        return $this->view->render();    
    }
    
    public function setAssignedUser($array)
    {
        $this->view->users = $array;
    }
    
    public function setAssignedIntranets($array)
    {
        $this->view->intranets = $array;
    }
    
    /**
     * All placholders will be set with the output text. 
     * These arrays are used as base information.
     * 
     * @param string $lang
     * @return array
     */
    public function fillPlaceHolderWithSelectedLanguage($lang)
    {
        $languageDE = array( "de" => array( "lw_listtool2" => array(
            "lang_newfile"      => "neue Datei anlegen",
            "lang_newlink"      => "neuen Link anlegen",
            "lang_sortlist"     => "Liste sortieren",
            "lang_name"         => "Name",
            "lang_date"         => "Erstelldatum",
            "lang_lastdate"     => "letzte &Auml;nderung",
            "lang_freedate"     => "Bezugsdatum",
            "lang_filedate"     => "letzte Datei&auml;nderung",
            "lang_published"    => "ver&ouml;ffentlicht",
            "lang_description"  => "Beschreibung",
            "lang_user"         => "Benutzer",
            "lang_firstuser"    => "Erster Benutzer",
            "lang_lastuser"     => "Letzter Benutzer",
            "lang_link"         => "Verlinkung",
            "lang_download"     => "Herunterladen",
            "lang_edit"         => "bearbeiten",
            "lang_delete"       => "l&ouml;schen",
            "lang_release"      => "zur&uuml;ckgeben",
            "lang_release_title"=> "Den Eintrag zur&uuml;ckgeben, damit andere Personen diesen bearbeiten k&ouml;nnen.",
            "lang_borrow"       => "zur Bearbeitung ausleihen",
            "lang_borrow_title" => "Ausgeliehene Eintr&auml;ge k&ouml;nnen nur von dem Ausleiher und einem Administrator bearbeitet werden. Andere Nutzer haben keinen Schreibzugriff. Der Eintrag steht dar&uuml;ber hinaus aber zum Donwload/Verlinkung weiterhin zur Verf&uuml;gung.",
            "lang_reallydelete" => "wirklich l&ouml;schen?",
            "lang_borrowedby"   => "wird bearbeitet von",
            "lang_noentries"    => "Es liegen keine Eintr&auml;ge vor."
        )));
        
        $languageEN = array( "en" => array( "lw_listtool2" => array(
            "lang_newfile"      => "add new file",
            "lang_newlink"      => "add new link",
            "lang_sortlist"     => "sort list",
            "lang_name"         => "Name",
            "lang_date"         => "Creation date",
            "lang_lastdate"     => "Last change",
            "lang_freedate"     => "refering date",
            "lang_filedate"     => "Last file change date",
            "lang_published"    => "Published",
            "lang_description"  => "Description",
            "lang_user"         => "User",
            "lang_firstuser"    => "First user",
            "lang_lastuser"     => "Last user",
            "lang_link"         => "Link",
            "lang_download"     => "Download",
            "lang_edit"         => "edit",
            "lang_delete"       => "delete",
            "lang_release"      => "check in",
            "lang_release_title"=> "check the entry in to allow other persons to edit it",
            "lang_borrow"       => "check out for editing",
            "lang_borrow_title" => "checked out entries can only be edited by you or an administrator. Other users cannot edit this entry. It is still possible to use the link or download the file.",
            "lang_reallydelete" => "really delete?",
            "lang_borrowedby"   => "checked out by",
            "lang_noentries"    => "no entries available"
        )));
        
        if($lang == "de") {
            return $languageDE;
        } else {
            return $languageEN;
        }
    }
}