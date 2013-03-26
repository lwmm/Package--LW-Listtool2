<?php

/* * ************************************************************************
 *  Copyright notice
 *
 *  Copyright 2013 Logic Works GmbH
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *  
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *  
 * ************************************************************************* */

namespace LwListtool\View;

class ListtoolList extends \LWmvc\View {

    public function __construct() {
        parent::__construct('edit');
        $this->dic = new \LwListtool\Services\dic();
        $this->systemConfiguration = $this->dic->getConfiguration();
        $this->auth = $this->dic->getLwAuth();
        $this->inAuth = $this->dic->getLwInAuth();
    }

    public function setListRights($rights) {
        $this->listRights = $rights;
    }

    public function setConfiguration($configuration) {
        $this->configuration = $configuration;
    }

    public function setLanguagePhrases($langPhrases) {
        $this->langPhrases = $langPhrases;
    }

    public function setListId($id) {
        $this->listId = $id;
    }

    public function init() {
        if (filter_var($this->configuration->getValueByKey('template'), FILTER_VALIDATE_INT)) {
            die("page templates are not implemented yet"); //$template = $base . $this->repository->loadTemplateById($this->configuration->getValueByKey('template'));
        } else {
            $template = \lw_io::loadFile(dirname(__FILE__) . '/listTemplates/' . $this->configuration->getValueByKey('template'));
        }
        $this->view = new \lw_te($template);
    }

    public function render() {
        #return $this->render_old();
        $listtoolbase = new \LwListtool\View\ListtoolBase();
        return $listtoolbase->render() . "\n" . $this->buildList();
        #return $this->buildList();
    }

    protected function buildList() {
        $out = '<div class="lwlt_list" id="list_' . $this->listId . '">' . PHP_EOL;

        if ($this->listRights->isReadAllowed()) {

            $out.= $this->buildHeader();
            $bool = false;
            foreach ($this->view->aggregate as $entry) {
                $bool = true;
            }
            if ($bool) {
                $out.= $this->buildEntryTable();
            }
        }

        $out.= '</div>' . PHP_EOL;

        return $out;
    }

    protected function buildHeader() {
        $out = '<h2>' . $this->configuration->getValueByKey('name') . '</h2>' . PHP_EOL;

        if ($this->listRights->isWriteAllowed()) {

            $out.= '<div class="lt_adminfunctions">' . PHP_EOL;
            $out.= '<a href="#" id="lt_new_file">' . $this->langPhrases["lang_newfile"] . '</a>' . PHP_EOL;
            $out.= '| <a href="#" id="lt_new_link">' . $this->langPhrases["lang_newlink"] . '</a>' . PHP_EOL;

            if ($this->configuration->getValueByKey('sorting') == "opt1number" && $this->listRights->isWriteAllowed()) {
                $out.= '| <a href="#" id="lt_sorting">' . $this->langPhrases["lang_sortlist"] . '</a>' . PHP_EOL;
            }

            $out.= '</div>' . PHP_EOL;
        }
        return $out;
    }

