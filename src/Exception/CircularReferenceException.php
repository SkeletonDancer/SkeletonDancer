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

namespace Rollerworks\Tools\SkeletonDancer\Exception;

final class CircularReferenceException extends \RuntimeException
{
    public function __construct(array $classes)
    {
        $msg = 'Classes "%s" have produced a CircularReferenceException. ';
        $msg .= 'An example of this problem would be the following: Class C has class B as its dependency. ';
        $msg .= 'Then, class B has class A has its dependency. Finally, class A has class C as its dependency. ';
        $msg .= 'This case would produce a CircularReferenceException.';

        throw new self(sprintf($msg, implode(',', $classes)));
    }
}
