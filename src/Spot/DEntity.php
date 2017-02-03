<?php namespace Dreamlands\Spot;

use Doctrine\DBAL\Schema\Table;
use Spot\Entity;

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
}
