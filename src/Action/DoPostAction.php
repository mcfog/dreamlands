<?php namespace Dreamlands\Action;

use Dreamlands\DAction;
use Dreamlands\Entity\PostEntity;
use Dreamlands\Repository\UnitOfWork;

class DoPostAction extends DAction
{
    const METHOD = 'POST';
    const PATH = '/post';

    protected function run()
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
                break;
        }

        return $this
            ->message('xxx')
            ->mayBack()
            ->mayJump('/', 'hhh', true)
            ->render();
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

        $userEntity = $this->currentUser->getUser();
        if (!$userEntity) {
            return $this
                ->message('请先报道')
                ->mayBack(true)
                ->render();
        }
        $post = PostEntity::newThread($userEntity, $board, $title, $content);

        return $this->repo->runUnitOfWork(function (UnitOfWork $unitOfWork) use ($post, $board) {
            $unitOfWork = $this->repo->getUnitOfWork();
            $unitOfWork->persist($post);
            $unitOfWork->commit();

            return $this
                ->message('发布成功')
                ->mayJump('/t/' . $post->id, '查看')
                ->mayJump('/b/' . $board->id, sprintf('返回【%s】', $board->title), true)
                ->render();
        });
    }
}
