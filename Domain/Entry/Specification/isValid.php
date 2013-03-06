<?php

namespace lwListtool\Domain\Entry\Specification;

define("LW_REQUIRED_ERROR", "1");
define("LW_MAXLENGTH_ERROR", "2");
define("LW_BOOL_ERROR", "3");
define("LW_FILETOOBIG_ERROR", "4");
define("LW_WHITELIST_ERROR", "5");
define("LW_BLACKLIST_ERROR", "6");

class isValid extends \LWddd\Validator
{
    public function __construct()
    {
        $this->allowedKeys = array(
                "id",
                "name",
                "published",
                "description",
                "opt2number",
                "opt1bool",
                "opt1text",
                "opt2text",
                "opt3text",
                "opt1file",
                "opt2file");
        
        $mvalue = ini_get("upload_max_filesize");
        if (intval($mvalue) > intval(ini_get("post_max_size"))) $mvalue = ini_get("post_max_size");
        
        if(substr($mvalue, -1, 1)=="M") {
            $mvalue = substr($mvalue, 0, strlen($mvalue)-1)*1024*1024;
        }
        elseif(substr($mvalue, -1, 1)=="K") {
            $mvalue = substr($mvalue, 0, strlen($mvalue)-1)*1024;
        }
        elseif(substr($mvalue, -1, 1)=="G") {
            $mvalue = substr($mvalue, 0, strlen($mvalue)-1)*1024*1024*1024;        
        }
        $this->maxfilesize = $mvalue;
    }
    
    static public function getInstance()
    {
        return new isValid();
    }
    
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }
    
    public function isSatisfiedBy(\lwListtool\Domain\Entry\Object\entry $object)
    {
        $valid = true;
        foreach($this->allowedKeys as $key){
            $method = $key."Validate";
            if (method_exists($this, $method)) {
                $result = $this->$method($key, $object);
                if($result == false){
                    $valid = false;
                }
            }
        }
        return $valid;
    }
    
    public function publishedValidate($key, $object)
    {
        if ($object->getValueByKey($key) != 1 && $object->getValueByKey($key) != 0) {
            $this->addError($key, LW_BOOL_ERROR);
            return false;
        }
        return true;
    }
    
    public function nameValidate($key, $object)
    {
        $value = trim($object->getValueByKey($key));
               
        if (!$value) {
            $this->addError($key, LW_REQUIRED_ERROR);
            return false;
        }
        
        $maxlength = 255;
        if (!$this->hasMaxlength($value, array("maxlength"=>$maxlength)) ) {
            $this->addError($key, LW_MAXLENGTH_ERROR, array("maxlength"=>$maxlength));
            return false;
        }
        return true;
    }
    
    public function descriptionValidate($key, $object)
    {
        $maxlength = 255;
        if (!$this->hasMaxlength($object->getValueByKey($key), array("maxlength"=>$maxlength)) ) {
            $this->addError($key, LW_MAXLENGTH_ERROR, array("maxlength"=>$maxlength));
            return false;
        }
        return true;
    }
    
    public function opt1textValidate($key, $object)
    {
        $maxlength = 255;
        if (!$this->hasMaxlength($object->getValueByKey($key), array("maxlength"=>$maxlength)) ) {
            $this->addError($key, LW_MAXLENGTH_ERROR, array("maxlength"=>$maxlength));
            return false;
        }
        return true;
    }
    
    public function opt2textValidate($key, $object)
    {
        $maxlength = 255;
        if (!$this->hasMaxlength($object->getValueByKey($key), array("maxlength"=>$maxlength)) ) {
            $this->addError($key, LW_MAXLENGTH_ERROR, array("maxlength"=>$maxlength));
            return false;
        }
        return true;
    }
    
    public function opt1fileValidate($key, $object)
    {
        $ok = true;
        $array = $object->getValueByKey($key);
        if (!$array['name']) {
            return true;
        }
        if ($array['size'] > $this->maxfilesize) {
            $this->addError($key, LW_FILETOOBIG_ERROR, array("maxsize"=> $this->maxfilesize, "actualsize"=>$array['size']));
            $ok = false;
        }
        $extlist = '.jpg,.jpeg,.gif,.png';
        $ext = \lw_io::getFileExtension($array['name']);
        $extarray_u = explode(",", $extlist);
        foreach($extarray_u as $singleext) {
            $extarray[] = strtolower(trim($singleext));
        }
        if (!in_array('.'.strtolower($ext), $extarray)) {
            $this->addError($key, LW_WHITELIST_ERROR, array("allowed"=>$extlist, "extension"=>$ext));
            $ok = false;
        }
        return $ok;
    }
    
    public function opt3textValidate($key, $object)
    {
        if ($object->getValueByKey('opt1bool') != 1) {
            return true;
        }
        $value = $object->getValueByKey($key);
        if (!$value) {
            $this->addError($key, LW_REQUIRED_ERROR);
            return false;
        }
        return true;
    }
    
    public function opt2fileValidate($key, $object)
    {
        if ($object->getValueByKey('opt1bool') == 1) {
            return true;
        }
        $ok = true;
        
        $array = $object->getValueByKey($key);
        if (!$array['name']) {
            return true;
        }
        
        if ($array['size'] > $this->maxfilesize) {
            $this->addError($key, LW_FILETOOBIG_ERROR, array("maxsize"=>$this->maxfilesize, "actualsize"=>$array['size']));
            $ok = false;
        }
        
        $ext = \lw_io::getFileExtension($array['name']);
        $extarray_u = explode(",", $this->configuration->getValueByKey('suffix'));
        foreach($extarray_u as $singleext) {
            $extarray[] = strtolower(trim($singleext));
        }
        if ($this->configuration->getValueByKey('suffix_type') == "white") {
            if (!in_array('.'.strtolower($ext), $extarray)) {
                $this->addError($key, LW_WHITELIST_ERROR, array("allowed"=>$this->configuration->getValueByKey('suffix'), "extension"=>$ext));
                $ok = false;
            }
        }
        else {
            if (in_array($ext, $extarray)) {
                $this->addError($key, LW_BLACKLIST_ERROR, array("notallowed"=>$this->configuration->getValueByKey('suffix'), "extension"=>$ext));
                $ok = false;
            }
        }
        return $ok;
    }
}