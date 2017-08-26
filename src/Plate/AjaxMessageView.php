<?php namespace Dreamlands\Plate;

use Psr\Http\Message\ResponseInterface;

class AjaxMessageView extends AjaxView implements IMessageView
{
    use MessageViewTrait;

    /**
     * @var string
     */
    protected $message;

    public function __construct(string $message)
    {
        $this->message = $message;
        parent::__construct();
    }

    public function render(array $data = []): ResponseInterface
    {
        return parent::render(
            $data + [
                'isAjaxMessage' => true,
                'actions' => $this->actions,
                'message' => $this->message,
            ]
        );
    }
}
