<?php namespace Dreamlands\Action\Board;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;
use Dreamlands\Utility\Utility;
use Psr\Http\Message\ResponseInterface;

class ThreadAction extends DAction
{
    const PATH = '/t/{id:\d+}';

    const PERPAGE = 50;

    protected function run(): ResponseInterface
    {
        $id = $this->request->getAttribute('id');
        /**
         * @var PostEntity $thread
         */
        $thread = $this->repo->byId(PostEntity::class, $id);
        if (!$thread || $thread->type !== PostEntity::TYPE_THREAD) {
            return $this->akarin();
        }

        switch ($this->getQueryParam('[jump]', '')) {
            case 'latest':
                if ($thread->child_count <= self::PERPAGE) {
                    return $this->redirect(sprintf('/t/%d', $id));
                }
                return $this->redirect(sprintf('/t/%d?from=%s',
                    $id,
                    Utility::base36($this->repo->getLastAnchor($thread, self::PERPAGE))
                ));
            default:
                //noop
        }

        $from = Utility::base36_decode($this->getQueryParam('[from]', ''));
        $from = $from > 0 ? $from : null;

        $posts = $this->repo->getPosts($thread, $from, false);
        /** @noinspection PhpParamsInspection */
        $posts = iterator_to_array($posts->fetch(self::PERPAGE));
        /**
         * @var PostEntity[] $posts
         */
        $last = '';
        if (!empty($posts)) {
            $lastPost = $posts[count($posts) - 1];
            $remain = $this->repo->getPosts($thread, $lastPost->touched_at, false)->count();
            $next = Utility::base36($lastPost->touched_at);

            if ($remain > self::PERPAGE) {
                $last = Utility::base36($this->repo->getLastAnchor($thread, self::PERPAGE));
            } elseif ($remain > 0) {
                $last = $next;
            }
        } else {
            $remain = 0;
            $next = '';
        }

        return $this->plate('thread')->render([
            'board' => $this->container->boards[$thread->parent_id],
            'thread' => $thread,
            'posts' => $posts,
            'remain' => $remain,
            'next' => $next,
            'last' => $last,
            'from' => $from,
        ]);
    }
}
