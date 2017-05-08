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

namespace SkeletonDancer\Test;

use Symfony\Component\Console\Output\StreamOutput;

/**
 * @method static assertRegExp($pattern, $string, $message = '')
 */
trait OutputAssertionTrait
{
    /**
     * @var StreamOutput
     */
    protected $output;

    /**
     * Gets the display returned by the last execution of the command.
     *
     * @return string The display
     */
    protected function getDisplay(): string
    {
        rewind($this->output->getStream());

        $display = stream_get_contents($this->output->getStream());
        $display = str_replace(PHP_EOL, "\n", $display);

        return $display;
    }

    protected function assertOutputMatches($expectedLines, bool $regex = false)
    {
        $output = preg_replace('/\s!\s/', ' ', trim($this->getDisplay()));
        $expectedLines = (array) $expectedLines;

        foreach ($expectedLines as $matchLine) {
            if (is_array($matchLine)) {
                list($line, $lineRegex) = $matchLine;
            } else {
                $line = $matchLine;
                $lineRegex = $regex;
            }

            if (!$lineRegex) {
                $line = preg_replace('#\s+#', '\\s+', preg_quote($line, '#'));
            }

            self::assertRegExp('#'.$line.'#m', $output);
        }
    }

    protected function assertOutputNotMatches($expectedLines, bool $regex = false)
    {
        $output = preg_replace('/\s!\s/', ' ', trim($this->getDisplay()));
        $expectedLines = (array) $expectedLines;

        foreach ($expectedLines as $matchLine) {
            if (is_array($matchLine)) {
                list($line, $lineRegex) = $matchLine;
            } else {
                $line = $matchLine;
                $lineRegex = $regex;
            }

            if (!$lineRegex) {
                $line = preg_replace('#\s+#', '\\s+', preg_quote($line, '#'));
            }

            self::assertNotRegExp('#'.$line.'#m', $output);
        }
    }
}
