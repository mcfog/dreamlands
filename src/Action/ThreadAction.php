<?php namespace Dreamlands\Action;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;

class ThreadAction extends DAction
{
    const PATH = '/t/{id:\d+}';

    protected function run()
    {
        $id = $this->request->getAttribute('id');
        /**
         * @var PostEntity $post
         */
        $post = $this->repo->byId(PostEntity::class, $id);
        if (!$post) {
            return $this->akarin();
        }

        $replies = $this->repo->getPosts($post);

        return $this->plate('thread')->render([
            'board' => $this->container->boards[$post->parent_id],
            'thread' => $post,
        ]);
    }
}
