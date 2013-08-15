<?php

namespace lwListtool\Services;

class Autoloader
{
    public function __construct()
    {
        spl_autoload_register(array($this, 'loader'));
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }
    
    private function loader($className) 
    {
        die($className);
        if (strstr($className, 'LWddd')) {
            $config = \lw_registry::getInstance()->getEntry('config');
            $path = $this->config['plugin_path']['lw'].'lw_ddd';
            $filename = str_replace('LWddd', $path, $className);
        }
        elseif (strstr($className, 'LWmvc')) {
            $config = \lw_registry::getInstance()->getEntry('config');
            $path = $this->config['plugin_path']['lw'].'lw_mvc';
            $filename = str_replace('LWmvc', $path, $className);
        }
        elseif (strstr($className, 'LwI18n')) {
            $config = \lw_registry::getInstance()->getEntry('config');
            $path = $this->config['plugin_path']['lw'].'lw_i18n';
            $filename = str_replace('LwI18n', $path, $className);
        }
        elseif (strstr($className, 'LwMailer')) {
            $config = \lw_registry::getInstance()->getEntry('config');
            $path = $this->config['path']['package'].'LwMailer/Controller/';
            $filename = $path . $className;
            die($filename);
        }
        else {
            $path = dirname(__FILE__).'/../..';
            $filename = str_replace('lwListtool', $path.'/lw_listtool2', $className);
        }
        $filename = str_replace('\\', '/', $filename).'.php';
        
        if (is_file($filename)) {
            include_once($filename);
        }
    }
}
