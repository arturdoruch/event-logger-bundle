<?php

namespace ArturDoruch\EventLoggerBundle\Log\Driver;

use ArturDoruch\EventLoggerBundle\Entity\AbstractLog;
use ArturDoruch\EventLoggerBundle\Entity\LogManager;
use ArturDoruch\EventLoggerBundle\Entity\LogRepository;
use ArturDoruch\EventLoggerBundle\Log\Driver\Exception\LogNotFoundException;
use ArturDoruch\EventLoggerBundle\Log\LogMetadata;
use ArturDoruch\EventLoggerBundle\LogInterface;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class DatabaseLogDriver implements LogDriverInterface
{
    /**
     * @var LogManager
     */
    private $manager;

    /**
     * @var LogRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $logClass;

    /**
     * @var array The log custom properties getters na setters.
     */
    private $propertyMetadata = [];

    public function __construct(LogManager $logManager, LogMetadata $logMetadata)
    {
        $this->manager = $logManager;
        $this->repository = $this->manager->getRepository();
        $this->logClass = $logMetadata->getClassName();

        $extraProperties = $logMetadata->getPropertyCollection()->getExtra();

        foreach ($extraProperties as $property) {
            $name = ucfirst($property->getName());
            $this->propertyMetadata[] = [
                'logSetter' => 'set' . $name,
                'entityGetter' => 'get' . $name
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function count(string $category): int
    {
        return $this->repository->countLogs($category);
    }

    /**
     * @inheritDoc
     */
    public function getQuery(array $criteria, array $order)
    {
        return $this->repository->getQueryBuilder($criteria, $order);
    }

    /**
     * @inheritDoc
     */
    public function get($id): LogInterface
    {
        $entity = $this->getEntity($id);

        return $this->prepare($entity);
    }

    /**
     * @inheritDoc
     */
    public function changeState(int $state, $id): LogInterface
    {
        $entity = $this->getEntity($id);
        $entity->setState($state);

        $this->manager->update($entity);

        return $this->prepare($entity);
    }

    /**
     * @param $id
     *
     * @return AbstractLog
     */
    protected function getEntity($id): AbstractLog
    {
        if (!$entity = $this->repository->find($id)) {
            throw new LogNotFoundException($id);
        }

        return $entity;
    }

    /**
     * @param AbstractLog $entity
     *
     * @return LogInterface
     */
    public function prepare($entity): LogInterface
    {
        /** @var LogInterface $log */
        $log = new $this->logClass($entity->getCategory(), $entity->getLevel(), $entity->getAction() ?? '');
        $log
            ->setId($entity->getId())
            ->setCreatedAt($entity->getCreatedAt())
            ->setMessage($entity->getMessage())
            ->setContext($entity->getContext())
            ->setState($entity->getState());

        if ($changedStateAt = $entity->getChangedStateAt()) {
            $log->setChangedStateAt($changedStateAt);
        }

        foreach ($this->propertyMetadata as $metadata) {
            $log->{$metadata['logSetter']}($entity->{$metadata['entityGetter']}());
        }

        //$this->setParameters($log, $entity);

        return $log;
    }

    /*
     * Sets log custom parameters from log entity.
     *
     * @param LogInterface $log
     * @param AbstractLog $logEntity
     */
    //abstract protected function setParameters(LogInterface $log, AbstractLog $logEntity);

    /**
     * @inheritDoc
     */
    public function remove(array $ids)
    {
        $this->manager->remove($ids);
    }

    /**
     * @inheritDoc
     */
    public function purge(string $channel): int
    {
        return $this->manager->removeWithCategory($channel);
    }
}
