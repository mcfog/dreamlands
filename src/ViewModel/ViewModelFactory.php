<?php namespace Dreamlands\ViewModel;

use Dreamlands\DContainer;

class ViewModelFactory
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

    public function wrap($data): GenericViewModel
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->container->instantiate(GenericViewModel::class, [$data]);
    }
}
