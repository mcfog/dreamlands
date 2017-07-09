<?php namespace Dreamlands\Entity;

use Doctrine\DBAL\Schema\Table;
use Dreamlands\Spot\DEntity;
use Dreamlands\Utility\Utility;
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
 * @property $child_count;
 * @property $latest_childs;
 * @property $title;
 * @property $content_type;
 * @property $content;
 * @property $created_at;
 * @property $touched_at;
 * @property $deleted_at;
 * @property $via;
 * @property UserEntity $user;
 * @property PostEntity $parent;
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
            'parent_id' => ['type' => 'integer', 'notnull' => false],
            'user_id' => ['type' => 'integer', 'notnull' => false],
            'type' => ['type' => 'smallint', 'required' => true],
            'flag' => ['type' => 'integer', 'required' => true, 'default' => 0],
            'child_count' => ['type' => 'integer', 'value' => 0],
            'latest_childs' => ['type' => 'json_array', 'value' => []],
            'title' => ['type' => 'string'],
            'content_type' => ['type' => 'smallint', 'required' => true],
            'content' => ['type' => 'text'],
            'created_at' => ['type' => 'integer', 'value' => time(), 'required' => true],
            'touched_at' => ['type' => 'bigint', 'value' => Utility::getNanotime(), 'required' => true],
            'deleted_at' => ['type' => 'integer', 'value' => null],
            'via' => ['type' => 'string', 'required' => true, 'default' => 'web'],
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'user' => $mapper->belongsTo($entity, UserEntity::class, 'user_id'),
            'parent' => $mapper->belongsTo($entity, PostEntity::class, 'parent_id'),
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

        $postEntity = new self;
        $postEntity->parent = $board;
        $postEntity->parent_id = $board->id;
        $postEntity->user = $userEntity;
        $postEntity->user_id = $userEntity->id;
        $postEntity->type = self::TYPE_THREAD;
        $postEntity->flag = 0;
        $postEntity->title = $title;
        $postEntity->content_type = $contentType;
        $postEntity->content = $content;
        $postEntity->via = self::VIA_WEB;

        return $postEntity;
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

        $postEntity = new self;
        $postEntity->parent = $thread;
        $postEntity->parent_id = $thread->id;
        $postEntity->user = $userEntity;
        $postEntity->user_id = $userEntity->id;
        $postEntity->type = self::TYPE_REPLY;
        $postEntity->flag = 0;
        $postEntity->title = $title;
        $postEntity->content_type = $contentType;
        $postEntity->content = $content;
        $postEntity->via = self::VIA_WEB;

        return $postEntity;
    }

    public function attachReplyData(array $replyData)
    {
        if ($this->type !== self::TYPE_THREAD) {
            throw new \Exception(__METHOD__ . '/' . __LINE__);
        }

        $this->child_count++;
        $this->latest_childs[] = $replyData;
        if (count($this->latest_childs) > 3) {
            array_shift($this->latest_childs);
        }

        $this->touch();
    }

    public function touch()
    {
        $this->touched_at = Utility::getNanotime();
    }

    protected static function loadRelation($key, array $data)
    {
        switch ($key) {
            case 'user':
                return new UserEntity($data);
            case 'parent':
                return new PostEntity($data);
            default:
                return parent::loadRelation($key, $data);
        }
    }
}
