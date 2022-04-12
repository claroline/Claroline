<?php

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\JVal\Validator;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Psr\Container\ContainerInterface;

class ValidatorProvider
{
    /** @var string */
    const CREATE = 'create';
    /** @var string */
    const UPDATE = 'update';

    /** @var ObjectManager */
    private $om;
    /** @var SchemaProvider */
    private $schema;

    /**
     * The list of registered validators in the platform.
     *
     * @var ContainerInterface
     */
    private $validators;

    public function __construct(
        ObjectManager $om,
        ContainerInterface $validators,
        SchemaProvider $schema
    ) {
        $this->om = $om;
        $this->validators = $validators;
        $this->schema = $schema;
    }

    /**
     * Gets a registered validator instance.
     *
     * @throws \Exception
     */
    public function get(string $class): ?ValidatorInterface
    {
        if (!$this->validators->has($class)) {
            throw new \Exception(sprintf('No validator found for class "%s" Maybe you forgot to add the "claroline.validator" tag to your validator.', $class));
        }

        return $this->validators->get($class);
    }

    /**
     * Validates `data` using the `class` validator.
     *
     * @param string $class          - the class of the validator to use
     * @param mixed  $data           - the data to validate
     * @param string $mode           - 'create', 'update'
     * @param bool   $throwException - if true an InvalidDataException is thrown instead of returning the errors
     *
     * @return array - the list of validation errors
     *
     * @throws InvalidDataException
     */
    public function validate($class, $data, $mode, $throwException = false, array $options = [])
    {
        // validates JSON Schema
        $schema = $this->schema->getSchema($class);
        if ($schema) {
            $validator = Validator::buildDefault();
            $errors = $validator->validate($this->toObject($data), $schema, '', [$mode]);
            if (!empty($errors)) {
                if ($throwException) {
                    throw new InvalidDataException(sprintf('Invalid data for "%s".', $class), $errors);
                }

                return $errors;
            }
        }

        // validates unique fields
        try {
            $validator = $this->get($class);
        } catch (\Exception $e) {
            //no custom validator
            $uniqueFields = [];
            $identifiers = $this->schema->getIdentifiers($class);

            if (is_array($identifiers)) {
                foreach ($identifiers as $identifier) {
                    $uniqueFields[$identifier] = $identifier;
                }
            }

            $errors = $this->validateUnique($uniqueFields, $data, $mode, $class);
            if (!empty($errors) && $throwException) {
                throw new InvalidDataException(sprintf('Invalid data for "%s".', $class), $errors);
            }

            return $errors;
        }

        // can be deduced from the mapping, but we won't know
        // which field is related to which data prop in that case
        $uniqueFields = $validator->getUniqueFields();
        $errors = $this->validateUnique($uniqueFields, $data, $mode, $class);

        //custom validation
        $errors = array_merge($errors, $validator->validate($data, $mode, $options));

        if (!empty($errors) && $throwException) {
            throw new InvalidDataException(sprintf('Invalid data for "%s".', $class), $errors);
        }

        return $errors;
    }

    private function toObject(array $data): \stdClass
    {
        $data = json_decode(json_encode($data));

        if ([] === $data) {
            $data = new \StdClass();
        }

        return $data;
    }

    private function validateUnique(array $uniqueFields, array $data, $mode, $class)
    {
        $errors = [];

        foreach ($uniqueFields as $dataProp => $entityProp) {
            if (isset($data[$dataProp])) {
                $qb = $this->om->createQueryBuilder();

                $qb
                    ->select('COUNT(DISTINCT o)')
                    ->from($class, 'o')
                    ->where("o.{$entityProp} = :{$entityProp}")
                    ->setParameter($entityProp, $data[$dataProp]);

                if (self::UPDATE === $mode && isset($data['id'])) {
                    // we are updating an existing object, we must exclude it from the results
                    $parameter = is_numeric($data['id']) ? 'id' : 'uuid';
                    $value = is_numeric($data['id']) ? (int) $data['id'] : $data['id'];

                    $qb
                        ->setParameter($parameter, $value)
                        ->andWhere("o.{$parameter} != :{$parameter}");
                }

                $countResults = $qb->getQuery()->getSingleScalarResult();

                if ((self::UPDATE === $mode && isset($data['id'])) || self::CREATE === $mode) {
                    if ($countResults > 0) {
                        $errors[] = ['path' => $dataProp, 'message' => "{$dataProp} already exists and should be unique"];
                    }
                } else {
                    if ($countResults > 1) {
                        $errors[] = ['path' => $dataProp, 'message' => "{$dataProp} already exists and should be unique"];
                    }
                }
            }
        }

        return $errors;
    }
}
