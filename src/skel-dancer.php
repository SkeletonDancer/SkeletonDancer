<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require __DIR__.'/../vendor/autoload.php';

use Rollerworks\Tools\SkeletonDancer\GenerateCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new GenerateCommand());
$application->run();
