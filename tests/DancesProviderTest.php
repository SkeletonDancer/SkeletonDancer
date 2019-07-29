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
use SkeletonDancer\Configuration\DancesProvider;
use SkeletonDancer\Configuration\Loader;
use SkeletonDancer\Test\OutputAssertionTrait;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Exception\IOException;

final class DancesProviderTest extends TestCase
{
    use OutputAssertionTrait;

    /**
     * @var DancesProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->output = new StreamOutput(fopen('php://memory', 'w', false), StreamOutput::VERBOSITY_VERY_VERBOSE);
        $this->output->setDecorated(false);

        $this->provider = new DancesProvider(
            __DIR__.'/Fixtures/LocalDances/somewhere/over/the',
            __DIR__.'/Fixtures/Dances',
            new Loader(),
            new ConsoleLogger($this->output)
        );
    }

    /** @test */
    public function it_throws_exception_when_dances_directory_does_not_exist()
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Directory "/NOT" does not exist.');

        new DancesProvider(
            __DIR__.'/Fixtures/LocalDances/somewhere/over/the',
            '/NOT',
            new Loader(),
            new ConsoleLogger($this->output)
        );
    }

    /** @test */
    public function it_returns_all_avaible_dances()
    {
        self::assertEquals(['_local/empty', 'skeletondancer/empty'], $this->provider->all()->names());
        self::assertEquals(['skeletondancer/empty'], $this->provider->global()->names());
        self::assertEquals(['_local/empty'], $this->provider->local()->names());
    }

    /** @test */
    public function it_returns_all_installed_dances()
    {
        self::assertEquals(
            [
                'skeletondancer/corrupted3',
                'skeletondancer/corrupted4',
                'skeletondancer/empty',
            ],
            $this->provider->installed()->names()
        );
    }

    /** @test */
    public function it_returns_all_installed_dances_without_local()
    {
        $this->provider = new DancesProvider(
            __DIR__,
            __DIR__.'/Fixtures/Dances',
            new Loader(),
            new ConsoleLogger($this->output)
        );

        self::assertEquals(['skeletondancer/empty'], $this->provider->all()->names());
        self::assertEquals(['skeletondancer/empty'], $this->provider->global()->names());
        self::assertEquals([], $this->provider->local()->names());

        $this->assertOutputMatches(['No local ".dances" directory could be found.']);
    }

    /** @test */
    public function it_logs_errors_of_damages_dances()
    {
        self::assertEquals(['_local/empty', 'skeletondancer/empty'], $this->provider->all()->names());

        $localDancesDir = __DIR__.'/Fixtures/LocalDances/.dances';
        $dancesDirectory = __DIR__.'/Fixtures/Dances';

        $this->assertOutputMatches([
            "Found local \".dances\" directory at \"{$localDancesDir}\".",
            "Dance \"skeletondancer/corrupted\" is damaged: Config file \".dance.json\" does not exist in \"{$dancesDirectory}/skeletondancer/corrupted\"",
            "Dance \"skeletondancer/corrupted2\" is damaged: Config file \".dance.json\" does not exist in \"{$dancesDirectory}/skeletondancer/corrupted2\"",
            "Dance \"skeletondancer/corrupted3\" is damaged: Invalid configuration in \"{$dancesDirectory}/skeletondancer/corrupted3/.dance.json\":
* [title] The property title is required
* [description] The property description is required
* [generators] The property generators is required",
            "Dance \"skeletondancer/corrupted4\" is damaged: Parse error on line 2:
...   \"questioners\": [}
---------------------^
Expected one of: 'STRING', 'NUMBER', 'NULL', 'TRUE', 'FALSE', '{', '[', ']' in {$dancesDirectory}/skeletondancer/corrupted4/.dance.json",
            "Dance \"_local/corrupted5\" is damaged: Parse error on line 2:\n
...   \"questioners\": [}\n
---------------------^\n
Expected one of: 'STRING', 'NUMBER', 'NULL', 'TRUE', 'FALSE', '{', '[', ']' in {$localDancesDir}/corrupted5/.dance.json",
            "Dance \"_local/corrupted\" is damaged: Config file \".dance.json\" does not exist in \"{$localDancesDir}/corrupted\"",
            "Dance \"_local/corrupted6\" is damaged: Invalid configuration in \"{$localDancesDir}/corrupted6/.dance.json\":
* [title] The property title is required
* [description] The property description is required
* [generators] The property generators is required",
        ]);
    }
}
