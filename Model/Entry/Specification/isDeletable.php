<?php

namespace LwListtool\Model\Entry\Specification;

class isDeletable 
{
    public function __construct()
    {
    }
    
    static public function getInstance()
    {
        return new isDeletable();
    }
    
    public function isSatisfiedBy(LwListtool\Model\Entry\Object\entry $entity)
    {
        return true;
    }
}