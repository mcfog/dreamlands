<?php namespace Dreamlands\Plate;

use Dreamlands\Utility\DViewTrait;
use Lit\Core\JsonView;

class AjaxView extends JsonView
{
    use DViewTrait;
    protected $isError = false;

    public function renderError(string $message, array $detail = [])
    {
        return $this
            ->setIsError(true)
            ->render([
                'message' => $message,
                'detail' => $detail,
            ]);
    }

    public function render(array $data = [])
    {
        return parent::render([
            'isError' => $this->isError,
            'result' => $data,
        ]);
    }

    /**
     *
     * @param bool $isError
     * @return $this
     */
    public function setIsError(bool $isError)
    {
        $this->isError = $isError;

        return $this;
    }
}
