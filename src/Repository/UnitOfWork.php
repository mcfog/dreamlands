<?php namespace Dreamlands\Repository;

use Dreamlands\Entity\UserEntity;
use Dreamlands\Spot\DEntity;
use Spot\Locator;

class UnitOfWork
{
    /**
     * @var \SplObjectStorage
     */
    protected $storage;
    /**
     * @var Locator
     */
    protected $db;

    public function __construct(Locator $db)
    {
        $this->storage = new \SplObjectStorage();
        $this->db = $db;
    }

    public function persist(DEntity $entity)
    {
        $this->storage->attach($entity);
    }

    public function commit()
    {
        if ($this->storage->count() === 0) {
            return false;
        }
        return $this->db->mapper(UserEntity::class)->transaction(function () {
            foreach ($this->storage as $entity) {
                /**
                 * @var DEntity $entity
                 */
                $this->db->mapper(get_class($entity))->save($entity);
            }
            $this->clear();

            return true;
        });
    }

    public function clear()
    {
        $this->storage->removeAll($this->storage);
    }
}
