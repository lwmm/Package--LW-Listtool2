<?php

namespace LwListtool\View;

class ConfigurationForm extends \LWmvc\View\View
{
    public function __construct($type)
    {
        parent::__construct('edit');
        $this->dic = new \LwListtool\Services\dic();
        $this->view = new \lw_view(dirname(__FILE__).'/templates/ConfigurationForm.tpl.phtml');
        $this->systemConfiguration = $this->dic->getConfiguration();
        $this->response = new \LwListtool\Model\Configuration\Service\Response();
        $this->featureCollection = \lw_registry::getInstance()->getEntry("FeatureCollection");
    }

    public function render()
    {        
        if($this->featureCollection->getFeature("LwListtool_ApprovalSystem")->isActive()){
            $this->view->useApprovalSystemFeatureList = true;
        }
        if($this->featureCollection->getFeature("LwListtool_EmailNotification")->isActive()){
            $this->view->useEmailNotificationSystemFeatureList = true;
        }
        $LwI18nController = new \LwI18n\Controller\I18nController($this->dic->getDbObject(), $this->response);
        $LwI18nController->execute( "lw_listtool2", "de", $this->fillPlaceHolderWithSelectedLanguage("de"));
        $LwI18nController->execute( "lw_listtool2", "en", $this->fillPlaceHolderWithSelectedLanguage("en"));
        
        $this->view->actionUrl = $this->systemConfiguration['url']['client']."admin.php?obj=content&cmd=open&oid=".$this->view->entity->getId()."&pcmd=save";
        $this->view->backUrl = $this->systemConfiguration['url']['client']."admin.php?obj=content";
        $this->view->rightslink = $this->systemConfiguration['url']['client']."admin.php?obj=content&cmd=open&oid=".$this->view->entity->getId()."&pcmd=assignIntranets&ltid=".$this->view->entity->getId();
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
     * List of single assigned users and users of assigned intranets.
     * 
     * @param array $array
     */
    public function setAssignedUserList($array)
    {
        $this->view->userList = $array;
    }
    
    public function setAssignedApprovalAdminIds($array)
    {
        $this->view->approvalAdminIds = $array;
    }
    
    public function setListId($listId)
    {
        $this->listId = $listId;
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
            "lang_listtitle"                => "Listenname",
            "lang_newfile"                  => "neue Datei anlegen",
            "lang_newlink"                  => "neuen Link anlegen",
            "lang_sortlist"                 => "Liste sortieren",
            "lang_name"                     => "Name",
            "lang_date"                     => "Erstelldatum",
            "lang_lastdate"                 => "letzte &Auml;nderung",
            "lang_freedate"                 => "Bezugsdatum",
            "lang_filedate"                 => "letzte Datei&auml;nderung",
            "lang_published"                => "ver&ouml;ffentlicht",
            "lang_description"              => "Beschreibung",
            "lang_keywords"                 => "Stichworte",
            "lang_additionalinfo"           => "Zusatzinfo",
            "lang_firstuser"                => "Erster Benutzer",
            "lang_lastuser"                 => "Letzter Benutzer",
            "lang_link"                     => "Verlinkung",
            "lang_download"                 => "Herunterladen",
            "lang_edit"                     => "bearbeiten",
            "lang_delete"                   => "l&ouml;schen",
            "lang_release"                  => "zur&uuml;ckgeben",
            "lang_release_title"            => "Den Eintrag zur&uuml;ckgeben, damit andere Personen diesen bearbeiten k&ouml;nnen.",
            "lang_borrow"                   => "zur Bearbeitung ausleihen",
            "lang_borrow_title"             => "Ausgeliehene Eintr&auml;ge k&ouml;nnen nur von dem Ausleiher und einem Administrator bearbeitet werden. Andere Nutzer haben keinen Schreibzugriff. Der Eintrag steht dar&uuml;ber hinaus aber zum Donwload/Verlinkung weiterhin zur Verf&uuml;gung.",
            "lang_borrow_msg"               => "Sie haben Dokumente zur Bearbeitung ausgeliehen.Bitte geben Sie diese Dokumente nach Aktualisierung zur&uuml;ck. Um dies zu tun, w&auml;hlen Sie bitte den Link 'zur&uuml;ckgeben' in der entsprechenden Tabellenzeile.",
            "lang_reallydelete"             => "wirklich l&ouml;schen?",
            "lang_borrowedby"               => "wird bearbeitet von",
            "lang_noentries"                => "Es liegen keine Eintr&auml;ge vor.",
            "lang_thumbnail"                => "Bildvorschau"
        )));
        
        $languageDEApproval = array(
            "lang_approval_title"           => "Dateien im Genehmigungsverfahren k&ouml;nnen nicht bearbeitet werden. Alle Bearbeiter m&uuml;ssen den Status der Datei best&auml;tigen.",
            "lang_approval"                 => "Genehmigung starten",
            "lang_approval_started_by"      => "Genehmigungsverfahren eingeleitet von ",
            "lang_approval_approved_file"   => "genehmigte Datei",
            "lang_approval_status"          => "Genehmigungsstatus",
            "lang_approval_status_title"    => "Es werden Ihnen detailierte Informationen zum Genehmigungsverfahren angezeigt.",
            "lang_approval_ended_status"    => "Genehmigungsstatus - Beendet",
            "lang_approval_vote"            => "abstimmen",
            "lang_approval_vote_title"      => "Geben Sie ihre Stimme ab, ob diese Datei bereit f&uuml;r die Abgabe ist.",
            "lang_approval_voted"           => "abgestimmt",
            "lang_approval_missed_voting"   => "Abstimmung verpasst",
            "lang_approval_send_reminder"   => "Erinnerungsmail verschicken",
            "lang_approval_send_reminder_title" => "Es wird eine Erinnerungsmail an die Bearbeiter geschickt, die noch nicht abgestimmt haben.",
            "lang_approval_approve"         => "Datei genehmigen",
            "lang_approval_approve_title"   => "Genehmigte Dateien k&ouml;nnen nicht mehr bearbeitet werden.",
            "lang_approval_release_file"    => "Datei freigeben",
            "lang_approval_release_file_title"  => "Das Genehmigungsverfahren wird beendet und die Datei zur erneuten Bearbeitung freigegeben."
        );
        if($this->featureCollection->getFeature("LwListtool_ApprovalSystem")->isActive()){
            foreach($languageDEApproval as $key => $value){
                $languageDE["de"]["lw_listtool2"][$key] = $value;
            }
        }

        $languageEN = array( "en" => array( "lw_listtool2" => array(
            "lang_listtitle"                => "Listname",
            "lang_newfile"                  => "add new file",
            "lang_newlink"                  => "add new link",
            "lang_sortlist"                 => "sort list",
            "lang_name"                     => "Name",
            "lang_date"                     => "Creation date",
            "lang_lastdate"                 => "Last change",
            "lang_freedate"                 => "refering date",
            "lang_filedate"                 => "Last file change date",
            "lang_published"                => "Published",
            "lang_description"              => "Description",
            "lang_keywords"                 => "Keywords",
            "lang_additionalinfo"           => "Additional info",
            "lang_firstuser"                => "First user",
            "lang_lastuser"                 => "Last user",
            "lang_link"                     => "Link",
            "lang_download"                 => "Download",
            "lang_edit"                     => "edit",
            "lang_delete"                   => "delete",
            "lang_release"                  => "check in",
            "lang_release_title"            => "check the entry in to allow other persons to edit it",
            "lang_borrow"                   => "check out for editing",
            "lang_borrow_title"             => "checked out entries can only be edited by you or an administrator. Other users cannot edit this entry. It is still possible to use the link or download the file.",
            "lang_borrow_msg"               => "You checked out a document.Please check it in after editing.",
            "lang_reallydelete"             => "really delete?",
            "lang_borrowedby"               => "checked out by",
            "lang_noentries"                => "no entries available",
            "lang_thumbnail"                => "Picture preview"
        )));
        
        $languageENApproval = array(
            "lang_approval_title"           => "Files in approval state can not be edited. All editors have to confirm the version of the file.",
            "lang_approval"                 => "start approval",
            "lang_approval_started_by"      => "Approval started by ",
            "lang_approval_approved_file"   => "Approved file",
            "lang_approval_status"          => "Approval state",
            "lang_approval_status_title"    => "You can see detailed informations about this approval.",
            "lang_approval_ended_status"    => "Approval state - Finished",
            "lang_approval_vote"            => "vote",
            "lang_approval_vote_title"      => "Vote if this file is ready for delivery.",
            "lang_approval_voted"           => "voted",
            "lang_approval_missed_voting"   => "voting missed",
            "lang_approval_send_reminder"   => "send email reminder",
            "lang_approval_send_reminder_title" => "A reminder email will be sent to the editors, who have not voted, yet.",
            "lang_approval_approve"         => "approve file",
            "lang_approval_approve_title"   => "Approved files can't be edited.",
            "lang_approval_release_file"    => "release file",
            "lang_approval_release_file_title"  => "The approval will be stopped and the file can be edited again."
        );
        if($this->featureCollection->getFeature("LwListtool_ApprovalSystem")->isActive()){
            foreach($languageENApproval as $key => $value){
                $languageEN["en"]["lw_listtool2"][$key] = $value;
            }
        }
        
        if($lang == "de") {
            return $languageDE;
        } else {
            return $languageEN;
        }
    }
}