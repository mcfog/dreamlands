<?php namespace Dreamlands\Action;

use Dreamlands\DAction;
use Psr\Http\Message\ResponseInterface;

class IndexAction extends DAction
{
    const PATH = '/';

    protected function run(): ResponseInterface
    {
        return $this->plate('index')->render();
    }
}
