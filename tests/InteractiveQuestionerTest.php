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

use Dance\Questioner\NameQuestioner;
use Dance\Questioner\NamespaceQuestioner;
use Dance\Questioner\OptionalPathQuestioner;
use PHPUnit\Framework\TestCase;
use SkeletonDancer\Dance;
use SkeletonDancer\InteractiveQuestionInteractor;
use SkeletonDancer\Test\ContainerCreator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Console\IO\BufferedIO;

final class InteractiveQuestionerTest extends TestCase
{
    use ContainerCreator;

    private $input;
    private $output;

    /**
     * @test
     */
    public function it_iterates_all_configurators()
    {
        $profile = new Dance('test/test', '', [NameQuestioner::class, NamespaceQuestioner::class], []);
        $answers = $this->createQuestioner(['Dancer', 'Rollerworks\Something'])->interact($profile)->getAnswers();

        self::assertSame(['name' => 'Dancer', 'namespace' => 'Rollerworks\Something'], $answers);
    }

    /**
     * @test
     */
    public function it_skips_optional_questions()
    {
        $profile = new Dance('test/test', '', [NameQuestioner::class, OptionalPathQuestioner::class], []);
        $answers = $this->createQuestioner(['Dancer', ''])->interact($profile)->getAnswers();

        self::assertSame(['name' => 'Dancer', 'path' => '/'], $answers);
    }

    /**
     * @test
     */
    public function it_asks_optional_questions_when_needed()
    {
        $profile = new Dance('test/test', '', [NameQuestioner::class, OptionalPathQuestioner::class], []);
        $answers = $this->createQuestioner(['Dancer', 'src/'])->interact($profile, false)->getAnswers();

        self::assertSame(['name' => 'Dancer', 'path' => 'src/'], $answers);
    }

    /**
     * @test
     */
    public function it_provides_defaults_for_the_user()
    {
        // NB. "\n" means confirm as-is (the current default value).
        $profile = new Dance('test/test', '', [NameQuestioner::class, OptionalPathQuestioner::class], []);
        $answers = $this->createQuestioner(['Dancer', "\n"])->interact($profile)->getAnswers();

        self::assertSame(['name' => 'Dancer', 'path' => '/'], $answers);
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

    private function createQuestioner(array $input): InteractiveQuestionInteractor
    {
        $style = $this->createStyle($input);
        $this->setUpContainer();

        return new InteractiveQuestionInteractor($style, new BufferedIO(), $this->container['class_initializer']);
    }
}
