<?php namespace Dreamlands\Action;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;

class BoardAction extends DAction
{
    const PATH = '/b/{id:\d+}';

    const PERPAGE = 10;

    protected function run()
    {
        $id = $this->request->getAttribute('id');
        if (!isset($this->container->boards[$id])) {
            return $this->akarin();
        }

        $from = intval(base_convert($this->getQueryParam('[from]', ''), 36, 10));
        $from = $from > 0 ? $from : null;

        $board = $this->container->boards[$id];

        $posts = $this->repo->getPosts($board, $from);
        /** @noinspection PhpParamsInspection */
        $posts = iterator_to_array($posts->fetch(self::PERPAGE));
        /**
         * @var PostEntity[] $posts
         */
        if (!empty($posts)) {
            $lastPost = $posts[count($posts) - 1];
            $remain = $this->repo->getPosts($board, $lastPost->touched_at)->count();
            $next = base_convert($lastPost->touched_at, 10, 36);
        } else {
            $remain = 0;
            $next = '';
        }

        return $this->plate('board')->render([
            'board' => $board,
            'posts' => $posts,
            'remain' => $remain,
            'next' => $next,
            'from' => $from,
        ]);
    }
}
