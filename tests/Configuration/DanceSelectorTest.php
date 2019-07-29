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

namespace SkeletonDancer\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use SkeletonDancer\Configuration\DanceSelector;
use SkeletonDancer\Configuration\DancesProvider;
use SkeletonDancer\Dance;
use SkeletonDancer\Dances;
use SkeletonDancer\Test\OutputAssertionTrait;
use SkeletonDancer\Test\SymfonyStyleTestHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\IO\InputStream\NullInputStream;
use Webmozart\Console\IO\OutputStream\NullOutputStream;

final class DanceSelectorTest extends TestCase
{
    use OutputAssertionTrait;

    /**
     * @var IO
     */
    private $io;

    /**
     * @var ArrayInput
     */
    private $input;

    /** @test */
    public function it_accepts_the_given_choice()
    {
        $resolver = $this->createProfileResolver(['1', '1']);

        self::assertEquals('SkeletonDancer/php-std', $resolver->resolve(false)->name);
        self::assertEquals('SkeletonDancer/php-std', $resolver->resolve(true)->name);
    }

    /** @test */
    public function it_asks_when_no_dance_was_provided()
    {
        self::assertEquals('SkeletonDancer/php-std', $this->createProfileResolver(['1'])->resolve(false)->name);

        $this->assertOutputMatches(['[0] SkeletonDancer/empty']);
        $this->assertOutputMatches(['[1] SkeletonDancer/php-std']);
        $this->assertOutputMatches(['[2] _local/bundle']);
        $this->assertOutputMatches(['[3] _local/empty']);
    }

    /** @test */
    public function it_asks_when_no_dance_was_provided_with_local_ignored()
    {
        self::assertEquals('SkeletonDancer/php-std', $this->createProfileResolver(['1'])->resolve(true)->name);
        self::assertEquals('SkeletonDancer/empty', $this->createProfileResolver(['0'])->resolve(true)->name);

        $this->assertOutputMatches(['[0] SkeletonDancer/empty']);
        $this->assertOutputMatches(['[1] SkeletonDancer/php-std']);
        $this->assertOutputNotMatches(['_local/empty']);
        $this->assertOutputNotMatches(['_local/bundle']);
    }

    private function createProfileResolver(array $input, bool $interactive = true): DanceSelector
    {
        $this->io = new IO(
            new Input(new NullInputStream()),
            new Output(new NullOutputStream()),
            new Output(new NullOutputStream())
        );

        $this->io->setInteractive($interactive);

        $d1 = new Dance('SkeletonDancer/php-std', '');
        $d2 = new Dance('SkeletonDancer/empty', '');

        $d3 = new Dance('_local/empty', '');
        $d4 = new Dance('_local/bundle', '');

        $dancesProvider = $this->prophesize(DancesProvider::class);
        $dancesProvider->global()->willReturn(new Dances([
            $d1->name => $d1,
            $d2->name => $d2,
        ]));

        $dancesProvider->local()->willReturn(new Dances([
            $d3->name => $d3,
            $d4->name => $d4,
        ]));

        $dancesProvider->all()->willReturn(new Dances([
            $d1->name => $d1,
            $d2->name => $d2,
            $d3->name => $d3,
            $d4->name => $d4,
        ]));

        return new DanceSelector($dancesProvider->reveal(), $this->createStyle($input, $interactive));
    }

    private function createStyle(array $input = [], bool $interactive = true): SymfonyStyle
    {
        $this->input = new ArrayInput([]);
        $this->input->setStream($this->getInputStream($input));
        $this->input->setInteractive($interactive);

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
        $this->output->setDecorated(false);

        return new SymfonyStyleTestHelper($this->input, $this->output);
    }

    private function getInputStream(array $input)
    {
        $input = implode(PHP_EOL, $input);

        $stream = fopen('php://memory', 'r+', false);
        fwrite($stream, $input);
        rewind($stream);

        return $stream;
    }
}
