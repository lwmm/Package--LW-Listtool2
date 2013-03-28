<?php

namespace LwListtool\Domain\Entry\DataHandler;

class CommandHandler
{
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function setFilePath($path)
    {
        $this->filePath = $path;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }
    
    public function deleteEntity($id, $listId)
    {
        $this->db->setStatement("DELETE FROM t:lw_master WHERE id = :id AND category_id = :listid ");
        $this->db->bindParameter("id", 'i', $id);
        $this->db->bindParameter("listid", 'i', $listId);
        $ok = $this->db->pdbquery();
        if ($ok) {
            $this->deleteFiles($id);
        }
        return $ok;
    }
    
    protected function deleteFiles($id)
    {
        $dir = \lw_directory::getInstance($this->getFilePath());
        $files = $dir->getDirectoryContents('file');
        foreach ($files as $file) {
            if (strstr($file->getName(), 'item_'.$id.'.')) {
                $file->delete();
            }
        }        
        
        $dir = \lw_directory::getInstance($this->getFilePath()."archive/");
        $files = $dir->getDirectoryContents('file');
        foreach ($files as $file) {
            if (strstr($file->getName(), 'item_'.$id.'.')) {
                $file->delete();
            }
        }
    }
    
    public function addEntity($listId, $array, $userId, $archive)
    {
        print_r($array);die();
        $this->db->setStatement("INSERT INTO t:lw_master ( lw_object, category_id, name, description, published, opt1bool, opt2number, opt1text, opt2text, opt3text, lw_first_date, lw_first_user, lw_last_date, lw_last_user ) VALUES ( 'lw_listtool2', :listid, :name, :description, :published, :opt1bool, :opt2number, :opt1text, :opt2text, :opt3text, :firstdate, :firstuser, :lastdate, :lastuser ) ");
        $this->db->bindParameter("listid", 'i', $listId);
        $this->db->bindParameter("name", 's', $array['name']);
        $this->db->bindParameter("description", 's', $array['description']);
        $this->db->bindParameter("published", 's', $array['published']);
        $this->db->bindParameter("opt1bool", 's', $array['opt1bool']);
        $this->db->bindParameter("opt2number", 's', $array['opt2number']);
        $this->db->bindParameter("opt1text", 's', $array['opt1text']);
        $this->db->bindParameter("opt2text", 's', $array['opt2text']);
        $this->db->bindParameter("opt3text", 's', $array['opt3text']);
        $this->db->bindParameter("firstdate", 's', date("YmdHis"));
        $this->db->bindParameter("firstuser", 'i', $userId);
        $this->db->bindParameter("lastdate", 's', date("YmdHis"));
        $this->db->bindParameter("lastuser", 'i', $userId);
        $id = $this->db->pdbinsert($this->db->gt('lw_master'));
        
        if ($id && $array['opt2file']['size'] > 0) {
            $this->saveEntryFile($id, $array, $archive);
        }
        
        if ($id && $array['opt1file']['size'] > 0) {
            $this->saveThumbnailFile($id, $array);
        }
        return $id;
    }
    
    public function saveEntity($id, $array, $userId, $archive)
    {
        $this->db->setStatement("UPDATE t:lw_master SET name = :name, description = :description, published = :published, opt2number = :opt2number, opt1text = :opt1text, opt2text = :opt2text, opt3text = :opt3text, lw_last_date = :lastdate, lw_last_user = :lastuser WHERE id = :id ");
        $this->db->bindParameter("id", 'i', $id);
        $this->db->bindParameter("name", 's', $array['name']);
        $this->db->bindParameter("description", 's', $array['description']);
        $this->db->bindParameter("published", 's', $array['published']);
        $this->db->bindParameter("opt2number", 's', $array['opt2number']);
        $this->db->bindParameter("opt1text", 's', $array['opt1text']);
        $this->db->bindParameter("opt2text", 's', $array['opt2text']);
        $this->db->bindParameter("opt3text", 's', $array['opt3text']);
        $this->db->bindParameter("lastdate", 's', date("YmdHis"));
        $this->db->bindParameter("lastuser", 'i', $userId);
        $ok = $this->db->pdbquery();
        
        if ($ok && $array['opt2file']['size'] > 0) {
            $this->saveEntryFile($id, $array, $archive);
        }
        if ($ok && $array['opt1file']['size'] > 0) {
            $this->saveThumbnailFile($id, $array);
        }
        return $ok;
    }
    
