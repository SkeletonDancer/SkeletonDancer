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

namespace Rollerworks\Tools\SkeletonDancer\Tests\Mocks;

final class ProfileResolverClass
{
    public function resolve($dir)
    {
        static $map = [
            'src/Bundle/MyBundle' => 'bundle',
            'src/Component/MyComponent' => 'library',
        ];

        if (isset($map[$dir])) {
            return $map[$dir];
        }
    }
}
