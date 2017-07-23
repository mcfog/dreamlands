<?php namespace Dreamlands\Action\User;

use Dreamlands\DAction;
use Dreamlands\Exceptions\DException;
use Psr\Http\Message\ResponseInterface;

class SpawnAction extends DAction
{
    const PATH = '/user/spawn';
    const METHOD = 'POST';

    protected function run(): ResponseInterface
    {
        $nickname = $this->getBodyParam('[nickname]');
        try {
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
        } catch (DException $e) {
            return $this->message($e->getMessage())
                ->mayBack(true)
                ->render()
                ->withStatus(400);
        }
    }
}
