<?php namespace Dreamlands\Repository;

use Spot\Query;

class DList
{
    /**
     * @var Query
     */
    protected $query;
    protected $count;
    protected $pageSize = 20;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function count()
    {
        if (!isset($this->count)) {
            $this->count = $this->query->count();
        }
        return $this->count;
    }

    public function fetchPage($pageNo)
    {
        $offset = ($pageNo - 1) * $this->pageSize;

        return $this->fetch($this->pageSize, $offset);
    }

    public function fetch($limit, $offset = 0)
    {
        return $this->query->limit($limit, $offset);
    }

    /**
     *
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }
}
