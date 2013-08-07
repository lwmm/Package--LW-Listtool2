<?php

/**************************************************************************
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
***************************************************************************/

namespace LwListtool\View;

class Sortlist extends \LWmvc\View\View
{
    public function __construct($type)
    {
        parent::__construct('edit');
        $this->dic = new \LwListtool\Services\dic();
        $this->systemConfiguration = $this->dic->getConfiguration();
    }

    public function setListRights($rights)
    {
        $this->listRights = $rights;
    }
    
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }
    
    public function init()
    {
        $this->view = new \lw_view(dirname(__FILE__).'/templates/sortlist.tpl.phtml');
    }    
    
    public function render()
    {
        $this->view->urlmedia = $this->systemConfiguration['url']['media'];
        $this->view->actionUrl = \lw_page::getInstance()->getUrl(array("cmd"=>"saveSorting"));
        $form = $this->view->render();
        $popupView = new \LwListtool\View\Popup();
        $popupView->setForm($form);
        return $popupView->render();
    }
}