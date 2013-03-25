<?php

namespace LwListtool\Services;

class PathTest
{

    public function __construct($config)
    {
        $this->ListtoolPath = $config['path']['listtool'];
    }
    
    public function PathExistsAndIsWriteable()
    {
        if (!is_dir($this->ListtoolPath)) {
            mkdir($this->ListtoolPath);
        }
        if (is_dir($this->ListtoolPath) && is_writable($this->ListtoolPath)) {
            return true;
        }
        return false;
    }
    
    public function checkPathSecurity()
    {
        $dir = \lw_directory::getInstance($this->ListtoolPath);
        $files = $dir->getDirectoryContents('file');
        $available = false;
        foreach($files as $file) {
            if ($file->getName() == '.htaccess') {
                $available = true;
            }
        }
        if ($available) {
            return true;
        }
        else {
            file_put_contents($this->ListtoolPath.'.htaccess', "deny from all");
        }
    }
}