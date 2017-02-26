<?php namespace Dreamlands\Action;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;

class AjaxGetQuoteAction extends DAction
{
    const PATH = '/api/get-quote';

    protected function run()
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
