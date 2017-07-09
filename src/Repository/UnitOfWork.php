<?php namespace Dreamlands\Repository;

use Dreamlands\Entity\UserEntity;
use Dreamlands\Spot\DEntity;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Spot\Locator;

class UnitOfWork
{
    use LoggerAwareTrait;
    /**
     * @var \SplObjectStorage
     */
    protected $storage;
    /**
     * @var Locator
     */
    protected $db;

    public function __construct(Locator $db, LoggerInterface $logger)
    {
        $this->storage = new \SplObjectStorage();
        $this->db = $db;
        $this->setLogger($logger);
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
                $result = $this->db->mapper(get_class($entity))->save($entity);
                if (!$result) {
                    $this->logger->error('db_persist_failed', [
                        'result' => $result,
                        'entity' => $entity,
                    ]);
                    throw new \RuntimeException('db persist failed');
                }
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
