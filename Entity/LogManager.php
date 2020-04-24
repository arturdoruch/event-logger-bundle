<?php

namespace ArturDoruch\EventLoggerBundle\Entity;

use ArturDoruch\EventLoggerBundle\LogInterface;
use ArturDoruch\EventLoggerBundle\Log\LogPropertyCollection;
use ArturDoruch\Tool\ClassValidator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogManager
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var LogRepository
     */
    protected $repository;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var array
     */
    private $fieldNames = [];

    /**
     * @var array The array with field "columnName" => "type" pairs.
     */
    private $fieldTypes = [];

    /**
     * @param Registry $doctrine
     * @param string $entityClass The log entity class name.
     * @param \ArturDoruch\EventLoggerBundle\Log\LogPropertyCollection $logPropertyCollection
     */
    public function __construct(Registry $doctrine, LogPropertyCollection $logPropertyCollection, string $entityClass)
    {
        $this->connection = $doctrine->getConnection();
        $this->em = $doctrine->getManager();

        ClassValidator::validateSubclassOf($entityClass, AbstractLog::class, 'log entity');

        $this->repository = $this->em->getRepository($entityClass);

        if (!$this->repository instanceof LogRepository) {
            throw new \InvalidArgumentException(sprintf(
                'The repository class "%s" of the entity "%s" must extends "%s" repository class.',
                get_class($this->repository), $entityClass, LogRepository::class
            ));
        }

        $this->repository->setLogPropertyCollection($logPropertyCollection);
        $classMetadata = $this->em->getClassMetadata($entityClass);

        $this->tableName = $classMetadata->table['name'];
        $this->fieldNames = $classMetadata->fieldNames;
        $this->setFieldTypes($classMetadata);
    }

    /**
     * @return LogRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Note: ORM cannot be used, because after sql exception EntityManager is closed.
     *
     * @param LogInterface $log
     *
     * @return int
     */
    public function insert(LogInterface $log)
    {
        return $this->connection->insert($this->tableName, $this->prepareInsertData($log), $this->fieldTypes);
    }


    protected function prepareInsertData(LogInterface $log): array
    {
        $data = [];

        foreach ($this->fieldNames as $columnName => $fieldName) {
            $data[$columnName] = $log->get($fieldName);
        }

        unset($data['id']);

        return $data;
    }

    /**
     * @param AbstractLog $log
     */
    public function update(AbstractLog $log)
    {
        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * @param string $category
     *
     * @return int
     */
    public function removeWithCategory(string $category)
    {
        return $this->connection->executeUpdate(
            'DELETE FROM '.$this->tableName.' WHERE category = :category',
            ['category' => $category]
        );
    }

    /**
     * Removes log entities with id.
     *
     * @param array $ids.
     */
    public function remove(array $ids)
    {
        $this->connection->executeQuery(
            'DELETE FROM '.$this->tableName.' WHERE id IN (?)',
            [$ids],
            [Connection::PARAM_INT_ARRAY]
        );
    }


    private function setFieldTypes(ClassMetadata $classMetadata)
    {
        $fieldMapping = $classMetadata->fieldMappings;

        foreach ($fieldMapping as $mapping) {
            $this->fieldTypes[$mapping['columnName']] = $mapping['type'];
        }
    }
}
