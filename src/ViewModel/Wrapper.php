<?php namespace Dreamlands\ViewModel;

use Dreamlands\DContainer;

class Wrapper
{
    /**
     * @var DContainer
     */
    private $container;

    /**
     * Wrapper constructor.
     */
    public function __construct(DContainer $container)
    {
        $this->container = $container;
    }

    public function convertToJson($data, $option = 0)
    {
        return $this->wrap($data)->toJson($option);
    }

    public function wrap($data)
    {
        return $this->container->instantiate(ViewModel::class, [$data]);
    }
}
