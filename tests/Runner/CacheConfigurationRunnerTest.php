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

namespace SkeletonDancer\Tests\Runner;

use PHPUnit\Framework\TestCase;
use SkeletonDancer\Dance;
use SkeletonDancer\QuestionsSet;
use SkeletonDancer\Runner;
use SkeletonDancer\Runner\CacheConfigurationRunner;
use SkeletonDancer\Service\Filesystem;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CacheConfigurationRunnerTest extends TestCase
{
    /**
     * @var ArrayInput
     */
    private $input;

    /**
     * @var StreamOutput
     */
    private $output;

    protected function setUp()
    {
        @unlink(sys_get_temp_dir().'/'.CacheConfigurationRunner::CACHE_FILENAME);

        self::assertFileNotExists(sys_get_temp_dir().'/'.CacheConfigurationRunner::CACHE_FILENAME);
    }

    /** @test */
    public function it_caches_provided_answers_in_case_of_an_exception()
    {
        $dance = new Dance('dummy/dummy', 'dummy');

        $style = $this->createStyle();
        $filesystem = $this->createFilesystem();
        $questionsSet = new QuestionsSet(function () {});
        $questionsSet->set('name', 'John');

        $runner = $this->prophesize(Runner::class);
        $runner->run($dance, $questionsSet)->willThrow($ex = new \InvalidArgumentException('I cannot let you do this John.'));

        $cachedRunner = new CacheConfigurationRunner($style, $filesystem, $runner->reveal());

        try {
            $cachedRunner->run($dance, $questionsSet);
        } catch (\Exception $e) {
            self::assertSame($ex, $e);
        }

        self::assertJsonStringEqualsJsonFile(sys_get_temp_dir().'/'.CacheConfigurationRunner::CACHE_FILENAME, '{"name": "John"}');
    }

    /** @test */
    public function it_caches_nothing_when_no_exception_was_thrown()
    {
        $dance = new Dance('dummy/dummy', 'dummy');

        $style = $this->createStyle();
        $filesystem = $this->createFilesystem();
        $questionsSet = new QuestionsSet(function () {});

        $runner = $this->prophesize(Runner::class);
        $runner->run($dance, $questionsSet)->shouldBeCalled();

        $cachedRunner = new CacheConfigurationRunner($style, $filesystem, $runner->reveal());
        $cachedRunner->run($dance, $questionsSet);

        self::assertFileNotExists(sys_get_temp_dir().'/'.CacheConfigurationRunner::CACHE_FILENAME);
    }

    private function createStyle(): SymfonyStyle
    {
        $this->input = new ArrayInput([]);
        $this->input->setInteractive(true);

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
        $this->output->setDecorated(false);

        return new SymfonyStyle($this->input, $this->output);
    }

    private function createFilesystem(): Filesystem
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects(self::any())->method('getCurrentDir')->willReturn(sys_get_temp_dir());

        return $filesystem;
    }
}
