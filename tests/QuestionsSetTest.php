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
use SkeletonDancer\Question;
use SkeletonDancer\QuestionsSet;
use Symfony\Component\Console\Question\Question as SfQuestion;

final class QuestionsSetTest extends TestCase
{
    /**
     * @test
     */
    public function it_iterates_all_configurators()
    {
        $question1 = Question::ask('Name');
        $question2 = Question::ask('Path');

        $questions = new QuestionsSet(
            function (SfQuestion $question) use ($question1, $question2) {
                if ($question->getQuestion() === $question1->getLabel()) {
                    return 'Dancer';
                }

                if ($question->getQuestion() === $question2->getLabel()) {
                    return 'src/';
                }
            }
        );

        self::assertEquals('Dancer', $questions->communicate('name', $question1));
        self::assertEquals('src/', $questions->communicate('path', $question2));

        self::assertSame(['name' => 'Dancer', 'path' => 'src/'], $questions->getAnswers());
        self::assertEquals('Dancer', $questions->get('name'));
        self::assertNull($questions->get('foo'));

        self::assertTrue($questions->has('name'));
        self::assertTrue($questions->has('path'));
        self::assertFalse($questions->has('foo'));
    }

    /**
     * @test
     */
    public function it_skips_optional_questions()
    {
        $question1 = Question::ask('Name');
        $question2 = Question::ask('Path', '/')->markOptional();

        $questions = new QuestionsSet(
            function (SfQuestion $question) use ($question1, $question2) {
                if ($question->getQuestion() === $question1->getLabel()) {
                    return 'Dancer';
                }
            }
        );

        self::assertEquals('Dancer', $questions->communicate('name', $question1));
        self::assertEquals('/', $questions->communicate('path', $question2));
        self::assertSame(['name' => 'Dancer', 'path' => '/'], $questions->getAnswers());
    }

    /**
     * @test
     */
    public function it_asks_optional_questions_when_needed()
    {
        $question1 = Question::ask('Name');
        $question2 = Question::ask('Path', '/')->markOptional();

        $questions = new QuestionsSet(
            function (SfQuestion $question) use ($question1, $question2) {
                if ($question->getQuestion() === $question1->getLabel()) {
                    return 'Dancer';
                }

                if ($question->getQuestion() === $question2->getLabel()) {
                    return 'src/';
                }
            }, false
        );

        self::assertEquals('Dancer', $questions->communicate('name', $question1));
        self::assertEquals('src/', $questions->communicate('path', $question2));
        self::assertSame(['name' => 'Dancer', 'path' => 'src/'], $questions->getAnswers());
    }

    /**
     * @test
     */
    public function it_allows_optional_questions_to_be_null()
    {
        $question1 = Question::ask('Name');
        $question2 = Question::ask('Path', '/')->markOptional('/');

        $questions = new QuestionsSet(
            function (SfQuestion $question) use ($question1, $question2) {
                if ($question->getQuestion() === $question1->getLabel()) {
                    return 'Dancer';
                }

                if ($question->getQuestion() === $question2->getLabel()) {
                    self::assertNotNull($normalizer = $question2->getNormalizer());

                    return $normalizer('/');
                }
            }, false
        );

        self::assertEquals('Dancer', $questions->communicate('name', $question1));
        self::assertEquals(null, $questions->communicate('path', $question2));

        self::assertSame(['name' => 'Dancer', 'path' => null], $questions->getAnswers());
    }

    /**
     * @test
     */
    public function it_executes_question_default_when_lazy()
    {
        $question1 = Question::ask('Name');
        $question2 = Question::ask('Path', function ($config) {
            return $config['name'].'/';
        });

        $questions = new QuestionsSet(
            function (SfQuestion $question) use ($question1, $question2) {
                if ($question->getQuestion() === $question1->getLabel()) {
                    return 'Dancer';
                }

                if ($question->getQuestion() === $question2->getLabel()) {
                    return $question->getDefault();
                }
            }
        );

        self::assertEquals('Dancer', $questions->communicate('name', $question1));
        self::assertEquals('Dancer/', $questions->communicate('path', $question2));
        self::assertSame(['name' => 'Dancer', 'path' => 'Dancer/'], $questions->getAnswers());
    }

    /**
     * @test
     */
    public function it_supplies_defaults()
    {
        $question1 = Question::ask('Name');
        $question2 = Question::ask('Path', '/');

        $questions = new QuestionsSet(
            function (SfQuestion $question) {
                return $question->getDefault();
            }
        );

        self::assertNull($questions->communicate('name', $question1));
        self::assertEquals('/', $questions->communicate('path', $question2));
        self::assertSame(['name' => null, 'path' => '/'], $questions->getAnswers());
    }

    /**
     * @test
     */
    public function it_does_not_allow_overwrites()
    {
        $question1 = Question::ask('Name');
        $question2 = Question::ask('Path', '/');

        $questions = new QuestionsSet(
            function (SfQuestion $question) {
                return $question->getDefault();
            }
        );

        $questions->communicate('name', $question1);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Answer "name" already exists in the QuestionsSet.');

        $questions->communicate('name', $question2);
    }
}
