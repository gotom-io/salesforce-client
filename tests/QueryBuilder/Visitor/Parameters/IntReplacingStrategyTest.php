<?php

namespace Xsolve\SalesforceClient\QueryBuilder\Visitor\Parameters;

class IntReplacingStrategyTest extends AbstractReplacingStrategyTest
{
    protected function createStrategy(): ReplacingStrategyInterface
    {
        return new IntReplacingStrategy();
    }

    protected function getApplicableType(): Type
    {
        return Type::INT();
    }

    public static function replaceValuesProvider(): array
    {
        return [
            [0, '0'],
            [123, '123'],
            [123.01, '123'],
            [123.51, '123'],
            [-123, '-123'],
            [-123.01, '-123'],
            [-123.51, '-123'],
        ];
    }
}
