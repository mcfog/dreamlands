#!/usr/bin/env php
<?php

use Dreamlands\DContainer;
use Symfony\Component\Finder\Finder;

require __DIR__ .'/../vendor/autoload.php';

$container = new DContainer();
$files = (new Finder())
    ->files()
    ->in(__DIR__.'/../src/Entity')
    ->name('*Entity.php');

foreach($files as $file) {
    /**
     * @var SplFileInfo $file
     */
    $class = sprintf('Dreamlands\\Entity\\%s', substr(str_replace(DIRECTORY_SEPARATOR, '\\', $file->getRelativePathname()), 0, -4));//cut -4 .php

    $container->db->mapper($class)->migrate();
}