    protected function buildEntryTable() {
        $out = '<table width="100%" border="1">' . PHP_EOL;
        $out.= '<thead>' . PHP_EOL;
        $out.= '<tr>' . PHP_EOL;
        if ($this->configuration->getValueByKey('showId') == 1) {
            $out.= '<th>ID</th>' . PHP_EOL;
        }
        if ($this->configuration->getValueByKey('showName') == 1) {
            $out.= '<th>' . $this->langPhrases["lang_name"] . '</th>' . PHP_EOL;
        }
        if ($this->configuration->getValueByKey('showDescription') == 1) {
            $out.= '<th>' . $this->langPhrases["lang_description"] . '</th>' . PHP_EOL;
        }
        if ($this->configuration->getValueByKey('showDate') == 1) {
            $out.= '<th>' . $this->langPhrases["lang_date"] . '</th>' . PHP_EOL;
        }
        if ($this->configuration->getValueByKey('showLastDate') == 1) {
            $out.= '<th>' . $this->langPhrases["lang_lastdate"] . '</th>' . PHP_EOL;
        }
        if ($this->configuration->getValueByKey('showUser') == 1) {
            $out.= '<th>' . $this->langPhrases["lang_user"] . '</th>' . PHP_EOL;
        }
        if ($this->listRights->isWriteAllowed()) {
            if ($this->configuration->getValueByKey('publishedoption') == 0) {
                $out.= '<th>' . $this->langPhrases["lang_published"] . '</th>' . PHP_EOL;
            }
        }
        if ($this->configuration->getValueByKey('linktype') == 1) {
            $out.= '<th>&nbsp;</th>';
        }
        $out.= '<th>&nbsp;</th>' . PHP_EOL;
        $out.= '</tr>' . PHP_EOL;
        $out.= '</thead>' . PHP_EOL;
        $out.= '<tbody>' . PHP_EOL;
        foreach ($this->view->aggregate as $entry) {
            $out.= $this->buildEntries($entry);
        }
        $out.= '</tbody>' . PHP_EOL;
        $out.= '</table>' . PHP_EOL;
        return $out;
    }

    protected function buildEntries($entry) {
        $out = '<tr';
        if ($this->listRights->isWriteAllowed()) {
            if ($entry->getValueByKey('published') == 0) {
                $out.= ' style="background-color:#ffcccc;"';
            }
        }
        $out.= '>' . PHP_EOL;

        $columns = array("Id", "Name", "Description", "Date", "LastDate", "Published", "Username", "LinkType", "AdminFunctions");
        foreach ($columns as $c) {
            $method = "buildColumn".$c;
            $out.= $this->$method($entry);
        }
        
        $out.= '</tr>' . PHP_EOL;
        return $out;
    }
    
    protected function buildColumnId($entry)
    {
        if ($this->configuration->getValueByKey('showId') == 1) {
            $out.= '<td align="left">' . $entry->getValueByKey("id") . '</td>' . PHP_EOL;
        }
        return $out;
    }
    
    protected function buildColumnName($entry)
    {
        if ($this->configuration->getValueByKey('showName') == 1) {
            $out.= '<td align="left">';
            if ($this->configuration->getValueByKey('linktype') != 1) {
                if ($entry->isLink()) {
                    $out.= '<a href="' . $entry->getValueByKey("opt3text") . '" class="lwlt_link">' . $entry->getValueByKey("name") . '</a>';
                }
                if ($entry->isFile() && $entry->hasFile()) {
                    $out.= '<a href="' . \lw_page::getInstance()->getUrl(array("cmd" => "downloadEntry", "id" => $entry->getValueByKey("id"))) . '" class="lwlt_download">' . $entry->getValueByKey("name") . '</a>';
                }
            } else {
                $out.= ' . $entry->getValueByKey("name") . ';
            }
            $out.= '</td>' . PHP_EOL;
        }
        return $out;
    }
    
    protected function buildColumnDescription($entry)
    {
        if ($this->configuration->getValueByKey('showDescription') == 1) {
            $out.= '<td>' . html_entity_decode($entry->getValueByKey('description')) . '</td>' . PHP_EOL;
        }
        return $out;
    }
    
    protected function buildColumnDate($entry)
    {
        if ($this->configuration->getValueByKey('showDate') == 1) {
            $out.= '<td align="left">' . $entry->getFirstDate() . ' ';
            if ($this->configuration->getValueByKey('showTime') == 1) {
                $out.= $entry->getFirstTime();
            }
            $out.= '</td>' . PHP_EOL;
        }
        return $out;
    }
    
    protected function buildColumnLastDate($entry)
    {
        if ($this->configuration->getValueByKey('showLastDate') == 1) {
            $out.= '<td align="left">' . $entry->getLastDate();

            if ($this->configuration->getValueByKey('showTime') == 1) {
                $out.= $entry->getLastTime();
            }
            $out.= '</td>';
        }
        return $out;
    }
    
    protected function buildColumnPublished($entry)
    {
        if ($this->listRights->isWriteAllowed()) {
            if ($this->configuration->getValueByKey('publishedoption') == 0) {
                $out.= '<td align="left">' . $entry->getValueByKey("published") . '</td>' . PHP_EOL;
            }
        }
        return $out;
    }
    
