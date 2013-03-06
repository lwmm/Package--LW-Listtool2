<?php

namespace lwListtool\Domain\Configuration\Service;

class Filter
{
    public function __construct()
    {
    }
    
    public function getInstance()
    {
        return new Filter();
    }
    
    public function filter(\LWddd\ValueObject $valueObject)
    {
        $values = $valueObject->getValues();
        foreach($values as $key => $value) {
            $value = trim($value);
            $method = $key.'Filter';
            if (method_exists($this, $method)) {
                $value = $this->$method($value);
            }
            $filteredValues[$key] = $value;
        }
        return new \LWddd\ValueObject($filteredValues);
    }
}
