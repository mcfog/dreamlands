<?php namespace Dreamlands;

use Dreamlands\Middleware\CurrentUserMiddleware;
use Lit\Bolt\BoltApp;
use Lit\Middlewares\FigCookiesMiddleware;

/**
 * Class Dreamlands
 * @package Dreamlands
 *
 * @property DContainer $container
 */
class Dreamlands extends BoltApp
{
    public function __construct(DContainer $container)
    {
        parent::__construct($container);
        $this
            ->prepend($this->container->produce(CurrentUserMiddleware::class))
            ->prepend($this->container->produce(FigCookiesMiddleware::class))
            ->append($container->produce(ErrorHandler::class));
//        //DELETEME
//        echo '<xmp>' . PHP_EOL;
//        var_dump(array_map(function ($m) {
//            return is_object($m) ? get_class($m) : get_class($m[0]);
//        }, $this->stack));
//        die;
//        //DELETEME END


    }
}
