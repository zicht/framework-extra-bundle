<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\JsonSchema;

use PHPUnit\Framework\TestCase;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Translation\TranslatorInterface;
use Zicht\Bundle\FrameworkExtraBundle\JsonSchema\SchemaService;

class SchemaServiceTest extends TestCase
{
    /** @var SchemaService */
    protected $schemaService = null;

    protected function setUp(): void
    {
        $translator = $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock();
        $this->schemaService = new SchemaService($translator, __DIR__);
    }

    /**
     * @param Schema|string|object|array $schema
     * @dataProvider validateSchemaTypesProvider
     * @throws \Swaggest\JsonSchema\Exception
     * @throws \Swaggest\JsonSchema\InvalidValue
     */
    public function testGetSchema($schema)
    {
        $result = $this->schemaService->getSchema($schema);
        $this->assertInstanceOf(Schema::class, $result);
    }

    /**
     * Provides tests to ensure that the $schema provided to SchemaService::validate can be Schema, string, or object
     *
     * @return array[]
     * @throws \Swaggest\JsonSchema\Exception
     * @throws \Swaggest\JsonSchema\InvalidValue
     */
    public function validateSchemaTypesProvider()
    {
        return [
            //
            // Schema can be a Schema instance
            //
            [Schema::import(__DIR__ . '/bundles/validate-foo.schema.json')],

            //
            // Schema can be a string
            //
            // Allows relative path without starting '/'
            ['bundles/validate-foo.schema.json'],
            // Allows relative path with starting '/'
            ['/bundles/validate-foo.schema.json'],
            // Allows absolute path
            [__DIR__ . '/bundles/validate-foo.schema.json'],

            //
            // Schema can be an object
            //
            [json_decode(file_get_contents(__DIR__ . '/bundles/validate-foo.schema.json'))],
        ];
    }

    /**
     * @param Schema|string|object|array $schema
     * @param string|int|object|array|null $data
     * @param string|int|object|array|null $expected
     * @param string|null $expectedMessageRegExp
     * @dataProvider validateProvider
     */
    public function testValidate($schema, $data, $expected, $expectedMessageRegExp = null)
    {
        $result = $this->schemaService->validate($schema, $data, $message);
        if ($expectedMessageRegExp === null) {
            self::assertNull($message);
        } else {
            self::assertRegExp($expectedMessageRegExp, $message);
        }
        self::assertEquals($expected, $result);
    }

    /**
     * Provides tests that check that a given value validates with the given schema
     *
     * @return array[]
     */
    public function validateProvider()
    {
        return [
            // Valid and invalid objects
            ['/bundles/validate-object.schema.json', [], true],
            ['/bundles/validate-object.schema.json', ['str' => 'Hello World'], true],
            ['/bundles/validate-object.schema.json', ['unspecifiedProperty' => 'Hello World'], true],
            ['/bundles/validate-object.schema.json', (object)[], true],
            ['/bundles/validate-object.schema.json', (object)['str' => 'Hello World'], true],
            ['/bundles/validate-object.schema.json', (object)['unspecifiedProperty' => 'Hello World'], true],
            ['/bundles/validate-object.schema.json', null, false, '/Object expected/i'],
            ['/bundles/validate-object.schema.json', 123, false, '/Object expected/i'],
            ['/bundles/validate-object.schema.json', 'Hello World', false, '/Object expected/i'],

            // Support refs
            ['/bundles/validate-refs.schema.json', ['number' => 123, 'string' => 'foo'], true],
        ];
    }

    /**
     * @param Schema|string|object|array $schema
     * @param string|int|object|array|null $data
     * @param string|int|object|array|null $expected
     * @param string|null $expectedMessageRegExp
     * @dataProvider migrateProvider
     */
    public function testMigrate($schema, $data, $expected, $expectedMessageRegExp = null)
    {
        $result = $this->schemaService->migrate($schema, $data, $message);
        if ($expectedMessageRegExp === null) {
            self::assertNull($message);
        } else {
            self::assertRegExp($expectedMessageRegExp, $message);
        }
        self::assertEquals($expected, $result);
    }

    /**
     * Provides tests that migrate data from an old schema to data matching a new schema
     *
     * @return array[]
     */
    public function migrateProvider()
    {
        return [
            // Should add property 'requiredString' automatically
            ['/bundles/migrate-object.schema.json', [], ['requiredString' => 'foo']],
            ['/bundles/migrate-object.schema.json', ['string' => 'foo'], ['string' => 'foo', 'requiredString' => 'foo']],
            ['/bundles/migrate-object.schema.json', ['requiredString' => 'bar'], ['requiredString' => 'bar']],
            ['/bundles/migrate-object.schema.json', ['string' => 'foo', 'requiredString' => 'bar'], ['string' => 'foo', 'requiredString' => 'bar']],

            // Should add nested property 'requiredString' automatically
            ['/bundles/migrate-nested-objects.schema.json', [], ['nest' => ['requiredString' => 'foo']]],
            ['/bundles/migrate-nested-objects.schema.json', ['nest' => ['requiredString' => 'bar']], ['nest' => ['requiredString' => 'bar']]],

            // Handling nested lists... should not be converted into object
            ['/bundles/migrate-object.schema.json', ['list' => []], ['list' => [], 'requiredString' => 'foo']],

            // Unfortunate...
            // In the next example we would have expected that the migration adds 'requiredString'.  Unfortunately this is not possibl
            // because php can not distinguish between an empty array and an empty object.
            ['/bundles/migrate-nested-objects.schema.json', ['nest' => []], ['nest' => []]],
            // However, by having at least one property (which should be the case in a migration), it is properly converted into an object, not an empty array
            ['/bundles/migrate-nested-objects.schema.json', ['nest' => ['string' => 'foo']], ['nest' => ['string' => 'foo', 'requiredString' => 'foo']]],
        ];
    }
}
