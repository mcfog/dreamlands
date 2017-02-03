<?php namespace Dreamlands\Repository;

use Dreamlands\Entity\PostEntity;
use Dreamlands\Entity\UserEntity;
use Dreamlands\Spot\DEntity;
use Dreamlands\Spot\DMapper;
use Spot\Locator;
use Spot\Query;

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
            $this->unitOfWork = new UnitOfWork($this->db);
        }

        return $this->unitOfWork;
    }

    public function runUnitOfWork(callable $logic)
    {
        return $logic(new UnitOfWork($this->db));
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
                    'parent_id' => 0
                ])
        );
    }

    /**
     * @param PostEntity $parent
     * @return Query|PostEntity[]
     */
    public function getPosts(PostEntity $parent)
    {
        if (!isset(PostEntity::$childTypeMap[$parent->type])) {
            throw new \Exception(__METHOD__ . '/' . __LINE__);
        }
        $type = PostEntity::$childTypeMap[$parent->type];
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->mapper(PostEntity::class)
            ->where([
                'deleted_at' => null,
                'type' => $type,
                'parent_id' => $parent->id
            ]);
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
}
