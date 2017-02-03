<?php namespace Dreamlands\Spot;

use Doctrine\DBAL\Schema\Table;
use Spot\Entity;
use Spot\Relation\RelationAbstract;

/**
 * Class DEntity
 * @package Dreamlands\Spot
 */
class DEntity extends Entity
{
    protected static $mapper = DMapper::class;

    public static function alterTableSchema(Table $table)
    {

    }

    public function &__get($field)
    {
        if (array_key_exists($field, $this->_dataModified) || array_key_exists($field, $this->_data)) {
            return parent::__get($field);
        }

        $relation = $this->relation($field);
        if ($relation instanceof RelationAbstract) {
            $entity = $relation->execute();
            return $entity;
        }

        return parent::__get($field);
    }

}
