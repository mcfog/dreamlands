<?php namespace Dreamlands\Action\Board;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;
use Dreamlands\Utility\Utility;
use Psr\Http\Message\ResponseInterface;

class BoardAction extends DAction
{
    const PATH = '/b/{id:\d+}';

    const PERPAGE = 10;

    protected function run(): ResponseInterface
    {
        $id = $this->request->getAttribute('id');
        if (!isset($this->container->boards[$id])) {
            return $this->akarin();
        }

        $from = Utility::base36_decode($this->getQueryParam('[from]', ''));
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
            $next = Utility::base36($lastPost->touched_at);
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
