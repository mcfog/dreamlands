<?php namespace Dreamlands\Action;

use Dreamlands\DAction;

class IndexAction extends DAction
{
    const PATH = '/';

    protected function main()
    {
        return $this->renderPlate('index');
    }
}
