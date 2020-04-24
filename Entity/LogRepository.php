<?php

namespace ArturDoruch\EventLoggerBundle\Entity;

use ArturDoruch\EventLoggerBundle\Log\LogPropertyCollection;
use ArturDoruch\EventLoggerBundle\Log\Property\PropertyInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogRepository extends EntityRepository
{
    private $fieldsToSelect = 'id, state, category, level, message, createdAt';

    /**
     * @var PropertyInterface[]
     */
    private $filterableProperties = [];

    /**
     * @param LogPropertyCollection $logPropertyCollection
     */
    public function setLogPropertyCollection(LogPropertyCollection $logPropertyCollection)
    {
        $this->filterableProperties = $logPropertyCollection->getFilterable();

        $listableProperties = $logPropertyCollection->getListable();
        $this->fieldsToSelect = 'id, state';

        foreach ($listableProperties as $listableProperty) {
            $this->fieldsToSelect .= ', ' . $listableProperty->getName();
        }
    }

    /**
     * @param array $criteria The sql query criteria.
     * @param array $order
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder(array $criteria, array $order = [])
    {
        $builder = $this->createQueryBuilder('l');
        $builder->select('partial l.{'.$this->fieldsToSelect.'}');

        if (!empty($criteria['level'])) {
            $level = $criteria['level'];

            if ($level === 'error_and_higher') {
                $builder->andWhere("l.level = 'critical' OR l.level = 'error'");
            } elseif ($level === 'warning_and_lower') {
                $builder->andWhere("l.level = 'warning' OR l.level = 'notice' OR l.level = 'info'");
            } else {
                $builder->andWhere('l.level = :level')->setParameter('level', $level);
            }
        }

        foreach ($this->filterableProperties as $property) {
            $name = $property->getName();
            $type = $property->getType();

            if ($name === 'level') {
                continue;
            }

            if ($type === 'integer') {
                if (isset($criteria[$name]) && is_numeric($criteria[$name])) {
                    $builder
                        ->andWhere(sprintf('l.%s = :%s', $name, $name))
                        ->setParameter($name, $criteria[$name]);
                }

                continue;
            }

            if ($type === 'datetime') {
                $dateFromName = $name . 'From';
                $dateToName = $name . 'To';

                if (!empty($criteria[$dateFromName])) {
                    $builder
                        ->andWhere(sprintf('l.%s >= :%s', $name, $dateFromName))
                        ->setParameter($dateFromName, $criteria[$dateFromName]);
                }

                if (!empty($criteria[$dateToName])) {
                    $builder
                        ->andWhere(sprintf('l.%s <= :%s', $name, $dateToName))
                        ->setParameter($dateToName, $criteria[$dateToName]);
                }

                continue;
            }

            if (empty($criteria[$name])) {
                continue;
            }

            if ($property->getFilterFormChoices() === null) {
                $builder
                    ->andWhere(sprintf('l.%s LIKE :%s', $name, $name))
                    ->setParameter($name, '%' .$criteria[$name] . '%');
            } else {
                $builder
                    ->andWhere(sprintf('l.%s = :%s', $name, $name))
                    ->setParameter($name, $criteria[$name]);
            }
        }

        foreach ($order as $field => $direction) {
            $builder->addOrderBy('l.'.$field, $direction);
        }

        return $builder;
    }

    /**
     * @param string $category
     * @return int
     */
    public function countLogs(string $category): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('count(l)')
            ->where('l.category = :category')
            ->setParameter('category', $category);

        return $qb->getQuery()->getSingleScalarResult();
    }
}
