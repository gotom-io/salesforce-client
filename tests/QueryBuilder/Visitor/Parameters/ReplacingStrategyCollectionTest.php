<?php

namespace Xsolve\SalesforceClient\QueryBuilder\Visitor\Parameters;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ReplacingStrategyCollectionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ReplacingStrategyCollection
     */
    private $collection;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->collection = new ReplacingStrategyCollection();
    }

    public function testOffsetExistsDefault()
    {
        $this->assertFalse(isset($this->collection[0]));
        $this->assertTrue(isset($this->collection[null]));
    }

    #[DataProvider('typesProvider')]
    public function testOffsetExists(Type $type)
    {
        $this->assertTrue(isset($this->collection[$type]));
    }

    public function testOffsetGetDefault()
    {
        $this->assertInstanceOf(ReplacingStrategyInterface::class, $this->collection[null]);
    }

    #[DataProvider('typesProvider')]
    public function testOffsetGet(Type $type)
    {
        $this->assertInstanceOf(ReplacingStrategyInterface::class, $this->collection[$type]);
    }

    #[DataProvider('typesProvider')]
    public function testOffsetSetUnset(Type $type)
    {
        unset($this->collection[$type]);
        $this->assertFalse(isset($this->collection[$type]));
        $strategy = $this->prophesize(ReplacingStrategyInterface::class);
        $strategy->isApplicable($type)->willReturn(true);
        $this->collection[] = $strategy->reveal();
        $this->assertTrue(isset($this->collection[$type]));
        $this->assertSame($strategy->reveal(), $this->collection[$type]);
    }

    public static function typesProvider(): array
    {
        return array_map(function (Type $type) {
            return [$type];
        }, Type::members());
    }
}
