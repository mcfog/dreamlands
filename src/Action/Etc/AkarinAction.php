<?php namespace Dreamlands\Action\Etc;

use Dreamlands\DAction;

class AkarinAction extends DAction
{
    const PATH = '/akarin';

    protected function main()
    {
        $this->currentUser->spawnUser();

        return $this->renderPlate('etc/akarin');
    }
}
