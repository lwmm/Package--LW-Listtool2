<?php

namespace lwListtool\Domain\Configuration\Specification;

class isDeletable 
{
    public function __construct()
    {
    }
    
    static public function getInstance()
    {
        return new isDeletable();
    }
    
    public function isSatisfiedBy(lwListtool\Domain\Configuration\Object\configuration $entity)
    {
        return true;
    }
}