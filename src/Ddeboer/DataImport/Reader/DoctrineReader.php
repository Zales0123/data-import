<?php

namespace Ddeboer\DataImport\Reader;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query;

/**
 * Reads entities through the Doctrine ORM
 *
 */
class DoctrineReader implements CountableReaderInterface
{
    protected $objectManager;
    protected $objectName;

    /**
     * @var IterableResult
     */
    protected $iterableResult;

    /**
     * @var array
     */
    protected $fields;
    
    /**
     * Constuctor
     *
     * @param ObjectManager $objectManager Doctrine object manager
     * @param string        $objectName    Doctrine object name, e.g.
     *                                     YourBundle:YourEntity
     */
    public function __construct(ObjectManager $objectManager, $objectName, array $fields = array())
    {
        $this->objectManager = $objectManager;
        $this->objectName = $objectName;
        $this->fields = $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        if ($this->fields === array()) {
            return $this->fields;
        }
        return $this->objectManager->getClassMetadata($this->objectName)
                 ->getFieldNames();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->iterableResult->current());
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->iterableResult->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iterableResult->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->iterableResult->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        if (!$this->iterableResult) {
            if ($this->fields == array()) {
                $query = $this->objectManager->createQuery(
                    sprintf('select o from %s o', $this->objectName)
                );
            } else {
                $fields = $this->fields;
                array_walk($fields, function (&$value) { $value = 'o.'.$value; });
                $query = $this->objectManager->createQuery(
                    sprintf('select %s from %s o', join(', ', $fields), $this->objectName)
                );
            }
            $this->iterableResult = $query->iterate(array(), Query::HYDRATE_ARRAY);
        }

        $this->iterableResult->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $query = $this->objectManager->createQuery(
            sprintf('select count(o) from %s o', $this->objectName)
        );

        return $query->getSingleScalarResult();
    }
}
