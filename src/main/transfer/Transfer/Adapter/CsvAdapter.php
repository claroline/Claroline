<?php

namespace Claroline\TransferBundle\Transfer\Adapter;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\TransferBundle\Transfer\Adapter\Explain\Csv\Explanation;
use Claroline\TransferBundle\Transfer\Adapter\Explain\Csv\ExplanationBuilder;
use Claroline\TransferBundle\Transfer\Adapter\Explain\Csv\Property;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\TranslatorInterface;

class CsvAdapter implements AdapterInterface
{
    private const LINE_DELIMITER = PHP_EOL;
    private const COLUMN_DELIMITER = ';';
    private const ARRAY_DELIMITER = ',';
    private const ENCLOSURE = '"';

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function supports(string $mimeType): bool
    {
        return in_array($mimeType, ['text/csv', 'csv', 'text/plain', 'application/vnd.ms-excel', 'text/x-csv']);
    }

    /**
     * Create a php array object from the schema according to the data passed on.
     * Each line is a new object.
     *
     * @param string $content
     *
     * @return array
     */
    public function decodeSchema($content, Explanation $explanation)
    {
        $data = [];
        $lines = str_getcsv($content, self::LINE_DELIMITER);
        $header = array_shift($lines);
        $headers = array_filter(
            array_map(function ($headerProp) {
                return trim($headerProp);
            }, str_getcsv($header, self::COLUMN_DELIMITER, self::ENCLOSURE)),
            function ($headerProp) {
                return !empty($headerProp);
            }
        );

        foreach ($lines as $line) {
            if (!empty($line)) {
                $properties = array_map(function ($property) {
                    return trim($property);
                }, str_getcsv($line, self::COLUMN_DELIMITER, self::ENCLOSURE));
                $data[] = $this->buildObjectFromLine($properties, $headers, $explanation);
            }
        }

        return $data;
    }

    public function explainSchema(\stdClass $data, $mode)
    {
        $builder = new ExplanationBuilder($this->translator, $mode);

        return $builder->explainSchema($data);
    }

    public function explainIdentifiers(array $schemas)
    {
        $builder = new ExplanationBuilder($this->translator);

        return $builder->explainIdentifiers($schemas);
    }

    public function dump(string $fileDest, array $data, array $options, ?bool $append = false): void
    {
        if (empty($data)) {
            return;
        }

        $headers = !empty($options['headers']) ? $options['headers'] : ArrayUtils::getPropertiesName($data[0]);

        $fs = new FileSystem();

        if (!$append) {
            $fs->appendToFile($fileDest, implode(self::COLUMN_DELIMITER, $headers));
        }

        $lines = [];
        foreach ($data as $object) {
            $properties = [];
            foreach ($headers as $header) {
                $value = $this->getCsvSerialized($object, $header);
                // escape enclosure char if it's present inside the value
                $value = str_replace(self::ENCLOSURE, self::ENCLOSURE.self::ENCLOSURE, $value);

                $properties[] = self::ENCLOSURE.$value.self::ENCLOSURE;
            }

            $lines[] = implode(self::COLUMN_DELIMITER, $properties);
        }

        $fs->appendToFile($fileDest, self::LINE_DELIMITER.implode(self::LINE_DELIMITER, $lines));
    }

    /**
     * Build an object from an array of headers and properties path.
     */
    private function buildObjectFromLine(array $properties, array $headers, Explanation $explanation): array
    {
        $object = [];

        foreach ($headers as $index => $property) {
            //idiot condition proof in case something is wrong with the csv (like more lines or columns)
            if (isset($properties[$index]) && $properties[$index]) {
                $explainedProperty = $explanation->getProperty($property);

                if ($explainedProperty) {
                    $this->addPropertyToObject($explainedProperty, $object, $properties[$index]);
                }
            }
        }

        return $object;
    }

    /**
     * Build an object from an array of headers and properties path.
     *
     * @param mixed $value
     *
     * @return array
     */
    private function addPropertyToObject(Property $property, array &$object, $value)
    {
        $propertyName = $property->getName();
        $types = !is_array($property->getType()) ? [$property->getType()] : $property->getType();

        if ($property->isArray()) {
            $keys = explode('.', $propertyName);
            $objectProp = array_pop($keys);
            $formattedValue = array_map(function ($objectValue) use ($objectProp, $types) {
                $object = [];
                $object[$objectProp] = $this->formatValue($types, $objectValue);

                return $object;
            }, explode(self::ARRAY_DELIMITER, $value));

            $propertyName = implode('.', $keys);
        } else {
            $formattedValue = $this->formatValue($types, $value);
        }

        ArrayUtils::set($object, $propertyName, $formattedValue);

        return $object;
    }

    private function formatValue(array $types, $value)
    {
        $formattedValue = $value;

        if ('null' === $value && in_array('null', $types)) {
            $formattedValue = null;
        } else {
            if (in_array('integer', $types)) {
                $formattedValue = (int) $value;
            } elseif (in_array('boolean', $types)) {
                $formattedValue = (bool) $value;
            } elseif (in_array('string', $types) && empty($value)) {
                $formattedValue = '';
            }
        }

        return $formattedValue;
    }

    /**
     * Returns the property of the object according to the path for the csv export.
     */
    private function getCsvSerialized(array $object, string $path): string
    {
        $value = ArrayUtils::get($object, $path);
        if (!empty($value)) {
            if (is_array($value)) {
                return $this->getCsvArraySerialized($value, $path);
            }

            return strval($value);
        }

        return '';
    }

    /**
     * Returns the serialized array for the csv export.
     */
    private function getCsvArraySerialized(array $elements, string $path): string
    {
        $data = [];

        foreach ($elements as $element) {
            if (is_array($element)) {
                $data[] = $this->getCsvSerialized($element, $path);
            } else {
                $data[] = $element;
            }
        }

        return implode($data, self::ARRAY_DELIMITER);
    }
}
