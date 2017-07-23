<?php namespace Dreamlands\Entity;


use Doctrine\DBAL\Schema\Table;
use Dreamlands\Exceptions\DException;
use Dreamlands\Spot\DEntity;

/**
 * Class UserEntity
 * @package Dreamlands\Entity
 *
 * @property int $id
 * @property string $hash
 * @property string $nickname
 * @property $name
 * @property int $uniq
 * @property string $last_ip
 * @property int $expire_at
 * @property int $created_at
 */
class UserEntity extends DEntity
{
    protected static $table = 'user';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'hash' => [
                'type' => 'string',
                'required' => true,
                'length' => 40,
                'unique' => true,
                'customSchemaOptions' => ['collation' => 'ascii_bin']
            ],
            'nickname' => ['type' => 'string', 'required' => true, 'length' => 10],
            'last_ip' => ['type' => 'string', 'length' => 40, 'customSchemaOptions' => ['collation' => 'ascii_bin']],
            'uniq' => ['type' => 'integer', 'required' => true],
            'expire_at' => ['type' => 'integer', 'required' => true, 'index' => true],
            'created_at' => ['type' => 'integer', 'value' => time(), 'required' => true],
        ];
    }

    public static function alterTableSchema(Table $table)
    {
        parent::alterTableSchema($table);

        $table->addUniqueIndex(['nickname', 'uniq'], 'nickname_uniq');
    }

    public static function spawn($nickname)
    {
        if (!self::isValidNickname($nickname)) {
            throw new DException('非法的昵称');
        }

        $user = new static();
        $user->nickname = $nickname;
        $user->uniq = crc32(uniqid('', true)) % (2 ** 14);
        $user->hash = sha1(uniqid('', true));
        $user->expire_at = time() + 72 * 86400;

        return $user;
    }

    public static function isValidNickname($nickname)
    {
        return preg_match('/^[a-zA-Z0-9_\-.\x{0800}-\x{9fff}]{1,10}$/u', $nickname);
    }

    public function getDisplayName()
    {
        return sprintf('%s#%d', $this->nickname, $this->uniq);
    }
}
