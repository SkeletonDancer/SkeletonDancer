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

namespace Rollerworks\Tools\SkeletonDancer\Tests;

use Rollerworks\Tools\SkeletonDancer\AnswersSet;
use Rollerworks\Tools\SkeletonDancer\Question;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;
use Symfony\Component\Console\Question\Question as SfQuestion;

final class QuestionsSetTest extends \PHPUnit_Framework_TestCase
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
            },
            new AnswersSet(
                function ($v) {
                    return $v;
                }, []
            )
        );

        $this->assertEquals('Dancer', $questions->communicate('name', $question1));
        $this->assertEquals('src/', $questions->communicate('path', $question2));

        $this->assertSame(['name' => 'Dancer', 'path' => 'src/'], $questions->getAnswers());
        $this->assertEquals('Dancer', $questions->get('name'));
        $this->assertNull($questions->get('foo'));

        $this->assertTrue($questions->has('name'));
        $this->assertTrue($questions->has('path'));
        $this->assertFalse($questions->has('foo'));
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
            },
            new AnswersSet(
                function ($v) {
                    return $v;
                }, []
            )
        );

        $this->assertEquals('Dancer', $questions->communicate('name', $question1));
        $this->assertEquals('/', $questions->communicate('path', $question2));
        $this->assertSame(['name' => 'Dancer', 'path' => '/'], $questions->getAnswers());
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
            },
            new AnswersSet(
                function ($v) {
                    return $v;
                }, []
            ),
            false
        );

        $this->assertEquals('Dancer', $questions->communicate('name', $question1));
        $this->assertEquals('src/', $questions->communicate('path', $question2));
        $this->assertSame(['name' => 'Dancer', 'path' => 'src/'], $questions->getAnswers());
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
                    return '/';
                }
            },
            new AnswersSet(
                function ($v) {
                    return $v;
                }, []
            ),
            false
        );

        $this->assertEquals('Dancer', $questions->communicate('name', $question1));
        $this->assertEquals(null, $questions->communicate('path', $question2));

        $this->assertSame(['name' => 'Dancer', 'path' => null], $questions->getValues());
        $this->assertSame(['name' => 'Dancer', 'path' => '/'], $questions->getAnswers());
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
            },
            new AnswersSet(
                function ($v) {
                    return $v;
                }, []
            )
        );

        $this->assertEquals('Dancer', $questions->communicate('name', $question1));
        $this->assertEquals('Dancer/', $questions->communicate('path', $question2));
        $this->assertSame(['name' => 'Dancer', 'path' => 'Dancer/'], $questions->getAnswers());
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
            },
            new AnswersSet(
                function ($v) {
                    return $v;
                }, ['name' => 'Dancer', 'path' => 'src/']
            )
        );

        $this->assertEquals('Dancer', $questions->communicate('name', $question1));
        $this->assertEquals('src/', $questions->communicate('path', $question2));
        $this->assertSame(['name' => 'Dancer', 'path' => 'src/'], $questions->getAnswers());
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
            },
            new AnswersSet(
                function ($v) {
                    return $v;
                }, []
            )
        );

        $questions->communicate('name', $question1);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Question with name "name" already exists in the QuestionsSet.');

        $questions->communicate('name', $question2);
    }
}
