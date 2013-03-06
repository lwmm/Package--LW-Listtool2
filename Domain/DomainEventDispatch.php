<?php

namespace lwListtool\Domain;

class DomainEventDispatch 
{
    public function __construct()
    {
        
    }
    
    public static function getinstance()
    {
        return new DomainEventDispatch();
    }
    
    public function execute($event)
    {
        if ($event->getDomainName() == "Configuration") {
            $DomainEventHandlerClass = "\\lwListtool\\Domain\\".$event->getDomainName()."\\EventHandler";
            return $DomainEventHandlerClass::getInstance()->execute($event);
        }
        $class = "\\lwListtool\\Domain\\".$event->getDomainName()."\\EventResolver\\".$event->getEventName();
        return $class::getInstance($event)->resolve();
    }
}