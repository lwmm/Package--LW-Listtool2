<?php

namespace LwListtool\View;

class ListtoolBase extends \LWmvc\View
{
    public function __construct()
    {
        $this->view = new \lw_view(dirname(__FILE__).'/templates/listbase.tpl.phtml');
    }

    public function render()
    {
        $this->view->addurlfile = \lw_page::getInstance()->getUrl(array("cmd"=>"showAddFileForm"));
        $this->view->addurllink = \lw_page::getInstance()->getUrl(array("cmd"=>"showAddLinkForm"));
        $this->view->sorturl = \lw_page::getInstance()->getUrl(array("cmd"=>"sortEntries"));
        $this->view->baseurl = \lw_page::getInstance()->getUrl();
        return $this->view->render();
    }
}