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

namespace SkeletonDancer\Tests;

use PHPUnit\Framework\TestCase;
use SkeletonDancer\Configuration\Loader;
use SkeletonDancer\Dance;
use SkeletonDancer\LocalDances;
use SkeletonDancer\Test\OutputAssertionTrait;
use Symfony\Component\Filesystem\Exception\IOException;
use Webmozart\Console\IO\BufferedIO;

final class LocalDancesTest extends TestCase
{
    use OutputAssertionTrait;

    /**
     * @var BufferedIO
     */
    private $io;

    /**
     * @var LocalDances
     */
    private $dances;

    protected function setUp(): void
    {
        $this->io = new BufferedIO();
        $this->dances = new LocalDances(__DIR__.'/Fixtures/LocalDances/somewhere/over/the', $this->io, new Loader());
    }

    /** @test */
    public function it_throws_exception_when_dance_is_not_installed()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Dance "whoops" is not installed.');

        $this->dances->get('whoops');
    }

    /** @test */
    public function it_throws_exception_when_dances_directory_does_not_exist()
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('No local ".dances" directory could be found.');

        $this->dances = new LocalDances('/NOT', $this->io, new Loader());
    }

    /** @test */
    public function it_returns_if_a_dance_is_in_installed()
    {
        self::assertFalse($this->dances->has('whoops'));
        self::assertFalse($this->dances->has('dummy'));
        self::assertFalse($this->dances->has('skeletondancer/dummy'));
        self::assertFalse($this->dances->has('skeletondancer/corrupted'));
        self::assertFalse($this->dances->has('corrupted2'));
        self::assertTrue($this->dances->has('empty'));
    }

    /** @test */
    public function it_returns_all_installed_local_dances()
    {
        $dancesDirectory = __DIR__.'/Fixtures/LocalDances/.dances';

        $content = [
            <<<TAG
Dance "corrupted" is damaged: Config file ".dance.json" does not exist in "{$dancesDirectory}/corrupted.dance"
TAG
,
            <<<TAG
Dance "corrupted6" is damaged: Invalid configuration in "{$dancesDirectory}/corrupted6.dance/.dance.json":
      * [title] The property title is required
      * [description] The property description is required
      * [generators] The property generators is required
TAG
            ,
            <<<TAG
Dance "corrupted5" is damaged: Parse error on line 2:
    ...   "questioners": [}
    ---------------------^
    Expected one of: 'STRING', 'NUMBER', 'NULL', 'TRUE', 'FALSE', '{', '[', ']'
    in {$dancesDirectory}/corrupted5.dance/.dance.json
TAG
        ];

        self::assertDisplayMatches($content, false, $this->io->fetchErrors());

        $dance = new Dance(
            'empty',
            $dancesDirectory.'/empty.dance',
            [],
            ['SkeletonDancer\\Generator\\GitInitGenerator']
        );
        $dance->title = 'Empty SkeletonDancer Dance project';
        $dance->description = 'Empty SkeletonDancer Dance project';

        self::assertEquals(['empty' => $dance], $this->dances->all());
    }
}
