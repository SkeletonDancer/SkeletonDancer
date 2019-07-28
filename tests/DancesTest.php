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
use SkeletonDancer\Dance;
use SkeletonDancer\Dances;

final class DancesTest extends TestCase
{
    /**
     * @var Dances
     */
    private $dances;

    protected function setUp(): void
    {
        $this->dances = new Dances($this->getDances());
    }

    /** @test */
    public function it_gets_an_installed_dance()
    {
        self::assertEquals($this->getDances()['skeletondancer/php-std'], $this->dances->get('skeletondancer/php-std'));
        self::assertEquals($this->getDances()['skeletondancer/empty'], $this->dances->get('skeletondancer/empty'));

        self::assertEquals($this->getDances(), $this->dances->all());
        self::assertEquals(array_keys($this->getDances()), $this->dances->names());
        self::assertCount(\count($this->getDances()), $this->dances);
    }

    /** @test */
    public function it_throws_exception_when_dance_is_not_installed()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Dance "whoops" was not found or installed.');

        $this->dances->get('whoops');
    }

    /** @test */
    public function it_returns_if_a_dance_is_in_installed()
    {
        self::assertFalse($this->dances->has('whoops'));
        self::assertFalse($this->dances->has('dummy'));
        self::assertFalse($this->dances->has('skeletondancer/dummy'));
        self::assertFalse($this->dances->has('skeletondancer/corrupted'));
        self::assertFalse($this->dances->has('skeletondancer/corrupted2'));

        self::assertTrue($this->dances->has('skeletondancer/php-std'));
        self::assertTrue($this->dances->has('skeletondancer/php-std'));
        self::assertTrue($this->dances->has('_local/empty'));
    }

    private function getDances(): array
    {
        $d1 = new Dance(
            'skeletondancer/php-std',
            __DIR__.'/Fixtures/Dances/skeletondancer/php-std',
            [],
            ['SkeletonDancer\\Generator\\GitInitGenerator']
        );
        $d1->title = 'Empty SkeletonDancer Dance project';
        $d1->description = 'Empty SkeletonDancer Dance project';

        $d2 = new Dance(
            'skeletondancer/empty',
            __DIR__.'/Fixtures/Dances/skeletondancer/empty',
            [],
            ['SkeletonDancer\\Generator\\GitInitGenerator']
        );

        $d3 = new Dance('_local/empty', '');
        $d4 = new Dance('_local/bundle', '');

        return [
            $d1->name => $d1,
            $d2->name => $d2,
            $d3->name => $d3,
            $d4->name => $d4,
        ];
    }
}
