<?php

namespace LwListtool\Domain\Entry\EventResolver;

class save extends \LWddd\DomainEventResolver
{
    public function __construct($event)
    {
        parent::__construct($event);
        $this->dic = new \lwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Domain\\Entry\\";
        $this->ObjectClass = $this->baseNamespace."Object\\entry";
    }
    
    public function getInstance($event)
    {
        return new save($event);
    }
    
    protected function buildValueObjectFromPostArrays()
    {
        $array = $this->event->getDataByKey('postArray');
        $array['opt1file'] = $this->event->getDataByKey('opt1file');
        $array['opt2file'] = $this->event->getDataByKey('opt2file');
        return new \LWddd\ValueObject($array);
    }
    
    public function saveEntity($entity)
    {
        $config = $this->dic->getConfiguration();
        $this->getCommandHandler()->setFilePath($config['path']['listtool']);
        $result = $this->getCommandHandler()->saveEntity($entity->getId(), $entity->getValues(), $this->event->getParameterByKey("userId"));
        $this->postSaveWork($result, $entity->getId(), $entity);
        return $entity->getId();
    }
    
    public function resolve()
    {
        $DataValueObjectFiltered = $this->getDataValueObjectFilter()->filter($this->buildValueObjectFromPostArrays());
        $entity = $this->buildEntityFromValueObject($DataValueObjectFiltered);
        $entity->setId($this->event->getParameterByKey("id"));
        $isValidSpecification = $this->getIsValidSpecification();
        $isValidSpecification->setConfiguration($this->event->getParameterByKey("configuration"));

        if ($isValidSpecification->isSatisfiedBy($entity)) {
            $id = $this->saveEntity($entity);
            $this->event->getResponse()->setParameterByKey('saved', true);
        }
        else {
            $this->event->getResponse()->setDataByKey('error', $isValidSpecification->getErrors());
            $this->event->getResponse()->setParameterByKey('error', true);
        }                    
        return  $this->event->getResponse();
    }
}