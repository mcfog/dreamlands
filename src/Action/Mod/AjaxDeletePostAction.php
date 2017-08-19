<?php namespace Dreamlands\Action\Mod;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;
use Psr\Http\Message\ResponseInterface;

class AjaxDeletePostAction extends DAction
{
    const METHOD = 'POST';
    const PATH = '/api/mod/delete-post';

    protected function run(): ResponseInterface
    {
        /**
         * @var PostEntity $post
         */
        $post = $this->repo->byId(PostEntity::class, $this->getQueryParam('[id]'));
        if (!$post || !in_array($post->type, [
//                PostEntity::TYPE_THREAD,
                PostEntity::TYPE_REPLY,
            ])
        ) {
            return $this->akarin();
        }

        $post->deleted_at = time();
        $this->repo->getUnitOfWork()->persist($post);

        return $this
            ->message('Post Removed Successfully')
            ->render();
    }
}
