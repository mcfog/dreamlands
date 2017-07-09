<?php namespace Dreamlands\Action\Board;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;
use Dreamlands\Repository\UnitOfWork;
use Psr\Http\Message\ResponseInterface;

class DoPostAction extends DAction
{
    const METHOD = 'POST';
    const PATH = '/post';

    protected function run(): ResponseInterface
    {
        $parentId = intval($this->getBodyParam('[parent]'));

        /**
         * @var PostEntity $parent
         */
        $parent = $this->repo->byId(PostEntity::class, $parentId);
        if (!$parent || $parent->type == PostEntity::TYPE_REPLY) {
            throw new \Exception('invalid parent');
        }

        switch ($parent->type) {
            case PostEntity::TYPE_BOARD:
                return $this->postThread($parent);
            case PostEntity::TYPE_THREAD:
                return $this->postReply($parent);
            default:
                throw new \Exception(__METHOD__ . '/' . __LINE__);
        }
    }

    protected function postThread(PostEntity $board)
    {
        $title = $this->getBodyParam('[title]');
        $content = $this->getBodyParam('[content]');

        if (empty($title) && empty($content)) {
            return $this
                ->message('请输入内容和标题')
                ->mayBack(true)
                ->render();
        }

        $userEntity = $this->getAuthedUser();
        $post = PostEntity::newThread($userEntity, $board, $title, $content);

        return $this->repo->runUnitOfWork(function (UnitOfWork $unitOfWork) use ($post, $board) {
            $unitOfWork->persist($post);
            $unitOfWork->commit();

            return $this
                ->message('发布成功')
                ->mayJump('/t/' . $post->id, '查看')
                ->mayJump('/b/' . $board->id, sprintf('返回【%s】', $board->title), true)
                ->render();
        });
    }


    protected function postReply(PostEntity $thread)
    {
        $title = $this->getBodyParam('[title]');
        $content = $this->getBodyParam('[content]');

        if (empty($title) && empty($content)) {
            return $this
                ->message('请输入内容和标题')
                ->mayBack(true)
                ->render();
        }

        $userEntity = $this->getAuthedUser();
        $reply = PostEntity::newReply($userEntity, $thread, $title, $content);

        $this->repo->doReply($thread, $reply);
        $board = $this->container->boards[$thread->parent_id];
        return $this
            ->message('回复成功')
            ->mayJump('/t/' . $thread->id, '返回话题', true)
            ->mayJump('/b/' . $board->id, sprintf('返回【%s】', $board->title), true)
            ->render();
    }
}
