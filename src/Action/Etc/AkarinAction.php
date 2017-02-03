<?php namespace Dreamlands\Action\Etc;

use Dreamlands\DAction;

class AkarinAction extends DAction
{
    const PATH = '/akarin';

    protected function run()
    {
        return $this->plate('etc/akarin')->render();
    }
}
