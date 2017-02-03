<?php namespace Dreamlands\Action;

use Dreamlands\DAction;

class IndexAction extends DAction
{
    const PATH = '/';

    protected function run()
    {
        return $this->plate('index')->render();
    }
}
