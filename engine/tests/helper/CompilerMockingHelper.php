<?php

namespace Sapin\Test\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use Sapin\Ast\Compiler;

trait CompilerMockingHelper
{
    /**
     * @return MockObject&Compiler
     */
    private function createMockCompiler(): MockObject
    {
        /**
         * @var MockObject&Compiler $compiler
         * @noinspection PhpUnitInvalidMockingEntityInspection
         */
        $compiler = $this->getMockBuilder(Compiler::class)
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