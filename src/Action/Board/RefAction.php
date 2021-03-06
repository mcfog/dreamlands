<?php namespace Dreamlands\Action\Board;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;
use Dreamlands\Utility\Utility;
use Psr\Http\Message\ResponseInterface;

class RefAction extends DAction
{
    const PATH = '/r/{id:\d+}';

    protected function run(): ResponseInterface
    {
        $id = $this->request->getAttribute('id');
        /**
         * @var PostEntity $post
         */
        $post = $this->repo->byId(PostEntity::class, $id);
        if (!$post) {
            return $this->akarin();
        }

        switch ($post->type) {
            case PostEntity::TYPE_BOARD:
                return $this->redirect('/b/' . $post->id);
            case PostEntity::TYPE_THREAD:
                return $this->redirect('/t/' . $post->id);
            case PostEntity::TYPE_REPLY:
                $from = Utility::base36($this->repo->getAnchor($post, ThreadAction::PERPAGE) - 1);

                return $this->redirect(sprintf('/t/%d?from=%s#post-%08d', $post->parent_id, $from, $post->id));
            default:
                throw new \Exception(__METHOD__ . '/' . __LINE__);
        }

    }
}
