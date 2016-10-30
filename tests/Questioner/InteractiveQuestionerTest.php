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

namespace Rollerworks\Tools\SkeletonDancer\Tests\Questioner;

use Prophecy\Argument;
use Rollerworks\Tools\SkeletonDancer\AnswersSet;
use Rollerworks\Tools\SkeletonDancer\Configurator;
use Rollerworks\Tools\SkeletonDancer\Question;
use Rollerworks\Tools\SkeletonDancer\Questioner\InteractiveQuestioner;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class InteractiveQuestionerTest extends \PHPUnit_Framework_TestCase
{
    private $input;
    private $output;

    /**
     * @test
     */
    public function it_iterates_all_configurators()
    {
        $questioner = $this->createQuestioner(['Dancer', 'Rollerworks\Something']);

        $configurators = [
            $this->createConfigurator(
                function ($args) {
                    /** @var QuestionsSet $builder */
                    $builder = $args[0];

                    $builder->communicate('name', Question::ask('Name')->setMaxAttempts(1));
                }
            ),
            $this->createConfigurator(
                function ($args) {
                    /** @var QuestionsSet $builder */
                    $builder = $args[0];

                    $builder->communicate('namespace', Question::ask('Namespace')->setMaxAttempts(1));
                }
            ),
        ];

        $answers = $questioner->interact($configurators)->getValues();

        $this->assertSame(['name' => 'Dancer', 'namespace' => 'Rollerworks\Something'], $answers);
    }

    /**
     * @test
     */
    public function it_skips_optional_questions()
    {
        $questioner = $this->createQuestioner(['Dancer', '']);

        $configurators = [
            $this->createConfigurator(
                function ($args) {
                    /** @var QuestionsSet $builder */
                    $builder = $args[0];

                    $builder->communicate('name', Question::ask('Name')->setMaxAttempts(1));
                    $builder->communicate('path', Question::ask('Path', '/')->markOptional()->setMaxAttempts(1));
                }
            ),
        ];

        $answers = $questioner->interact($configurators)->getValues();

        $this->assertSame(['name' => 'Dancer', 'path' => '/'], $answers);
    }

    /**
     * @test
     */
    public function it_asks_optional_questions_when_needed()
    {
        $questioner = $this->createQuestioner(['Dancer', 'src/']);

        $configurators = [
            $this->createConfigurator(
                function ($args) {
                    /** @var QuestionsSet $builder */
                    $builder = $args[0];

                    $builder->communicate('name', Question::ask('Name')->setMaxAttempts(1));
                    $builder->communicate('path', Question::ask('Path', '/')->markOptional()->setMaxAttempts(1));
                }
            ),
        ];

        $answers = $questioner->interact($configurators, false)->getValues();

        $this->assertSame(['name' => 'Dancer', 'path' => 'src/'], $answers);
    }

    /**
     * @test
     */
    public function it_provides_defaults_for_the_user()
    {
        // NB. "\n" means confirm as-is (the current default value).
        $questioner = $this->createQuestioner(['Dancer', "\n"]);

        $configurators = [
            $this->createConfigurator(
                function ($args) {
                    /** @var QuestionsSet $builder */
                    $builder = $args[0];

                    $builder->communicate('name', Question::ask('Name')->setMaxAttempts(1));
                    $builder->communicate('path', Question::ask('Path', 'src/')->setMaxAttempts(1));
                }
            ),
        ];

        $answers = $questioner->interact($configurators, true, ['path' => 'src/'])->getValues();

        $this->assertSame(['name' => 'Dancer', 'path' => 'src/'], $answers);
    }

    private function createConfigurator(\Closure $func)
    {
        $gen = $this->prophesize(Configurator::class);
        $gen->interact(Argument::any())->will($func);

        return $gen->reveal();
    }

    /**
     * @param array $input
     *
     * @return SymfonyStyle
     */
    private function createStyle(array $input)
    {
        $this->input = new ArrayInput([]);
        $this->input->setStream($this->getInputStream($input));
        $this->input->setInteractive(true);

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
        $this->output->setDecorated(false);

        return new SymfonyStyle($this->input, $this->output);
    }

    private function getInputStream($input)
    {
        $input = implode(PHP_EOL, $input);

        $stream = fopen('php://memory', 'r+', false);
        fwrite($stream, $input);
        rewind($stream);

        return $stream;
    }

    private function createQuestioner(array $input): InteractiveQuestioner
    {
        $style = $this->createStyle($input);

        return new InteractiveQuestioner(
            $style,
            function (array $variables = [], array $defaults = []) {
                return new AnswersSet(
                    function ($v) {
                        return $v;
                    }, $defaults
                );
            }
        );
    }
}
