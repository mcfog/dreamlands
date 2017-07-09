<?php namespace Dreamlands\Utility;

trait DViewTrait
{
    /**
     * @var array
     */
    protected $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

}