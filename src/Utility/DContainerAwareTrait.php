<?php namespace Dreamlands\Utility;

use Dreamlands\DContainer;

trait DContainerAwareTrait
{
    /**
     * @var DContainer
     */
    protected $container;

    public function __construct(DContainer $container)
    {
        $this->container = $container;
    }
}
