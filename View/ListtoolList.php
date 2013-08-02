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

class ListtoolList extends \LWmvc\View
{

    public function __construct()
    {
        parent::__construct('edit');
        $this->dic = new \LwListtool\Services\dic();
        $this->systemConfiguration = $this->dic->getConfiguration();
        $this->auth = $this->dic->getLwAuth();
        $this->inAuth = $this->dic->getLwInAuth();
    }

    public function setListRights($rights)
    {
        $this->listRights = $rights;
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    public function setLanguagePhrases($langPhrases)
    {
        $this->langPhrases = $langPhrases;
    }

    public function setListId($id)
    {
        $this->listId = $id;
    }

    public function init()
    {
        $this->view = new \lw_view(dirname(__FILE__) . '/listTemplates/list.tpl.phtml');
    }

    public function render()
    {
        $listHasBorrowedItems = false;

        $this->view->lang = $this->langPhrases;
        $this->view->listId = $this->listId;
        $this->view->auth = $this->auth;
        $this->view->inAuth = $this->inAuth;
        $this->view->entries = $this->view->aggregate;
        $this->view->configuration = $this->configuration;

        if ($this->listRights->isReadAllowed()) {
            $this->view->ltRead = true;
        }
        if ($this->listRights->isWriteAllowed()) {
            $this->view->ltWrite = true;
        }

        foreach ($this->view->aggregate as $entry) {
            if ($this->configuration->getValueByKey('borrow') == 1) {
                if ($entry->isBorrowed()) {
                    if ($this->auth->isLoggedIn() || $entry->isBorrower($this->inAuth->getUserdata("id"))) {
                        $listHasBorrowedItems = true;
                    }
                }
            }
        }

        $this->view->listHasBorrowedItems = $listHasBorrowedItems;


        if ($this->configuration->getValueByKey('sorting') == "opt1number" && $this->listRights->isWriteAllowed()) {
            $this->view->manualsorting = true;
        }

        if ($this->listRights->isWriteAllowed()) {
            $listtoolbase = new \LwListtool\View\ListtoolBase();
            return $listtoolbase->render() . "\n" . $this->view->render();
        }
        else {
            return $this->view->render();
        }
    }

}

