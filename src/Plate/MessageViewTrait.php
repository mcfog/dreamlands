<?php namespace Dreamlands\Plate;

use Psr\Http\Message\ResponseInterface;

/**
 * Trait MessageViewTrait
 * @package Dreamlands\Plate
 */
trait MessageViewTrait
{
    protected $actions = [];

    public function mayBack($isDefault = false): IMessageView
    {
        /**
         * @var IMessageView $this
         */
        if ($isDefault) {
            $this->set('defaultAction', count($this->actions));
        }
        $this->actions[] = ['后退', IMessageView::ACTION_BACK];

        return $this;
    }

    public function mayRefresh($isDefault = false): IMessageView
    {
        /**
         * @var IMessageView $this
         */
        if ($isDefault) {
            $this->set('defaultAction', count($this->actions));
        }
        $this->actions[] = ['刷新', IMessageView::ACTION_REFRESH];

        return $this;
    }

    public function mayJump($url, $message = '继续', $isDefault = false): IMessageView
    {
        /**
         * @var IMessageView $this
         */
        if ($isDefault) {
            $this->set('defaultAction', count($this->actions));
        }
        $this->actions[] = [$message, IMessageView::ACTION_JUMP, $url];

        return $this;
    }

    public function render(array $data = []): ResponseInterface
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return parent::render(
            $data + [
                'actions' => $this->actions,
            ]
        );
    }
}
