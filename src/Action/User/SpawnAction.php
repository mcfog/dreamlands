<?php namespace Dreamlands\Action\User;

use Dreamlands\DAction;
use Psr\Http\Message\ResponseInterface;

class SpawnAction extends DAction
{
    const PATH = '/user/spawn';
    const METHOD = 'POST';

    protected function run(): ResponseInterface
    {
        $nickname = $this->getBodyParam('[nickname]');
        $user = $this->currentUser->spawnUser($nickname);

        if ($this->isAjax()) {
            return $this->ajax()->render([
                'hash' => $user->hash,
                'name' => $user->getDisplayName(),
            ]);
        } else {
            return $this
                ->message('你好，' . $user->getDisplayName())
                ->render();
        }
    }
}
