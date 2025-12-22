<?php

namespace Xsolve\SalesforceClient\Serializer;

use JMS\Serializer\Metadata\PropertyMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class CamelCaseNamingStrategyTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var CamelCaseNamingStrategy
     */
    private $strategy;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->strategy = new CamelCaseNamingStrategy();
    }

    #[DataProvider('propertyNameProvider')]
    public function testTranslateName(string $propertyName, string $expectedName)
    {
        $metadata = $this->prophesize(PropertyMetadata::class);
        $metadata->name = $propertyName;

        $this->assertSame($expectedName, $this->strategy->translateName($metadata->reveal()));
    }

    /**
     * @return array
     */
    public static function propertyNameProvider()
    {
        return [
            ['', ''],
            ['test', 'Test'],
            ['testPropertyName', 'TestPropertyName'],
        ];
    }
}
