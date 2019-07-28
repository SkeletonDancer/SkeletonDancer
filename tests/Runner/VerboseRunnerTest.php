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

use Dance\Generator\FailingGenerator;
use Dance\Generator\FooGenerator;
use Dance\Generator\SkippingGenerator;
use Dance\Generator\SpiderGenerator;
use PHPUnit\Framework\TestCase;
use SkeletonDancer\Dance;
use SkeletonDancer\QuestionsSet;
use SkeletonDancer\Runner\VerboseRunner;
use SkeletonDancer\Test\ContainerCreator;
use SkeletonDancer\Test\OutputAssertionTrait;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class VerboseRunnerTest extends TestCase
{
    use OutputAssertionTrait;
    use ContainerCreator;

    /**
     * @var ArrayInput
     */
    private $input;

    protected function setUp()
    {
        $this->setUpContainer();
    }

    /** @test */
    public function it_runs_generators()
    {
        $style = $this->createStyle();
        $answers = new QuestionsSet(function () {});
        $answers->set('name', 'John');

        $dance = new Dance('dummy/dummy', 'dummy');
        $dance->generators = [
            FooGenerator::class,
            SpiderGenerator::class,
        ];

        $this->filesystem->dumpFile('who.md', 'We won\'t get foo\'d again.')->shouldBeCalled();
        $this->filesystem->dumpFile('big.md', 'Does whatever')->shouldBeCalled();
        $this->filesystem->dumpFile('web.php', 'Yuck!')->shouldBeCalled();

        $runner = new VerboseRunner($style, $this->container['class_initializer']);
        $runner->run($dance, $answers);

        $this->assertOutputMatches([
            'Start dancing, this may take a while...',
            'Total of tasks: 2',
            '[1/2] Running '.FooGenerator::class.'  OK...',
            '[2/2] Running '.SpiderGenerator::class.'  OK...',
            'Done!',
        ]);
    }

    /** @test */
    public function it_runs_generators_and_reports_skipped_and_failed()
    {
        $style = $this->createStyle();
        $answers = new QuestionsSet(function () {});
        $answers->set('name', 'John');

        $dance = new Dance('dummy/dummy', 'dummy');
        $dance->generators = [
            FooGenerator::class,
            SkippingGenerator::class,
            FailingGenerator::class,
        ];

        $this->filesystem->dumpFile('who.md', 'We won\'t get foo\'d again.')->shouldBeCalled();

        $runner = new VerboseRunner($style, $this->container['class_initializer']);
        $runner->run($dance, $answers);

        $this->assertOutputMatches([
            'Start dancing, this may take a while...',
            'Total of tasks: 3',
            '[1/3] Running '.FooGenerator::class.'  OK...',
            '[2/3] Running '.SkippingGenerator::class.'  SKIPPED...',
            '[3/3] Running '.FailingGenerator::class.'  ERROR...',
            'Done!',
        ]);
    }

    /** @test */
    public function it_runs_generators_and_memory_and_time()
    {
        $style = $this->createStyle(true);
        $answers = new QuestionsSet(function () {});
        $answers->set('name', 'John');

        $dance = new Dance('dummy/dummy', 'dummy');
        $dance->generators = [FooGenerator::class];

        $this->filesystem->dumpFile('who.md', 'We won\'t get foo\'d again.')->shouldBeCalled();

        $runner = new VerboseRunner($style, $this->container['class_initializer']);
        $runner->run($dance, $answers);

        $this->assertOutputMatches([
            'Start dancing, this may take a while...',
            'Total of tasks: 1',
            [preg_quote('[1/1] Running '.FooGenerator::class.'  OK...', '#').' \(\d+ ms\)', true],
            ['// Total time: \d+ ms, Memory: \d+.\d+MB', true],
            'Done!',
        ]);
    }

    private function createStyle(bool $verbose = false): SymfonyStyle
    {
        $this->input = new ArrayInput([]);
        $this->input->setInteractive(true);

        $this->output = new StreamOutput(fopen('php://memory', 'w', false), $verbose ? StreamOutput::VERBOSITY_VERBOSE : null);
        $this->output->setDecorated(false);

        return new SymfonyStyle($this->input, $this->output);
    }
}
