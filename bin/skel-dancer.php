<?php

declare(strict_types=1);

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require __DIR__.'/../vendor/autoload.php';

use SkeletonDancer\Cli\DancerApplicationConfig;

\Symfony\Component\Debug\ErrorHandler::register();
\Symfony\Component\Debug\DebugClassLoader::enable();

$cli = new \Webmozart\Console\ConsoleApplication(new DancerApplicationConfig());
$cli->run();
