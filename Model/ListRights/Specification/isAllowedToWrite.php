<?php

namespace lwListtool\Model\ListRights\Specification;

class isAllowedToWrite extends \LWddd\Validator
{
    public function __construct()
    {
    }
    
    static public function getInstance()
    {
        return new isAllowedToWrite();
    }
    
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }
    
    public function isSatisfiedBy($listId, $userId)
    {
        return $allowed;
    }
}