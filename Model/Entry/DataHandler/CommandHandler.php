<?php

namespace LwListtool\Model\Entry\DataHandler;

class CommandHandler extends \LWmvc\Model\DataCommandHandler
{
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
    
    public function deleteThumbnail($id)
    {
        $this->db->setStatement("UPDATE t:lw_master SET opt1file = '' WHERE id = :id ");
        $this->db->bindParameter("id", "i", $id);
        
        $this->db->pdbquery();
        
        return $this->deleteExistingThumbnail($id);
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
        $this->deleteExistingThumbnail($id);
        $saved = $this->saveFile($array['opt1file']['tmp_name'], $thumbnail, false);
        if ($saved) {
            $this->resizeThumbnail($thumbnail);
            
            
            $this->db->setStatement("UPDATE t:lw_master SET opt1file = :opt1file WHERE id = :id ");
            $this->db->bindParameter("opt1file", 's', $thumbnail);
            $this->db->bindParameter("id", 'i', $id);
            $ok = $this->db->pdbquery();
        }
    }
    
    private function deleteExistingThumbnail($id)
    {
        $dir = \lw_directory::getInstance($this->getFilePath());
        $files = $dir->getDirectoryContents("file");
        foreach($files as $file){
            if (strstr($file->getName(), 'item_'.$id)) {
                if($file->getName() != 'item_'.$id.".file"){
                    $file->delete();
                }
            }
        }
        return true;
    }
    
    private function resizeThumbnail($filename)
    {
        list($width) = @getimagesize($this->getFilePath().$filename);
        if($width > 100){
            $image = new \LwListtool\Model\Entry\Service\ThumbnailResizer($this->getFilePath().$filename);
            $image->setParams(100, 100);
            $image->resize();
            $image->saveImage();        
        }
    }
    
    private function saveFile($tmp, $name, $archive=false)
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
    
    /**
     *              /F020/
     * 
     * Setzen des Flaggs "in Genehmigung" für den Eintrag und speichern
     * des Startdatums, Enddatums und der User-ID des Verfahrensstarters.
     */
    public function startApprovalEntity($id, $userId, $enddate)
    {
        $sek = date("His");
        $this->db->setStatement("UPDATE t:lw_master SET opt3bool = 1, opt2bool = 0, opt5number = 0, opt6number = :opt6number, opt5number = :opt5number, opt7number = :opt7number WHERE id = :id");
        $this->db->bindParameter('id', 'i', $id);
        $this->db->bindParameter('opt6number', 'i', $userId);
        $this->db->bindParameter('opt5number', 'i', date("Ymd").$sek);
        $this->db->bindParameter('opt7number', 'i', $enddate.$sek);
        return $this->db->pdbquery();
    }
   
    public function stoppApprovalEntity($id)
    {
        $this->deleteVoteApprovalEntriesByEntryId($id);
        
        $this->db->setStatement("UPDATE t:lw_master SET opt3bool = 0, opt5number = 0, opt6number = 0, opt7number = 0, opt8number = 0 WHERE id = :id ");
        $this->db->bindParameter('id', 'i', $id);
        return $this->db->pdbquery();
    }
    
    /**
     * Alle Stimmabgaben zu einem Genehmigungsverfahren löschen.
     */
    protected function deleteVoteApprovalEntriesByEntryId($id)
    {
        $this->db->setStatement("DELETE FROM t:lw_master WHERE lw_object = :lw_object AND opt1number = :opt1number ");
        $this->db->bindParameter("lw_object", "s", "lw_listtool2_vote");
        $this->db->bindParameter("opt1number", "i", $id);
        
        return $this->db->pdbquery();
    }

    /**
     *              /F040/ + /F060/
     * 
     * Die abgegebene Stimme wird gespeichert. Bei einer "Nein"-Stimmer
     * wird zusätzlich noch der Kommentar gespeichert.
     */
    public function addVoteApprovalEntry($id, $userId, $listId, $array)
    {
        if($array["lt_vote"] == 0){
            $this->db->setStatement("INSERT INTO t:lw_master (lw_object, category_id, opt1number, opt1bool, opt1text, lw_first_user, lw_first_date) VALUES (:lw_object, :category_id, :opt1number, :opt1bool, :opt1text, :lw_first_user, :lw_first_date) ");
            $this->db->bindParameter("opt1text", "s", htmlentities($array["lt_comment"]));
        }else{            
            $this->db->setStatement("INSERT INTO t:lw_master (lw_object, category_id, opt1number, opt1bool, lw_first_user, lw_first_date) VALUES (:lw_object, :category_id, :opt1number, :opt1bool, :lw_first_user, :lw_first_date) ");
        }
        $this->db->bindParameter("lw_object", "s", "lw_listtool2_vote");
        $this->db->bindParameter("category_id", "i", $listId);
        $this->db->bindParameter("opt1number", "i", $id);
        $this->db->bindParameter("opt1bool", "i", $array["lt_vote"]);
        $this->db->bindParameter("lw_first_user", "i", $userId);
        $this->db->bindParameter("lw_first_date", "i", date("YmdHis"));

        return $this->db->pdbquery();
    }
    
    /**
     * Datum der Versendung speichern.
     */
    public function setDateSendApprovalReminder($id)
    {
        $this->db->setStatement("UPDATE t:lw_master SET opt8number = :date WHERE id = :id ");
        $this->db->bindParameter("id", "i", $id);
        $this->db->bindParameter("date", "i", date("YmdHis"));
        
        return $this->db->pdbquery();
    }
    
    /**
     *              /F021/
     * 
     * Alle Informationen zum Genehmigungsverfahren löschen ( Stimmabgaben ) und den
     * Flagg setzen, dass dieser Eintrag genehmigt worden ist. 
     */
    public function setEntryApproved($id)
    {
        $this->deleteVoteApprovalEntriesByEntryId($id);
        
        $this->db->setStatement("UPDATE t:lw_master SET opt3bool = 0, opt4bool = 1, opt5number = :date, opt6number = 0, opt7number = 0, opt8number = 0 WHERE id = :id ");
        $this->db->bindParameter('id', 'i', $id);
        $this->db->bindParameter("date", "i", date("YmdHis"));
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