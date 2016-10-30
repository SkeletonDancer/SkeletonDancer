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

namespace Rollerworks\Tools\SkeletonDancer\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Rollerworks\Tools\SkeletonDancer\Configuration\AnswersSetFactory;
use Rollerworks\Tools\SkeletonDancer\Exception\VariableCircularReferenceException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class AnswersSetFactoryTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $expressionLanguage;
    /** @var AnswersSetFactory */
    private $factory;

    /** @before */
    public function setupFactory()
    {
        // Expression language is mocked here as don't care about
        // the actual execution (for most tests).
        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);
        $this->factory = new AnswersSetFactory($this->expressionLanguage);
    }

    /** @test */
    public function it_creates_an_AnswersSet_with_defaults()
    {
        $answersSet = $this->factory->create(['k' => 'v'], ['key1' => 'value']);

        self::assertEquals('value', $answersSet->resolve('key1'));
    }

    /** @test */
    public function it_evaluates_expressions()
    {
        $this->expressionLanguage->expects(self::once())
            ->method('evaluate')
            ->with('k1')
            ->willReturn('2v2')
        ;

        $answersSet = $this->factory->create([], ['key1' => '@k1', 'key2' => '@@k1']);

        self::assertEquals('2v2', $answersSet->resolve('key1'));
        self::assertEquals('@k1', $answersSet->resolve('key2'));
    }

    /**
     * Do some actual testing of expressions.
     *
     * @test
     */
    public function it_evaluates_expressions_for_real()
    {
        $this->expressionLanguage = new ExpressionLanguage();
        $this->factory = new AnswersSetFactory($this->expressionLanguage);

        $answersSet = $this->factory->create(['k1' => '2v2'], ['key1' => '@variables["k1"]', 'key2' => '@@k1']);

        self::assertEquals('2v2', $answersSet->resolve('key1'));
        self::assertEquals('@k1', $answersSet->resolve('key2'));
    }

    /**
     * @test
     */
    public function it_guards_expression_variables_against_circular_references()
    {
        $this->expressionLanguage = new ExpressionLanguage();
        $this->factory = new AnswersSetFactory($this->expressionLanguage);

        $answersSet = $this->factory->create(
            ['k2' => '@variables["k1"]', 'k1' => '@variables["k2"]'],
            ['key1' => '@variables["k1"]']
        );

        $expectedMessagePart = new VariableCircularReferenceException('k1', ['k1', 'k2']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessagePart->getMessage());

        $answersSet->resolve('key1');
    }
}
