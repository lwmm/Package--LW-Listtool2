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
        $baseurl = \lw_page::getInstance()->getUrl();
        if (!strstr($baseurl, "?")) {
            $baseurl = $baseurl."?gf543=1";
        }
        $this->view->baseurl = $baseurl;
        return $this->view->render();
    }
}