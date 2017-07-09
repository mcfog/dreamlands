<?php namespace Dreamlands\Action\Etc;

use Dreamlands\DAction;
use Dreamlands\Repository\Repository;
use Dreamlands\Utility\Inspector;
use Lit\Bolt\BoltContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class UnicornAction extends DAction
{
    const PATH = '/unicorn';
    private $error;

    public function __construct(BoltContainer $container, Repository $repo, LoggerInterface $logger, $error = null)
    {
        parent::__construct($container, $repo, $logger);
        $this->error = $error;
    }

    protected function run(): ResponseInterface
    {
        $error = $this->error;
        if ($this->container->envIsProd()) {
            $title = 'ಠ_ಠ';
            $detail = '';
            $this->logger->error('unicorn', [
                'error' => $error
            ]);
        } elseif ($error instanceof \Throwable) {
            $title = sprintf('%s: %s', get_class($error), $error->getMessage());
            $detail = Inspector::formatThrowable($error);
        } else {
            $title = gettype($error);
            if ($title === 'object') {
                $title = get_class($error);
            }
            ob_start();
            var_dump($error);
            $detail = ob_get_clean();
        }


        return $this->plate('etc/error')->render([
            'title' => $title,
            'detail' => $detail,
        ]);
    }

    protected function beforeMain()
    {
        //noop
    }

}
