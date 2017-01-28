<?php namespace Dreamlands\Action\Etc;

use Dreamlands\DAction;
use Dreamlands\Utility\Inspector;
use Lit\Bolt\BoltContainer;

class UnicornAction extends DAction
{
    const PATH = '/unicorn';
    private $error;

    public function __construct(BoltContainer $container, $error = null)
    {
        parent::__construct($container);
        $this->error = $error;
    }

    protected function main()
    {
        $error = $this->error;
        if ($error instanceof \Throwable) {
            $title = sprintf('%s: %s', get_class($error), $error->getMessage());
            $detail = implode("\n", Inspector::formatTrace($error->getTrace()));
        } elseif($error === null) {
            $title = 'Unicorn!';
            $detail = '';
        } else {
            $title = get_class($error);
            ob_start();
            var_dump($error);
            $detail = ob_get_clean();
        }


        return $this->renderPlate('etc/error', [
            'title' => $title,
            'detail' => $detail,
        ]);
    }
}
