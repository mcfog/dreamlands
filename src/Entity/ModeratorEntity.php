<?php namespace Dreamlands\Entity;


use Dreamlands\Spot\DEntity;

/**
 * Class UserEntity
 * @package Dreamlands\Entity
 *
 * @property $id
 * @property $name
 * @property $open_id
 * @property $is_super
 * @property $created_at
 */
class ModeratorEntity extends DEntity
{
    protected static $table = 'moderator';
    const PROVIDER_GITHUB = 'github';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'name' => ['type' => 'string', 'required' => true, 'unique' => true],
            'open_id' => ['type' => 'string', 'required' => true, 'length' => 255, 'unique' => true],
            'is_super' => ['type' => 'boolean', 'value' => false, 'required' => true],
            'created_at' => ['type' => 'integer', 'value' => time(), 'required' => true],
        ];
    }

    public static function getOpenId($provider, $id)
    {
        return sprintf('%s:#%s', $provider, $id);
    }
}
