<?php namespace Dreamlands\Action;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;

class ThreadAction extends DAction
{
    const PATH = '/t/{id:\d+}';

    protected function run()
    {
        $id = $this->request->getAttribute('id');
        $post = $this->repo->byId(PostEntity::class, $id);
        if (!$post) {
            return $this->akarin();
        }
        return $this->plate('thread')->render([
            'thread' => $post
        ]);
    }
}
