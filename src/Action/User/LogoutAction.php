<?php namespace Dreamlands\Action\User;

use Dreamlands\DAction;
use Psr\Http\Message\ResponseInterface;

class LogoutAction extends DAction
{
    const PATH = '/user/logout';
    const METHOD = 'POST';

    protected function run(): ResponseInterface
    {
        $this->currentUser->logout();

        return $this
            ->message('已经注销')
            ->mayRefresh(true)
            ->render();
    }
}