    protected function saveEntryFile($id, $array, $archive=false)
    {
        $filename = 'item_'.$id.'.file';
        $saved = $this->saveFile($array['opt2file']['tmp_name'], $filename, $archive);
        if ($saved) {
            $this->db->setStatement("UPDATE t:lw_master SET opt2file = :opt2file, opt3number = :opt3number WHERE id = :id ");
            $this->db->bindParameter("opt2file", 's', $array['opt2file']['name']);
            $this->db->bindParameter("opt3number", 's', date("YmdHis"));
            $this->db->bindParameter("id", 'i', $id);
            $ok = $this->db->pdbquery();
        }
    }
    
    protected function saveThumbnailFile($id, $array)
    {
        $thumbnail = 'item_'.$id.'.'.\lw_io::getFileExtension($array['opt1file']['name']);
        $saved = $this->saveFile($array['opt1file']['tmp_name'], $thumbnail, false);
        if ($saved) {
            $this->db->setStatement("UPDATE t:lw_master SET opt1file = :opt1file WHERE id = :id ");
            $this->db->bindParameter("opt1file", 's', $thumbnail);
            $this->db->bindParameter("id", 'i', $id);
            $ok = $this->db->pdbquery();
        }
    }
    
    public function saveFile($tmp, $name, $archive=false)
    {
        $dir = \lw_directory::getInstance($this->getFilePath());
        if ($dir->fileExists($name)) {
            if ($archive) {
                $target = $dir->getPath().'archive/'.date(YmdHis).'_'.$name;
                copy($dir->getPath().$name, $target);
                $this->_updatePermissions($target);
            }
            $ok = $dir->deleteFile($name);
        }
        $ok = $dir->addFile($tmp, $name);
        $this->_updatePermissions($dir->getPath().$name);
        return true;
    }     
    
    private function _updatePermissions($file)
    {
        return true;
        $config = \lw_registry::getInstance()->getEntry("config");
        
        if ($config['files']['chgrp']) {
            @chgrp($file, $config['files']['chgrp']);
        }
        if ($config['files']['chmod']) {
            @chmod($file, octdec($config['files']['chmod']));
        }
    } 

    public function saveSequence($id, $seq)
    {
        $this->db->setStatement("UPDATE t:lw_master SET opt1number = :seq WHERE id = :id");
        $this->db->bindParameter('id', 'i', $id);
        $this->db->bindParameter('seq', 'i', $seq);
        return $this->db->pdbquery();
    }
   
    public function borrowEntity($id, $userId)
    {
        $this->db->setStatement("UPDATE t:lw_master SET opt2bool = 1, opt5number = :opt5number, opt6number = :opt6number WHERE id = :id");
        $this->db->bindParameter('id', 'i', $id);
        $this->db->bindParameter('opt5number', 's', date('YmdHis'));
        $this->db->bindParameter('opt6number', 'i', $userId);
        return $this->db->pdbquery();
    }
   
    public function releaseEntity($id)
    {
        $this->db->setStatement("UPDATE t:lw_master SET opt2bool = 0, opt5number = 0, opt6number = 0 WHERE id = :id");
        $this->db->bindParameter('id', 'i', $id);
        return $this->db->pdbquery();
    }
   
    
    
    
    
    
    
    
    
    
    
    

    
    public function getHighestSeqInPage($oid)
    {
        $this->db->setStatement("SELECT max(opt1number) as maxseq FROM t:lw_master m WHERE ".$this->_getBaseWhere(array('oid' => $oid)));
        $erg = $this->db->pselect1($sql);
        return $erg['maxseq'];
    }    
    
    public function deleteItemByCategory($oid) {
        $sql = "DELETE FROM ".$this->db->gt("lw_master")." WHERE category_id = ".intval($oid);
        return $this->db->dbquery($sql);
    }
    
   
}