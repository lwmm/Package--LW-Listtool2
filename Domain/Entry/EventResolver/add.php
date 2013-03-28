<?php

namespace LwListtool\Domain\Entry\EventResolver;

class add extends \LWddd\DomainEventResolver
{
    public function __construct($event)
    {
        parent::__construct($event);
        $this->dic = new \LwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Domain\\Entry\\";
        $this->ObjectClass = $this->baseNamespace."Object\\entry";
    }
    
    public function getInstance($event)
    {
        return new add($event);
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
        $id = $this->getCommandHandler()->addEntity($this->event->getParameterByKey("listId"), $entity->getValues(), $this->event->getParameterByKey("userId"));
        $this->postSaveWork($id, $id, $entity);
        return $id;
    }
    
    public function resolve()
    {
        $DataValueObjectFiltered = $this->getDataValueObjectFilter()->filter($this->buildValueObjectFromPostArrays());
        $entity = $this->buildEntityFromValueObject($DataValueObjectFiltered);
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