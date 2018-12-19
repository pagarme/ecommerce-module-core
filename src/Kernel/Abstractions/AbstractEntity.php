<?php

namespace Mundipagg\Core\Kernel\Abstractions;

use JsonSerializable;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

/**
 * The Entity Abstraction. All the aggregate roots that are entities should extend
 * this class.
 *
 * Holds the business rules related to entities.
 *
 */
abstract class AbstractEntity implements JsonSerializable
{
    /**
     * @var AbstractValidString
     */
    protected $id;

    /**
     * @return AbstractValidString
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  string $id
     * @return AbstractEntity
     */
    public function setId(AbstractValidString $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Do the identity comparison with another Entity.
     *
     * @param  AbstractEntity $entity
     * @return bool
     */
    public function equals(AbstractEntity $entity)
    {
        return $this->id === $entity->getId();
    }
}