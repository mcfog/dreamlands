<?php namespace Dreamlands\Action\Board;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;
use Psr\Http\Message\ResponseInterface;

class AjaxPostBoxAction extends DAction
{
    const METHOD = 'POST';
    const PATH = '/api/post-box';

    protected function run(): ResponseInterface
    {
        $id = $this->getBodyParam('[id]');
        if (!isset($this->container->boards[$id])) {
            $post = $this->repo->byId(PostEntity::class, $id);
            if (!$post || $post->type !== PostEntity::TYPE_THREAD) {
                return $this->akarin();
            }
        } else {
            $post = $this->container->boards[$id];
        }

        return $this->plate('partial/postbox')->render([
            'parent' => $post,
        ]);
    }
}
