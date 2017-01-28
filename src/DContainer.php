<?php namespace Dreamlands;

use Dreamlands\Action\Etc\AkarinAction;
use Dreamlands\Plate\IdenticonExt;
use Dreamlands\Repository\Repository;
use Dreamlands\Utility\Inspector;
use League\Plates\Engine;
use Lit\Bolt\BoltContainer;
use Lit\Bolt\BoltRouteDefinition;
use Lit\Bolt\BoltRouter;
use Lit\Nexus\Cache\CacheKeyValue;
use Spot\Config;
use Spot\Locator;
use Stash\Driver\FileSystem;
use Stash\Pool;

/**
 * Class DContainer
 * @package Dreamlands
 *
 * @property CacheKeyValue $localCache
 * @property Locator $db
 * @property Repository $repo
 */
class DContainer extends BoltContainer
{
    public function __construct(array $values = [])
    {
        set_error_handler([Inspector::class, 'errorHandler']);
        set_exception_handler([Inspector::class, 'exceptionHandler']);

        $this[BoltRouter::class . '::'] = [
            '_cache' => function () {
                return $this->localCache->slice('route');
            },
            'notFound' => AkarinAction::class,
        ];
        $this['config'] = require(__DIR__ . '/../config.php');

        parent::__construct($values);

        $this
            ->alias(DRouteDefinition::class, BoltRouteDefinition::class)
            ->alias(CacheKeyValue::class, 'localCache',
                [
                    function () {
                        $pool = new Pool($this->produce(FileSystem::class));
                        $this->events->addListener(Dreamlands::EVENT_AFTER_LOGIC, function () use ($pool) {
                            foreach ($this->localCache->getDirtyItems() as $dirtyItem) {
                                $pool->saveDeferred($dirtyItem);
                            }
                            $pool->commit();
                        });
                        return $pool;
                    },
                    86400
                ])
            ->alias(Locator::class, 'db')
            ->alias(Repository::class, 'repo');

        $this[Config::class] = function () {
            $config = new Config();
            call_user_func_array([$config, 'addConnection'], $this->config('[db]'));

            $this->events->addListener(Dreamlands::EVENT_AFTER_LOGIC, function () {
                $this->repo->getUnitOfWork()->commit();
            });

            return $config;
        };
        
        $this[Engine::class] = function () {
            $engine = new Engine(__DIR__ . '/../templates', 'phtml');
            /** @noinspection PhpParamsInspection */
            $engine->loadExtension($this->produce(IdenticonExt::class));
            return $engine;
        };
    }

    public function config($key, $default = null)
    {
        return $this->get($this->offsetGet('config'), $key, $default);
    }
}
