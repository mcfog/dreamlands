<?php namespace Dreamlands\Entity;


use Dreamlands\Spot\DEntity;

/**
 * Class UserEntity
 * @package Dreamlands\Entity
 *
 * @property $id
 * @property $hash
 * @property $name
 * @property $expire_at
 * @property $created_at
 */
class UserEntity extends DEntity
{
    protected static $table = 'user';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'hash' => ['type' => 'string', 'required' => true, 'length' => 40],
            'name' => ['type' => 'string', 'required' => true, 'unique' => true],
            'expire_at' => ['type' => 'integer'],
            'created_at' => ['type' => 'integer', 'value' => time()],
        ];
    }

    public static function spawn()
    {
        $user = new static();
        $user->hash = sha1(uniqid('', true));
        $user->name = substr(str_replace(str_split('+=/'), '',base64_encode(sha1(uniqid(), true))), 0, 8);
        $user->expire_at = time() + 72 * 86400;

        return $user;
    }
}
