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
use SkeletonDancer\Runner\DryRunner;
use SkeletonDancer\Test\OutputAssertionTrait;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DryRunnerTest extends TestCase
{
    use OutputAssertionTrait;

    /**
     * @var ArrayInput
     */
    private $input;

    /** @test */
    public function it_reports_only()
    {
        $style = $this->createStyle();
        $answers = new QuestionsSet(function () {});
        $answers->set('name', 'John');

        $dance = new Dance('dummy/dummy', 'dummy');
        $dance->generators = [
            'SkeletonDancer\Generator\GitInitGenerator',
            'SkeletonDancer\Generator\ReadMeGenerator',
        ];

        $runner = new DryRunner($style);
        $runner->run($dance, $answers);

        $this->assertOutputMatches([
            'Start your dance practice, this wont take long...',
            'Total of tasks: 2',
            'Dry-run operation, no actual files will be generated.',
            '[1/2] Running SkeletonDancer\Generator\GitInitGenerator',
            '[2/2] Running SkeletonDancer\Generator\ReadMeGenerator',
            'Done!',
        ]);
    }

    /** @test */
    public function it_works_with_empty_generator_list()
    {
        $style = $this->createStyle();
        $answers = new QuestionsSet(function () {});
        $answers->set('name', 'John');

        $dance = new Dance('dummy/dummy', 'dummy');

        $runner = new DryRunner($style);
        $runner->run($dance, $answers);

        $this->assertOutputMatches([
            'Start your dance practice, this wont take long...',
            'Total of tasks: 0',
            'Dry-run operation, no actual files will be generated.',
            'Done!',
        ]);
    }

    private function createStyle(): SymfonyStyle
    {
        $this->input = new ArrayInput([]);
        $this->input->setInteractive(true);

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
        $this->output->setDecorated(false);

        return new SymfonyStyle($this->input, $this->output);
    }
}
