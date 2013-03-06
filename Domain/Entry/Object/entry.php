<?php

namespace lwListtool\Domain\Entry\Object;

class entry extends \LWddd\Entity
{
    public function __construct($id=false)
    {
        parent::__construct($id);
        $this->dic = new \lwListtool\Services\dic();
        $this->systemConfiguration = $this->dic->getConfiguration();
        $this->path = $this->systemConfiguration['path']['listtool'];
    }
    
    public function renderView($view)
    {
        $view->entity = $this;
    }
    
    public function isLink()
    {
        if ($this->getValueByKey('opt1bool') == 1) {
            return true;
        }
        return false;
    }
    
    public function isFile()
    {
        if ($this->getValueByKey('opt1bool') != 1) {
            return true;
        }
        return false;
    }
    
    public function getFilePath()
    {
        return $this->path.'item_'.$this->getValueByKey("id").'.file';
    }
    
    public function getThumbnailPath()
    {
        return $this->path.$this->getValueByKey("opt1file");
    }
    
    public function getThumbnailUrl()
    {
        return \lw_page::getInstance()->getUrl(array("cmd"=>"showThumbnail", "id"=>$this->getValueByKey("id")));
    }
    
    public function hasFile()
    {
        if (is_file($this->getFilePath())) {
            return true;
        }
        return false;
    }
    
    public function getFileRights()
    {
        $file = new \lw_file($this->getFilePath());
        return $file->getRights();
    }
    
    public function getFileSize()
    {
        $file = new \lw_file($this->getFilePath());
        return $file->getSize();
    }
    
    public function hasLastDate()
    {
        return false;
    }
    
    public function hasFirstDate()
    {
        return false;
    }
    
    public function hasUsername()
    {
        return false;
    }
    
    public function hasThumbnail()
    {
        if (is_file($this->getThumbnailPath())) {
            return true;
        }
        return false;
    }
    
    public function getFirstDate()
    {
        $date = substr($this->getValueByKey('lw_first_date'), 0, 8);
        return \lw_object::formatDate($date);
    }
    
    public function getFirstTime()
    {
        $hour = substr($this->getValueByKey('lw_first_date'), 8, 2);
        $min = substr($this->getValueByKey('lw_first_date'), 10, 2);
        $sec = substr($this->getValueByKey('lw_first_date'), 12, 2);
        
        return $hour.':'.$min.':'.$sec;
    }
    
    public function getLastDate()
    {
        $date = substr($this->getValueByKey('lw_last_date'), 0, 8);
        return \lw_object::formatDate($date);
    }
    
    public function getLastTime()
    {
        $hour = substr($this->getValueByKey('lw_last_date'), 8, 2);
        $min = substr($this->getValueByKey('lw_last_date'), 10, 2);
        $sec = substr($this->getValueByKey('lw_last_date'), 12, 2);
        
        return $hour.':'.$min.':'.$sec;
    }
    
    public function isBorrowed()
    {
        if ($this->getValueByKey('opt2bool') == 1) {
            return true;
        }
        return false;
    }
    
    public function isBorrower($userId)
    {
        if ($this->getValueByKey('opt6number') == $userId) {
            return true;
        }
        return false;
    }
    
    public function getBorrowerId()
    {
        return $this->getValueByKey('opt6number');
    }
    
    public function getBorrowerName()
    {
        $db = $this->dic->getDbObject();
        $result = $db->select1("SELECT name FROM ".$db->gt("lw_in_user")." WHERE id = ".intval($this->getValueByKey('opt6number')));
        return $result['name'];
    }
}