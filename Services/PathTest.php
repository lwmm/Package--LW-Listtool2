<?php

namespace LwListtool\Services;

class PathTest
{

    public function __construct($config)
    {
        $this->ListtoolPath = $config['path']['listtool'];
        $this->ListtoolPathArchive = $config['path']['listtool']."archive/";
    }
    
    public function PathExistsAndIsWriteable()
    {
        $bool = false;
        $bool2 = false;
        
        if (!is_dir($this->ListtoolPath)) {
            mkdir($this->ListtoolPath);
        }
        if (is_dir($this->ListtoolPath) && is_writable($this->ListtoolPath)) {
            $bool = true;
        }
        
        if (!is_dir($this->ListtoolPathArchive)) {
            mkdir($this->ListtoolPathArchive);
        }
        if (is_dir($this->ListtoolPathArchive) && is_writable($this->ListtoolPathArchive)) {
            $bool2 = true;
        }
        
        if($bool && $bool2) {
            return true;
        }
        
        return false;
    }
    
    public function checkPathSecurity()
    {
        $dir = \lw_directory::getInstance($this->ListtoolPath);
        $dir2 = \lw_directory::getInstance($this->ListtoolPathArchive);
        $files = $dir->getDirectoryContents('file');
        $files2 = $dir2->getDirectoryContents('file');
        $available = false;
        $available2 = false;
        
        foreach($files as $file) {
            if ($file->getName() == '.htaccess') {
                $available = true;
            }
        }
        
        foreach($files2 as $file) {
            if ($file->getName() == '.htaccess') {
                $available2 = true;
            }
        }
        if ($available && $available2) {
            return true;
        }
        elseif(!$available) {
            file_put_contents($this->ListtoolPath.'.htaccess', "deny from all");
        }
        elseif (!$available2) {
            file_put_contents($this->ListtoolPathArchive.'.htaccess', "deny from all");
        }
    }
}