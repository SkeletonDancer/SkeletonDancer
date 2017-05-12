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

//final class AnswersSetTest extends TestCase
//{
//    /** @test */
//    public function its_answers_and_values_are_empty_when_non_given()
//    {
//        $set = new AnswersSet(
//            function ($value) {
//                return $value;
//            }, ['key2' => 'value2']
//        );
//
//        self::assertEquals([], $set->answers());
//    }
//
//    /** @test */
//    public function it_allows_setting_answers()
//    {
//        $set = new AnswersSet(
//            function ($value) {
//                return $value;
//            }, ['key2' => 'value2']
//        );
//
//        $set->set('my-q', 'my answer');
//        $set->set('my-n', 'my answer2');
//
//        self::assertEquals(['my-q' => 'my answer', 'my-n' => 'my answer2'], $set->answers());
//
//        self::assertTrue($set->has('my-q'));
//        self::assertTrue($set->has('my-n'));
//        self::assertFalse($set->has('my-s'));
//    }
//
//    /** @test */
//    public function it_restricts_setting_the_same_answer_twice()
//    {
//        $set = new AnswersSet(
//            function ($value) {
//                return $value;
//            }, ['key2' => 'value2']
//        );
//
//        $set->set('my-q', 'my answer');
//
//        $this->expectException(\InvalidArgumentException::class);
//        $this->expectExceptionMessage('An answer was already set for "my-q"');
//
//        $set->set('my-q', 'my answer2');
//    }
//
//    /** @test */
//    public function it_resolves_a_closure_as_default()
//    {
//        $set = new AnswersSet(
//            function ($value) {
//                return $value;
//            }, ['key1' => 'value1']
//        );
//
//        $set->set('my-q', 'my answer');
//
//        self::assertEquals('value1', $set->resolve('key1', 'vay'));
//        self::assertEquals('vay', $set->resolve('key3', 'vay'));
//        self::assertEquals(
//            '{"my-q":"my-value"}=={"my-q":"my answer"}',
//            $set->resolve(
//                'key3',
//                function ($values, $answers) {
//                    return
//                        json_encode($values, JSON_UNESCAPED_SLASHES).
//                        '=='.
//                        json_encode($answers, JSON_UNESCAPED_SLASHES);
//                }
//            )
//        );
//
//        // Answer is set for this key.
//        self::assertEquals('my answer', $set->resolve('my-q', 'vay2'));
//    }
//}
