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

// Git refuses to commit .git directories (even if they are not actual Git repositories).
// But the tests expect the .git directory to exist.

@mkdir(__DIR__.'/Fixtures/Dances/skeletondancer/corrupted2/.git');
@mkdir(__DIR__.'/Fixtures/Dances/skeletondancer/corrupted2/.git');
@mkdir(__DIR__.'/Fixtures/Dances/skeletondancer/corrupted3/.git');
@mkdir(__DIR__.'/Fixtures/Dances/skeletondancer/corrupted4/.git');
@mkdir(__DIR__.'/Fixtures/Dances/skeletondancer/empty/.git');
