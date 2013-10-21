<?php

namespace LwListtool\View;

class PieChart extends \LWmvc\View\View
{
    public function __construct()
    {
        parent::__construct();
        $this->dic = new \LwListtool\Services\dic();
        $this->systemConfiguration = $this->dic->getConfiguration();
        $this->view = new \lw_view(dirname(__FILE__).'/templates/PieChart.tpl.phtml');
    }
    
    public function render($votedYes, $votedNo, $votedNot)
    {
        $this->view->yes = $votedYes;
        $this->view->no = $votedNo;
        $this->view->not = $votedNot;
        return $this->view->render();
    }
}
