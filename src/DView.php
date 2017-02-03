<?php namespace Dreamlands;

use Lit\Core\Interfaces\IView;
use Lit\Core\Traits\ViewTrait;
use Zend\Diactoros\Response\InjectContentTypeTrait;

abstract class DView implements IView
{
    use ViewTrait;
    use InjectContentTypeTrait;


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
