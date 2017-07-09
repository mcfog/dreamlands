<?php namespace Dreamlands\Plate;

use League\Plates\Engine;

class MessageView extends PlateView implements IMessageView
{
    use MessageViewTrait;

    public function __construct(Engine $plate, $message, array $data = [])
    {
        parent::__construct($plate, 'etc/message', [
                'message' => $message
            ] + $data);
    }
}
