<?php

namespace ArturDoruch\EventLoggerBundle\Log\Driver;

use ArturDoruch\EventLoggerBundle\Log\Driver\Exception\LogNotFoundException;
use ArturDoruch\EventLoggerBundle\LogInterface;

/**
 * Interface for log driver managing instances of the ArturDoruch\EventLoggerBundle\Log.
 *
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
interface LogDriverInterface
{
    /**
     * Counts logs with category.
     *
     * @param string $category
     *
     * @return int
     */
    public function count(string $category): int;

    /**
     * Gets query for getting logs collection (for using in paginator).
     *
     * @param array $criteria
     * @param array $order The collection of order data (in format ["orderField" => "orderDirection"]) for sorting logs.
     *
     * @return mixed
     */
    public function getQuery(array $criteria, array $order);

    /**
     * Prepares log from log locator.
     *
     * @param mixed $logLocator Depend on where LogDriver storing logs, can be: path to the log file, doctrine entity, ect.
     *
     * @return LogInterface
     */
    public function prepare($logLocator);

    /**
     * Gets the log with id.
     *
     * @param mixed $id The log id.
     *
     * @return LogInterface
     * @throws \ArturDoruch\EventLoggerBundle\Log\Driver\Exception\LogNotFoundException when log with id was not found.
     */
    public function get($id): LogInterface;

    /**
     * Changes the log state.
     *
     * @param int $state The log new state. One of the ArturDoruch\ScraperLogBundle\Log::STATE_* constant.
     * @param mixed $id The log id.
     *
     * @return LogInterface
     */
    public function changeState(int $state, $id): LogInterface;

    /**
     * Removes logs with id's.
     *
     * @param array $ids
     *
     * @throws \Exception
     */
    public function remove(array $ids);

    /**
     * Removes all logs with category.
     *
     * @param string $category
     *
     * @return int The number of removed logs.
     */
    public function purge(string $category): int;
}