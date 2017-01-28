<?php

use Dreamlands\DContainer;
use Dreamlands\Dreamlands;

require __DIR__ . '/../vendor/autoload.php';

try {
    Dreamlands::run(new DContainer());
} catch (\Error $e) {
    //DELETEME
    echo '<xmp>' . PHP_EOL;
    var_dump($e);
    die;
    //DELETEME END
}
