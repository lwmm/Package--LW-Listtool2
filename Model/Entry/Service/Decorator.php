<?php

namespace lwListtool\Model\Entry\Service;

class Decorator
{
    public function __construct()
    {
    }
    
    public function getInstance()
    {
        return new Decorator();
    }
    
    public function decorate(\LWddd\ValueObject $valueObject)
    {
        
        $values = $valueObject->getValues();
        foreach($values as $key => $value) {
            if(!is_array($value)) {
                $value = trim($value);
            }
            $method = $key.'Filter';
            if (method_exists($this, $method)) {
                $value = $this->$method($value);
            }
            $filteredValues[$key] = $value;
        }
        return new \LWddd\ValueObject($filteredValues);
    }
    
    public function opt3textFilter($value)
    {
        if (substr($value, 0, 7) != "http://" && substr($value, 0, 8) != "https://") {
            $value = "http://".$value;
        }
        return $value;
    }
    
    
}
