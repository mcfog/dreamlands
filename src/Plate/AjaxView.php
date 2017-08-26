<?php namespace Dreamlands\Plate;

use Dreamlands\DView;
use Dreamlands\ViewModel\ViewModelFactory;

class AjaxView extends DView
{
    protected $isError = false;

    /**
     * @var ViewModelFactory
     */
    protected $wrapper;

    public static function getInjectedProperties()
    {
        return parent::getInjectedProperties() + [
                'wrapper' => ViewModelFactory::class
            ];
    }


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
        $data = [
            'isError' => $this->isError,
            'result' => $data,
        ];

        $this->getEmptyBody()->write($this->wrapper->convertToJson($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $this->response
            ->withHeader('Content-Type', 'application/json');
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
