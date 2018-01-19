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
        self::assertDisplayMatches($expectedLines, $regex, $this->getDisplay());
    }

    protected function assertOutputNotMatches($expectedLines, bool $regex = false)
    {
        self::assertDisplayNotMatches($expectedLines, $regex, $this->getDisplay());
    }

    protected static function assertDisplayMatches($expectedLines, bool $regex, string $display): void
    {
        $output = preg_replace('/\s!\s/', ' ', trim($display));
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

    protected static function assertDisplayNotMatches($expectedLines, bool $regex, string $display): void
    {
        $output = preg_replace('/\s!\s/', ' ', trim($display));
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
