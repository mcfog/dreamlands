<?php namespace Dreamlands\Action\Board;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;
use Psr\Http\Message\ResponseInterface;

class AjaxGetQuoteAction extends DAction
{
    const PATH = '/api/get-quote';

    protected function run(): ResponseInterface
    {
        /**
         * @var PostEntity $post
         */
        $post = $this->repo->byId(PostEntity::class, $this->getQueryParam('[id]'));
        if (!$post || !in_array($post->type, [
                PostEntity::TYPE_THREAD,
                PostEntity::TYPE_REPLY,
            ])
        ) {
            return $this->akarin();
        }

        return $this->plate('partial/post')->render([
            'post' => $post,
        ]);
    }
}
