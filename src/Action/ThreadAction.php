<?php namespace Dreamlands\Action;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;

class ThreadAction extends DAction
{
    const PATH = '/t/{id:\d+}';

    const PERPAGE = 50;

    protected function run()
    {
        $id = $this->request->getAttribute('id');
        /**
         * @var PostEntity $thread
         */
        $thread = $this->repo->byId(PostEntity::class, $id);
        if (!$thread || $thread->type !== PostEntity::TYPE_THREAD) {
            return $this->akarin();
        }

        $from = intval(base_convert($this->getQueryParam('[from]', ''), 36, 10));
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
            $next = base_convert($lastPost->touched_at, 10, 36);

            if ($remain > self::PERPAGE) {
                $last = base_convert($this->repo->getLastAnchor($thread, self::PERPAGE), 10, 36);
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
