<?php

namespace Sapin\Test\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sapin\Ast\Compiler;

abstract class CompilerMockingHelper
{
    /**
     * @return MockObject&Compiler
     */
    public static function createMockCompiler(TestCase $context): MockObject
    {
        /**
         * @var MockObject&Compiler $compiler
         * @noinspection PhpUnitInvalidMockingEntityInspection
         */
        $compiler = $context->getMockBuilder(Compiler::class)
            ->onlyMethods(['compileNodes', 'compileNode'])
            ->getMock();

        $compiler
            ->method('compileNodes')
            ->willReturnCallback(function () use ($compiler) {
                $compiler->write('[children]');
                return $compiler;
            });

        $compiler
            ->method('compileNode')
            ->willReturnCallback(function () use ($compiler) {
                $compiler->write('[child]');
                return $compiler;
            });

        return $compiler;
    }
}
