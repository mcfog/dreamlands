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

    public static function fromArray(array $data)
    {
        $instance = new static($data);
        foreach ($data as $key => $val) {
            if (is_array($val) && $instance->relation($key)) {
                $instance->relation($key, static::loadRelation($key, $val));
            }
        }

        return $instance;
    }

    protected static function loadRelation($key, array $data)
    {
        return false;
    }
}
