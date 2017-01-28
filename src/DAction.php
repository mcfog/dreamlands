<?php namespace Dreamlands;

use Dreamlands\Middleware\CurrentUserMiddleware;
use Dreamlands\Plate\PlateView;
use Lit\Bolt\BoltAction;
use Lit\Middlewares\FigCookiesMiddleware;

/**
 * Class DAction
 * @package Dreamlands
 */
abstract class DAction extends BoltAction
{
    const METHOD = 'GET';
    const PATH = '/';

    /**
     * @var FigCookiesMiddleware
     */
    protected $cookie;

    /**
     * @var CurrentUserMiddleware
     */
    protected $currentUser;

    public function renderPlate(string $name, array $data = [])
    {
        /** @noinspection PhpParamsInspection */
        return $this->renderView($this->container->instantiate(PlateView::class, [
            'name' => $name,
        ]), $data + [
            'currentUser' => $this->currentUser,
        ]);
    }

    protected function beforeMain()
    {
        parent::beforeMain();

        $this->cookie = FigCookiesMiddleware::fromRequest($this->request);
        $this->currentUser = CurrentUserMiddleware::fromRequest($this->request);
    }
}
