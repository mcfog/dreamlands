<?php namespace Dreamlands\Entity;

use Doctrine\DBAL\Schema\Table;
use Dreamlands\Spot\DEntity;
use Spot\EntityInterface;
use Spot\MapperInterface;

/**
 * Class PostEntity
 * @package Dreamlands\Entity
 *
 * @property $id;
 * @property $parent_id;
 * @property $user_id;
 * @property $type;
 * @property $flag;
 * @property $title;
 * @property $contentType;
 * @property $content;
 * @property $created_at;
 * @property $touched_at;
 * @property $deleted_at;
 * @property $via;
 * @property UserEntity $user;
 */
class PostEntity extends DEntity
{
    const TYPE_BOARD = 0;
    const TYPE_THREAD = 1;
    const TYPE_REPLY = 2;
    const CONTENT_TYPE_PLAIN = 1;
    const CONTENT_TYPE_HTML = 2;
//    const CONTENT_TYPE_MD = 3;
    const VIA_WEB = 'web';

    protected static $table = 'post';

    public static $childTypeMap = [
        self::TYPE_BOARD => self::TYPE_THREAD,
        self::TYPE_THREAD => self::TYPE_REPLY,
    ];
    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'parent_id' => ['type' => 'integer', 'required' => true],
            'user_id' => ['type' => 'integer', 'required' => true],
            'type' => ['type' => 'smallint', 'required' => true],
            'flag' => ['type' => 'integer', 'required' => true, 'default' => 0],
            'title' => ['type' => 'string'],
            'contentType' => ['type' => 'smallint', 'required' => true],
            'content' => ['type' => 'text'],
            'created_at' => ['type' => 'integer', 'value' => time(), 'required' => true],
            'touched_at' => ['type' => 'integer', 'value' => time(), 'required' => true],
            'deleted_at' => ['type' => 'integer'],
            'via' => ['type' => 'string', 'required' => true, 'default' => 'web'],
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'user' => $mapper->belongsTo($entity, UserEntity::class, 'user_id')
        ];
    }

    public static function alterTableSchema(Table $table)
    {
        parent::alterTableSchema($table);

        $table
            ->addIndex(['parent_id', 'touched_at'], 'pid_touch')//board thread list
//            ->addIndex(['parent_id', 'user_id', 'touched_at'])
            ->addOption('auto_increment', 100000);
    }

    public static function newThread(
        UserEntity $userEntity,
        PostEntity $board,
        $title,
        $content,
        $contentType = self::CONTENT_TYPE_PLAIN
    ) {
        if($board->type !== self::TYPE_BOARD) {
            throw new \Exception(__METHOD__ . '/' . __LINE__);
        }

        return new self([
            'parent_id' => $board->id,
            'user_id' => $userEntity->id,
            'type' => self::TYPE_THREAD,
            'flag' => 0,
            'title' => $title,
            'contentType' => $contentType,
            'content' => $content,
            'created_at' => time(),
            'touched_at' => time(),
            'deleted_at' => null,
            'via' => self::VIA_WEB,
        ]);
    }

    public static function newReply(
        UserEntity $userEntity,
        PostEntity $thread,
        $title,
        $content,
        $contentType = self::CONTENT_TYPE_PLAIN
    ) {
        if ($thread->type !== self::TYPE_THREAD) {
            throw new \Exception(__METHOD__ . '/' . __LINE__);
        }

        return new self([
            'parent_id' => $thread->id,
            'user_id' => $userEntity->id,
            'type' => self::TYPE_REPLY,
            'flag' => 0,
            'title' => $title,
            'contentType' => $contentType,
            'content' => $content,
            'created_at' => time(),
            'touched_at' => time(),
            'deleted_at' => null,
            'via' => self::VIA_WEB,
        ]);
    }
}
