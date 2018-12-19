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
     * @var int
     */
    protected $id;

    /**
     * Almost every Entity has an equivalent at mundipagg. This property holds the
     * Mundipagg ID for the entity.
     *
     * @var AbstractValidString
     */
    protected $mundipaggId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  string $id
     * @return AbstractEntity
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AbstractValidString
     */
    public function getMundipaggId()
    {
        return $this->mundipaggId;
    }

    /**
     * @param AbstractValidString $mundipaggId
     * @return AbstractEntity
     */
    public function setMundipaggId(AbstractValidString $mundipaggId)
    {
        $this->mundipaggId = $mundipaggId;
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