    protected function buildColumnUsername($entry)
    {
        if ($this->configuration->getValueByKey('showUser') == 1) {
            $out.= '<td>username</td>' . PHP_EOL;
        }
        return $out;
    }
    
    protected function buildColumnLinkType($entry)
    {
        if ($this->configuration->getValueByKey('linktype') == 1) {
            $out.= '<td align="left">' . PHP_EOL;
            if ($entry->isLink()) {
                $out.= '<a href="' . $entry->getValueByKey("opt3text") . '" class="lwlt_link">' . $this->langPhrases["lang_link"] . '</a>';
            }
            if ($entry->isFile() && $entry->hasFile()) {
                $out.= '<a href="' . \lw_page::getInstance()->getUrl(array("cmd" => "downloadEntry", "id" => $entry->getValueByKey("id"))) . '" class="lwlt_download">' . $this->langPhrases["lang_download"] . '</a>';
            }
            $out.= '</td>' . PHP_EOL;
        }
        return $out;
    }
    
    
    protected function buildColumnAdminFunctions($entry)
    {
        $out.= '<td>' . PHP_EOL;
        if ($this->listRights->isWriteAllowed()) {
            if ($this->configuration->getValueByKey('borrow') == 1) {
                if ($entry->isBorrowed()) {
                    if ($this->auth->isLoggedIn() || $entry->isBorrower($this->inAuth->getUserdata("id"))) {
                        $out.= '<span class="lt_adminfunctions">
                                        <a href="#" class="lt_edit_entry" id="lt_entry_' . $entry->getValueByKey("id") . '">' . $this->langPhrases["lang_edit"] . '</a>
                                        | <a href="' . \lw_page::getInstance()->getUrl(array("cmd" => "deleteEntry", "id" => $entry->getValueByKey("id"))) . '" onclick="return confirm(\'' . $this->langPhrases["lang_reallydelete"] . '\');">' . $this->langPhrases["lang_delete"] . '</a>
                                        | <a href="' . \lw_page::getInstance()->getUrl(array("cmd" => "releaseEntry", "id" => $entry->getValueByKey("id"))) . '"><span title="' . $this->langPhrases["lang_release_title"] . '">' . $this->langPhrases["lang_release"] . '</span></a>
                                    </span>';     
                    } else {
                        $out.= '<span class="lt_adminfunctions">' . $this->langPhrases["lang_borrowedby"] . ' ' . $entry->getBorrowerName() . ' id: ' . $entry->getBorrowerId() . '</span>';
                    }
                } else {
                    $out.= '<span class="lt_adminfunctions"><a href="' . \lw_page::getInstance()->getUrl(array("cmd" => "borrowEntry", "id" => $entry->getValueByKey("id"))) . '"><span title="' . $this->langPhrases["lang_borrow_title"] . '">' . $this->langPhrases["lang_borrow"] . '</span></a></span>';
                }
            }
        }
        $out.= '</td>' . PHP_EOL;
        return $out;
    }

//    public function render_old() {
//        $this->view->reg("listtitle", $this->configuration->getValueByKey('name'));
//        if ($this->listRights->isReadAllowed()) {
//            $this->view->setIfVar('ltRead');
//        }
//        if ($this->listRights->isWriteAllowed()) {
//            $this->view->setIfVar('ltWrite');
//        }
//
//        $blocktemplate = $this->view->getBlock("entry");
//        foreach ($this->view->aggregate as $entry) {
//            $this->view->setIfVar('entries');
//            $btpl = new \lw_te($blocktemplate);
//            if ($entry->isLink()) {
//                $btpl->setIfVar('link');
//                $btpl->reg("opt3text", $entry->getValueByKey("opt3text"));
//            }
//            if ($entry->isFile() && $entry->hasFile()) {
//                $btpl->setIfVar('file');
//                $btpl->reg("downloadurl", \lw_page::getInstance()->getUrl(array("cmd" => "downloadEntry", "id" => $entry->getValueByKey("id"))));
//            }
//            $btpl->reg("deleteurl", \lw_page::getInstance()->getUrl(array("cmd" => "deleteEntry", "id" => $entry->getValueByKey("id"))));
//            $btpl->reg("id", $entry->getValueByKey("id"));
//
//            if ($this->configuration->getValueByKey('showName') == 1) {
//                $btpl->setIfVar("showname");
//                $btpl->reg("name", $entry->getValueByKey("name"));
//            }
//
//            if ($this->configuration->getValueByKey('showDate') == 1) {
//                $btpl->setIfVar("showdate");
//                $btpl->reg("lw_first_date", $entry->getFirstDate());
//                if ($this->configuration->getValueByKey('showTime') == 1) {
//                    $btpl->setIfVar("showtime");
//                    $btpl->reg("lw_first_time", $entry->getFirstTime());
//                }
//            }
//
//            if ($this->configuration->getValueByKey('showLastDate') == 1) {
//                $btpl->setIfVar("showlastdate");
//                $btpl->reg("lw_last_date", $entry->getLastDate());
//                if ($this->configuration->getValueByKey('showTime') == 1) {
//                    $btpl->setIfVar("showtime");
//                    $btpl->reg("lw_last_time", $entry->getLastTime());
//                }
//            }
//
//            if ($this->configuration->getValueByKey('showDescription') == 1) {
//                $btpl->setIfVar("showdescription");
//                $btpl->reg("description", html_entity_decode($entry->getValueByKey('description')));
//            }
//
//            $btpl->reg("published", $entry->getValueByKey("published"));
//
//            if ($this->listRights->isReadAllowed()) {
//                $btpl->setIfVar('ltRead');
//            }
//
//            if ($this->listRights->isWriteAllowed()) {
//                $btpl->setIfVar('ltWrite');
//            }
//
//            if ($this->configuration->getValueByKey('borrow') == 1) {
//                if ($entry->isBorrowed()) {
//                    if ($this->auth->isLoggedIn() || $entry->isBorrower($this->inAuth->getUserdata("id"))) {
//                        $btpl->setIfVar('showEditOptions');
//                        $btpl->setIfVar('showReleaseLink');
//                        $btpl->reg("releaseurl", \lw_page::getInstance()->getUrl(array("cmd" => "releaseEntry", "id" => $entry->getValueByKey("id"))));
//                    } else {
//                        $btpl->setIfVar('borrowed');
//                        $btpl->reg('borrower', $entry->getBorrowerName() . ' <!-- borrower_id: ' . $entry->getBorrowerId() . ' --> ');
//                    }
//                } else {
//                    $btpl->setIfVar('borrow');
//                    $btpl->reg("borrowurl", \lw_page::getInstance()->getUrl(array("cmd" => "borrowEntry", "id" => $entry->getValueByKey("id"))));
//                }
//            } else {
//                $btpl->setIfVar('showEditOptions');
//            }
//
//            if ($this->configuration->getValueByKey('showId') == 1) {
//                $btpl->setIfVar("showid");
//            }
//
//            if ($this->configuration->getValueByKey('showUser') == 1) {
//                $btpl->setIfVar("showuser");
//                $btpl->reg("last_username", "username");
//            }
//
//            if ($this->configuration->getValueByKey('linktype') == 1) {
//                $btpl->setIfVar("columnlink");
//            } else {
//                $btpl->setIfVar("namelink");
//            }
//
//            if ($this->configuration->getValueByKey('publishedoption') == 0) {
//                $btpl->setIfVar("columnpublished");
//            } else {
//                if ($entry->getValueByKey('published') == 0) {
//                    $btpl->setIfVar("rowcolorpublished");
//                }
//            }
//
//            $this->setTexts($btpl);
//
//            $bout.= $btpl->parse();
//        }
//
//        if ($this->configuration->getValueByKey('showName') == 1) {
//            $this->view->setIfVar("showname");
//        }
//
//        if ($this->configuration->getValueByKey('showDate') == 1) {
//            $this->view->setIfVar("showdate");
//        }
//
//        if ($this->configuration->getValueByKey('showLastDate') == 1) {
//            $this->view->setIfVar("showlastdate");
//        }
//
//        if ($this->configuration->getValueByKey('showDescription') == 1) {
//            $this->view->setIfVar("showdescription");
//        }
//
//        if ($this->configuration->getValueByKey('showId') == 1) {
//            $this->view->setIfVar("showid");
//        }
//
//        if ($this->configuration->getValueByKey('showUser') == 1) {
//            $this->view->setIfVar("showuser");
//        }
//
//        if ($this->configuration->getValueByKey('publishedoption') == 0) {
//            $this->view->setIfVar("columnpublished");
//        }
//
//        if ($this->configuration->getValueByKey('sorting') == "opt1number" && $this->listRights->isWriteAllowed()) {
//            $this->view->setIfVar("manualsorting");
//        }
//
//        if ($this->configuration->getValueByKey('showcss') == 1) {
//            $this->view->setIfVar("showcss");
//        }
//
//        if ($this->configuration->getValueByKey('linktype') == 1) {
//            $this->view->setIfVar("columnlink");
//        } else {
//            $this->view->setIfVar("namelink");
//        }
//
//        $this->setTexts($this->view);
//
//        $this->view->reg("listId", $this->listId);
//        $this->view->putBlock("entry", $bout);
//        $listtoolbase = new \LwListtool\View\ListtoolBase();
//        return $listtoolbase->render() . "\n" . $this->view->parse();
//    }
//
//    protected function setTexts($tpl) {
//        $tpl->reg("lang_newfile", $this->langPhrases["lang_newfile"]);
//        $tpl->reg("lang_newlink", $this->langPhrases["lang_newlink"]);
//        $tpl->reg("lang_sortlist", $this->langPhrases["lang_sortlist"]);
//        if ($this->configuration->getValueByKey('title_name')) {
//            $tpl->reg("lang_name", $this->configuration->getValueByKey('title_name'));
//        } else {
//            $tpl->reg("lang_name", $this->langPhrases["lang_name"]);
//        }
//        $tpl->reg("lang_date", $this->langPhrases["lang_date"]);
//        $tpl->reg("lang_lastdate", $this->langPhrases["lang_lastdate"]);
//        $tpl->reg("lang_published", $this->langPhrases["lang_published"]);
//        if ($this->configuration->getValueByKey('title_description')) {
//            $tpl->reg("lang_description", $this->configuration->getValueByKey('title_description'));
//        } else {
//            $tpl->reg("lang_description", $this->langPhrases["lang_description"]);
//        }
//        $tpl->reg("lang_user", $this->langPhrases["lang_user"]);
//        if ($this->configuration->getValueByKey('title_link')) {
//            $tpl->reg("lang_link", $this->configuration->getValueByKey('title_link'));
//        } else {
//            $tpl->reg("lang_link", $this->langPhrases["lang_link"]);
//        }
//        if ($this->configuration->getValueByKey('title_download')) {
//            $tpl->reg("lang_download", $this->configuration->getValueByKey('title_download'));
//        } else {
//            $tpl->reg("lang_download", $this->langPhrases["lang_download"]);
//        }
//        $tpl->reg("lang_edit", $this->langPhrases["lang_edit"]);
//        $tpl->reg("lang_delete", $this->langPhrases["lang_delete"]);
//        $tpl->reg("lang_release", $this->langPhrases["lang_release"]);
//        $tpl->reg("lang_release_title", $this->langPhrases["lang_release_title"]);
//        $tpl->reg("lang_borrow", $this->langPhrases["lang_borrow"]);
//        $tpl->reg("lang_borrow_title", $this->langPhrases["lang_borrow_title"]);
//        $tpl->reg("lang_reallydelete", $this->langPhrases["lang_reallydelete"]);
//        $tpl->reg("lang_borrowedby", $this->langPhrases["lang_borrowedby"]);
//        $tpl->reg("lang_noentries", $this->langPhrases["lang_noentries"]);
//    }

}