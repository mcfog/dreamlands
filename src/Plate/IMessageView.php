<?php

namespace Dreamlands\Plate;

use Psr\Http\Message\ResponseInterface;

interface IMessageView
{
    const ACTION_BACK = 1;
    const ACTION_REFRESH = 2;
    const ACTION_JUMP = 3;

    public function mayBack($isDefault = false): IMessageView;

    public function mayRefresh($isDefault = false): IMessageView;

    public function mayJump($url, $mxoessage = '继续', $isDefault = false): IMessageView;

    public function render(array $data = []): ResponseInterface;
}