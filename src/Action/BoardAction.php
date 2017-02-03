<?php namespace Dreamlands\Action;

use Dreamlands\DAction;

class BoardAction extends DAction
{
    const PATH = '/b/{id:\d+}';

    protected function run()
    {
        $id = $this->request->getAttribute('id');

        if (!isset($this->container->boards[$id])) {
            return $this->akarin();
        }

        $board = $this->container->boards[$id];

        return $this->plate('board')->render([
            'board' => $board,
            'posts' => $this->repo->getPosts($board)->with('user'),
        ]);
    }
}
