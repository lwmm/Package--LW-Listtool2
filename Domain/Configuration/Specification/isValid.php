<?php

namespace lwListtool\Domain\Configuration\Specification;

define("LW_REQUIRED_ERROR", "1");
define("LW_MAXLENGTH_ERROR", "2");

class isValid extends \LWddd\Validator
{
    public function __construct()
    {
        $this->allowedKeys = array(
                "id",
                "name",
                "opt1text",
                "lw_object",
                "lw_first_date",
                "lw_last_date");        
    }
    
    static public function getInstance()
    {
        return new isValid();
    }
    
    public function isSatisfiedBy(lwListtool\Domain\Configuration\Object\organization $object)
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
    
    public function nameValidate($key, $object)
    {
        $maxlength = 255;
        if (!$this->hasMaxlength($object->getValueByKey($key), array("maxlength"=>$maxlength)) ) {
            $this->addError($key, LW_MAXLENGTH_ERROR, array("maxlength"=>$maxlength));
            return false;
        }
        return true;
    }
}