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
use SkeletonDancer\Dances;
use Symfony\Component\Filesystem\Exception\IOException;
use Webmozart\Console\IO\BufferedIO;

final class DancesTest extends TestCase
{
    /**
     * @var BufferedIO
     */
    private $io;

    /**
     * @var Dances
     */
    private $dances;

    protected function setUp(): void
    {
        $this->io = new BufferedIO();
        $this->dances = new Dances(__DIR__.'/Fixtures/Dances', $this->io, new Loader());
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
        $this->expectExceptionMessage('Directory "/NOT" does not exist.');

        $this->dances = new Dances('/NOT', $this->io, new Loader());
    }

    /** @test */
    public function it_returns_if_a_dance_is_in_installed()
    {
        self::assertFalse($this->dances->has('whoops'));
        self::assertFalse($this->dances->has('dummy'));
        self::assertFalse($this->dances->has('skeletondancer/dummy'));
        self::assertFalse($this->dances->has('skeletondancer/corrupted'));
        self::assertFalse($this->dances->has('skeletondancer/corrupted2'));
        self::assertTrue($this->dances->has('skeletondancer/empty'));
    }

    /** @test */
    public function it_gets_an_installed_dance()
    {
        $dance = new Dance(
            'skeletondancer/empty',
            __DIR__.'/Fixtures/Dances/skeletondancer/empty',
            [],
            ['SkeletonDancer\\Generator\\GitInitGenerator']
        );
        $dance->title = 'Empty SkeletonDancer Dance project';
        $dance->description = 'Empty SkeletonDancer Dance project';

        self::assertEquals($dance, $this->dances->get('skeletondancer/empty'));
    }

    /** @test */
    public function it_returns_all_installed_dances()
    {
        $dancesDirectory = __DIR__.'/Fixtures/Dances';

        $content = <<<ERROR
Dance "skeletondancer/corrupted" is damaged: Missing .git directory in "{$dancesDirectory}/skeletondancer/corrupted".
Dance "skeletondancer/corrupted2" is damaged: Config file ".dance.json" does not exist in "{$dancesDirectory}/skeletondancer/corrupted2".
Dance "skeletondancer/corrupted3" is damaged: Invalid configuration in "{$dancesDirectory}/skeletondancer/corrupted3/.dance.json":
      * [title] The property title is required
      * [description] The property description is required
      * [generators] The property generators is required
Dance "skeletondancer/corrupted4" is damaged: Parse error on line 2:
    ...   "questioners": [}
    ---------------------^
    Expected one of: 'STRING', 'NUMBER', 'NULL', 'TRUE', 'FALSE', '{', '[', ']'
    in {$dancesDirectory}/skeletondancer/corrupted4/.dance.json
ERROR;

        self::assertEquals(
            str_replace("\r", '', $content),
            str_replace("\r", '', trim($this->io->fetchErrors()))
        );

        $dance = new Dance(
            'skeletondancer/empty',
            $dancesDirectory.'/skeletondancer/empty',
            [],
            ['SkeletonDancer\\Generator\\GitInitGenerator']
        );
        $dance->title = 'Empty SkeletonDancer Dance project';
        $dance->description = 'Empty SkeletonDancer Dance project';

        self::assertEquals(['skeletondancer/empty' => $dance], $this->dances->all());
    }
}
