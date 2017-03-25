<?php namespace Dreamlands\Utility;

use Dreamlands\DAction;
use Nimo\AbstractMiddleware;

abstract class AbstractInterceptor extends AbstractMiddleware
{
    /**
     * @var DAction
     */
    protected $action;

    protected function beforeMain()
    {
        parent::beforeMain();

        $this->action = DAction::fromRequest($this->request);
    }
}
