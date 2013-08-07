<?php

namespace lwListtool\Model\Configuration\Specification;

class isDeletable 
{
    public function __construct()
    {
    }
    
    static public function getInstance()
    {
        return new isDeletable();
    }
    
    public function isSatisfiedBy($entity)
    {
        return true;
    }
}