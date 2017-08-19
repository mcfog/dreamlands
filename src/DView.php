<?php namespace Dreamlands;

use Dreamlands\Utility\DViewTrait;
use Lit\Core\Interfaces\IView;
use Lit\Core\Traits\ViewTrait;
use Lit\Nexus\Interfaces\IPropertyInjection;
use Zend\Diactoros\Response\InjectContentTypeTrait;

abstract class DView implements IView, IPropertyInjection
{
    use ViewTrait;
    use InjectContentTypeTrait;
    use DViewTrait;

    public static function getInjectedProperties()
    {
        return [];
    }
}
