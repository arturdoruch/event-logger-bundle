<?php

namespace ArturDoruch\EventLoggerBundle\Entity;

use ArturDoruch\EventLoggerBundle\LogInterface;
use ArturDoruch\EventLoggerBundle\LogTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass(repositoryClass="ArturDoruch\EventLoggerBundle\Entity\LogRepository")
 * @ORM\Table(
 *      indexes={
 *          @ORM\Index(columns={"category"}),
 *          @ORM\Index(columns={"level"}),
 *          @ORM\Index(columns={"action"})
 *      }
 * )
 *
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
abstract class AbstractLog
{
    use LogTrait;
    
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    protected $category;

    /**
     * @var string
     * @ORM\Column(type="string", length=10)
     */
    protected $level;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $action;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $message;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    protected $context = [];

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $state;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $changedStateAt;

    /**
     * @param LogInterface $log
     */
    public function __construct(LogInterface $log)
    {
        $this->createdAt = $log->getCreatedAt() ?: new \DateTime();
        $this->category = $log->getCategory();
        $this->level = strtolower($log->getLevel());
        $this->action = $log->getAction();
        $this->message = $log->getMessage();
        $this->context = $log->getContext();
        $this->state = $log->getState();
        $this->changedStateAt = $log->getChangedStateAt();
    }


    public function setId($id)
    {
        throw new \LogicException('Calling method "setId" is not allowed.');
    }

    /**
     * @param int $state
     *
     * @return $this
     */
    public function setState(int $state)
    {
        $this->state = $state;
        $this->changedStateAt = new \DateTime();

        return $this;
    }
}
 