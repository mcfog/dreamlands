<?php namespace Dreamlands\Action\Etc;

use Dreamlands\DAction;
use Psr\Http\Message\ResponseInterface;

class AkarinAction extends DAction
{
    const PATH = '/akarin';

    protected function run(): ResponseInterface
    {
        if ($this->isAjax()) {
            return $this->message('not found')->render();
        }

        return $this->plate('etc/akarin')->render();
    }
}
