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
        } catch (\RuntimeException $e) {
            return $this->message($e->getMessage())
                ->mayBack(true)
                ->render()
                ->withStatus(400);
        } catch (\InvalidArgumentException $e) {
            return $this->message('非法的昵称')
                ->mayBack(true)
                ->render()
                ->withStatus(400);
        }
    }
}
