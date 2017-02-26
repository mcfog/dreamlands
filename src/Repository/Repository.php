<?php namespace Dreamlands\Repository;

use Dreamlands\Entity\PostEntity;
use Dreamlands\Entity\UserEntity;
use Dreamlands\Spot\DEntity;
use Dreamlands\Spot\DMapper;
use Spot\Locator;

class Repository
{
    /**
     * @var Locator
     */
    protected $db;
    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    public function __construct(Locator $db)
    {
        $this->db = $db;
    }

    /**
     * @return UnitOfWork
     */
    public function getUnitOfWork()
    {
        if (!isset($this->unitOfWork)) {
            $this->unitOfWork = $this->makeUnitOfWork();
        }

        return $this->unitOfWork;
    }

    public function runUnitOfWork(callable $logic)
    {
        return $logic($this->makeUnitOfWork());
    }

    /**
     * @param $hash
     * @return UserEntity
     */
    public function getUserByHash($hash)
    {
        return $this->mapper(UserEntity::class)
            ->where([
                'hash' => $hash,
                'expire_at >' => time(),
            ])
            ->first() ?: null;
    }

    /**
     * @param $class
     * @return DMapper
     */
    protected function mapper($class)
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->db->mapper($class);
    }

    /**
     * @return PostEntity[]
     */
    public function getBoardsArray()
    {
        return iterator_to_array(
            $this->mapper(PostEntity::class)
                ->where([
                    'deleted_at' => null,
                    'type' => PostEntity::TYPE_BOARD,
                ])
        );
    }

    /**
     * @param PostEntity $parent
     * @return DList
     * @throws \Exception
     */
    public function getPosts(PostEntity $parent, $from = null, $isDesc = true)
    {
        if (!isset(PostEntity::$childTypeMap[$parent->type])) {
            throw new \Exception(__METHOD__ . '/' . __LINE__);
        }
        $type = PostEntity::$childTypeMap[$parent->type];
        $conditions = [
            'deleted_at' => null,
            'type' => $type,
            'parent_id' => $parent->id
        ];
        if (!is_null($from)) {
            $conditions += [
                ($isDesc ? 'touched_at <' : 'touched_at >') => $from,
            ];
        }
        $query = $this->mapper(PostEntity::class)
            ->where($conditions)
            ->order(['touched_at' => ($isDesc ? 'DESC' : 'ASC')]);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return new DList($query->with('user'));
    }

    public function getLastAnchor(PostEntity $thread, $perpage)
    {
        if ($thread->type !== PostEntity::TYPE_THREAD) {
            throw new \Exception(__METHOD__ . '/' . __LINE__);
        }
        $offset = max(0, intval($thread->child_count / $perpage) * $perpage - 1);
        $query = $this->mapper(PostEntity::class)
            ->where([
                'deleted_at' => null,
                'type' => PostEntity::TYPE_REPLY,
                'parent_id' => $thread->id,
            ])
            ->order(['touched_at' => 'ASC'])
            ->offset($offset);

        return $query->first()->touched_at;
    }

    public function getAnchor(PostEntity $reply, $perpage)
    {
        if ($reply->type !== PostEntity::TYPE_REPLY) {
            throw new \Exception(__METHOD__ . '/' . __LINE__);
        }
        $mapper = $this->mapper(PostEntity::class);
        $count = $mapper
            ->where([
                'deleted_at' => null,
                'type' => PostEntity::TYPE_REPLY,
                'parent_id' => $reply->parent_id,
                'touched_at <' => $reply->touched_at,
            ])
            ->order(['touched_at' => 'ASC'])
            ->count();
        $offset = max(0, intval($count / $perpage) * $perpage - 1);
        $query = $mapper
            ->where([
                'deleted_at' => null,
                'type' => PostEntity::TYPE_REPLY,
                'parent_id' => $reply->parent_id,
            ])
            ->order(['touched_at' => 'ASC'])
            ->offset($offset);

        return $query->first()->touched_at;
    }

    /**
     * @param $class
     * @param $id
     * @return DEntity|null
     */
    public function byId($class, $id)
    {
        return $this->mapper($class)->get($id) ?: null;
    }

    public function doReply(PostEntity $thread, PostEntity $reply)
    {
        $unitOfWork = $this->makeUnitOfWork();
        $unitOfWork->persist($reply);
        $data = $reply->toArray();
        $unitOfWork->commit();

        $data['id'] = $reply->id;
        $thread->attachReplyData($data);
        $unitOfWork->persist($thread);
        $unitOfWork->commit();
    }

    /**
     * @return UnitOfWork
     */
    protected function makeUnitOfWork()
    {
        return new UnitOfWork($this->db);
    }
}
