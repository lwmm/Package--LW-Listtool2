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
    
    public function render($votedYes, $votedNo)
    {
        $this->view->yes = $votedYes;
        $this->view->no = $votedNo;
        return $this->view->render();
    }
}
