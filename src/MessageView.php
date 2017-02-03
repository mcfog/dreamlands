<?php namespace Dreamlands;

use Dreamlands\Plate\PlateView;
use League\Plates\Engine;

class MessageView extends PlateView
{
    const ACTION_BACK = 1;
    const ACTION_REFRESH = 2;
    const ACTION_JUMP = 3;

    protected $actions = [];

    public function __construct(Engine $plate, $message, array $data = [])
    {
        parent::__construct($plate, 'etc/message', [
                'message' => $message
            ] + $data);
    }

    public function mayBack($isDefault = false)
    {
        if ($isDefault) {
            $this->set('defaultAction', count($this->actions));
        }
        $this->actions[] = ['后退', self::ACTION_BACK];
        return $this;
    }

    public function mayRefresh($isDefault = false)
    {
        if ($isDefault) {
            $this->set('defaultAction', count($this->actions));
        }
        $this->actions[] = ['刷新', self::ACTION_REFRESH];
        return $this;
    }

    public function mayJump($url, $message = '继续', $isDefault = false)
    {
        if ($isDefault) {
            $this->set('defaultAction', count($this->actions));
        }
        $this->actions[] = [$message, self::ACTION_JUMP, $url];
        return $this;
    }

    public function render(array $data = [])
    {
        return parent::render(
            $data + [
                'actions' => $this->actions,
            ]
        );
    }
}
