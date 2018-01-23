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
use SkeletonDancer\Container;
use SkeletonDancer\Dance;
use SkeletonDancer\Dances;
use SkeletonDancer\Test\OutputAssertionTrait;
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
     * @var Container
     */
    private $container;

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
        $resolver = $this->createProfileResolver(['0']);

        self::assertArrayNotHasKey('dance', $this->container);

        $choice = $resolver->resolve();

        self::assertEquals('SkeletonDancer/php-std', $choice->name);
        self::assertSame($choice, $this->container['dance']);
    }

    /** @test */
    public function it_informs_when_passed_dance_is_not_installed()
    {
        $resolver = $this->createProfileResolver(['0']);

        self::assertArrayNotHasKey('dance', $this->container);

        $choice = $resolver->resolve('foo');

        self::assertEquals('SkeletonDancer/php-std', $choice->name);
        self::assertArrayHasKey('dance', $this->container);
        self::assertSame($choice, $this->container['dance']);

        $this->assertOutputMatches('Dance "foo" is not installed.');
    }

    /** @test */
    public function it_asks_when_no_dance_was_provided()
    {
        $resolver = $this->createProfileResolver(['0']);

        self::assertArrayNotHasKey('dance', $this->container);

        $choice = $resolver->resolve();

        self::assertEquals('SkeletonDancer/php-std', $choice->name);
        self::assertArrayHasKey('dance', $this->container);
        self::assertSame($choice, $this->container['dance']);

        $this->assertOutputMatches(['[0] SkeletonDancer/php-std']);
        $this->assertOutputNotMatches('is not installed.');
    }

    /** @test */
    public function it_informs_when_passed_dance_is_not_installed_none_interactive()
    {
        $resolver = $this->createProfileResolver([], false);

        self::assertArrayNotHasKey('dance', $this->container);

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Dance "foo" is not installed. Installed: SkeletonDancer/php-std, SkeletonDancer/empty');

        $resolver->resolve('foo');
    }

    /** @test */
    public function it_informs_when_no_dance_selected_none_interactive()
    {
        $resolver = $this->createProfileResolver([], false);

        self::assertArrayNotHasKey('dance', $this->container);

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('No Dance selected. Installed: SkeletonDancer/php-std, SkeletonDancer/empty');

        $resolver->resolve(null);
    }

    private function createProfileResolver(array $input, bool $interactive = true): DanceSelector
    {
        $this->io = new IO(
            new Input(new NullInputStream()),
            new Output(new NullOutputStream()),
            new Output(new NullOutputStream())
        );

        $this->io->setInteractive($interactive);

        $dances = $this->prophesize(Dances::class);
        $dances->has('SkeletonDancer/php-std')->willReturn(true);
        $dances->get('SkeletonDancer/php-std')->willReturn($d1 = new Dance('SkeletonDancer/php-std', ''));

        $dances->has('SkeletonDancer/empty')->willReturn(true);
        $dances->get('SkeletonDancer/empty')->willReturn($d2 = new Dance('SkeletonDancer/empty', ''));

        $dances->has('foo')->willReturn(false);

        $dances->all()->willReturn(
            [
                'SkeletonDancer/php-std' => $d1,
                'SkeletonDancer/empty' => $d2,
            ]
        );

        $this->container = new Container();
        $selector = new DanceSelector($dances->reveal(), $this->createStyle($input, $interactive), $this->container);
        $this->container['sf.console_input'] = $this->input;

        return $selector;
    }

    /**
     * @param array $input
     *
     * @return SymfonyStyle
     */
    private function createStyle(array $input = [], bool $interactive = true)
    {
        $this->input = new ArrayInput([]);
        $this->input->setStream($this->getInputStream($input));
        $this->input->setInteractive($interactive);

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
        $this->output->setDecorated(false);

        return new SymfonyStyle($this->input, $this->output);
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
