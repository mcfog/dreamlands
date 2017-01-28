<?php namespace Dreamlands\Repository;

use Dreamlands\Entity\UserEntity;
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
        if(!isset($this->unitOfWork)) {
            $this->unitOfWork = new UnitOfWork($this->db);
        }

        return $this->unitOfWork;
    }

    protected function mapper($class)
    {
        return $this->db->mapper($class);
    }

    /**
     * @param $hash
     * @return UserEntity
     */
    public function getUserByHash($hash)
    {
        return $this->mapper(UserEntity::class)
            ->where(['hash' => $hash])
            ->first() ?: null;
    }
}
