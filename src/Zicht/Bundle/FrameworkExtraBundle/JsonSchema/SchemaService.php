<?php declare(strict_types=1);
/**
 * @copyright Zicht Online <https://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\JsonSchema;

use Swaggest\JsonSchema\Context;
use Swaggest\JsonSchema\RemoteRefProvider;
use Swaggest\JsonSchema\Schema;

class SchemaService
{
    /** @var RemoteRefProvider|null */
    private $provider;

    /** @var string */
    private $webDir;

    public function __construct(RemoteRefProvider $provider, string $webDir)
    {
        $this->provider = $provider;
        $this->webDir = $webDir;
    }

    /**
     * @param Schema|string|\stdClass $schema
     * @return Schema
     * @throws \Swaggest\JsonSchema\Exception
     * @throws \Swaggest\JsonSchema\InvalidValue
     */
    public function getSchema($schema): Schema
    {
        if ($schema instanceof Schema) {
            return $schema;
        }

        if (is_string($schema)) {
            return Schema::import($schema, $this->getContext());

//            if (is_file($this->webDir . '/' . $schema)) {
//                return Schema::import($this->webDir . '/' . $schema, $this->getContext());
//            }
//
//            if (is_file($schema)) {
//                return Schema::import($schema, $this->getContext());
//            }
//
//            throw new \RuntimeException(sprintf('"%s" should point to an existing file within "%s"', $schema, $this->webDir));
        }

        if (is_object($schema)) {
            return Schema::import($schema, $this->getContext());
        }

        throw new \RuntimeException('$schema should be either a Schema instance, a string (file path) or \stdClass (schema)');
    }

    /**
     * Try to validate a given value
     *
     * @param Schema|string|\stdClass $schema
     * @param bool|int|float|string|array $data
     * @param null|string $message
     * @return bool Returns false when the validation failed
     */
    public function validate($schema, $data, &$message = null): bool
    {
        // PHP is unable to distinguish between an empty array and an empty object,
        // that causes `[]` to become an empty array, while the schema expects an
        // empty object.  We fix this case manually and hope this does not occur
        // anywhere else.
        if ($data === []) {
            $data = (object)$data;
        }

        try {
            // json_encode and then json_decode to return object structure instead of php array structure because the schema uses objects
            $this->getSchema($schema)->in(json_decode(json_encode($data), false));
            return true;
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return false;
        }
    }

    /**
     * Try to migrate a given value to a new value
     *
     * @param Schema|string|\stdClass $schema
     * @param bool|int|float|string|array $data
     * @param null|string $message
     * @return bool|int|float|string|array|null Returns null when the validation failed
     */
    public function migrate($schema, $data, &$message = null)
    {
        $context = $this->getContext();
        $context->skipValidation = true;

        // json_encode and then json_decode to return object structure instead of php array structure because the schema uses objects
        $objectValue = json_decode(json_encode($data), false);

        // PHP is unable to distinguish between an empty array and an empty object,
        // that causes `[]` to become an empty array, while the schema expects an
        // empty object.  We fix this case manually and hope this does not occur
        // anywhere else.
        if ($objectValue === []) {
            $objectValue = (object)$objectValue;
        }

        // json_encode and then json_decode to return php array structure instead of object structure because the key-value-bundle uses php arrays
        try {
            return json_decode(json_encode($this->getSchema($schema)->process($objectValue, $context)), true);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return null;
        }
    }

    private function getContext(): Context
    {
        return new Context($this->provider);
    }
}
