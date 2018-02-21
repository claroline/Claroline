<?php

namespace Claroline\AppBundle\API\Transfer\Adapter;

use Claroline\AppBundle\API\Transfer\Adapter\Explain\Csv\Explanation;
use Claroline\AppBundle\API\Transfer\Adapter\Explain\Csv\ExplanationBuilder;
use Claroline\AppBundle\API\Transfer\Adapter\Explain\Csv\Property;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service()
 * @DI\Tag("claroline.transfer.adapter")
 */
class CsvAdapter implements AdapterInterface
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var ArrayUtils */
    private $arrayUtils;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator")
     * })
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->arrayUtils = new ArrayUtils();
    }

    /**
     * Create a php array object from the schema according to the data passed on.
     * Each line is a new object.
     *
     * @param string      $content
     * @param Explanation $explanation
     */
    public function decodeSchema($content, Explanation $explanation)
    {
        $data = [];
        $lines = str_getcsv($content, PHP_EOL);
        $header = array_shift($lines);
        $headers = array_filter(
          str_getcsv($header, ';'),
          function ($header) {
              return '' !== $header;
          }
        );

        foreach ($lines as $line) {
            $properties = str_getcsv($line, ';');
            $data[] = $this->buildObjectFromLine($properties, $headers, $explanation);
        }

        return $data;
    }

    /**
     * Build an object from an array of headers and properties path.
     *
     * @param array       $properties
     * @param array       $headers
     * @param Explanation $explanation
     */
    private function buildObjectFromLine(array $properties, array $headers, Explanation $explanation)
    {
        $object = [];

        foreach ($headers as $index => $property) {
            //idiot condition proof in case something is wrong with the csv (like more lines or columns)
            if ($properties[$index]) {
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
     * @param Property $property
     * @param array    &$object
     * @param mixed    $value
     */
    private function addPropertyToObject(Property $property, array &$object, $value)
    {
        $propertyName = $property->getName();

        if ($property->isArray()) {
            $keys = explode('.', $propertyName);
            $objectProp = array_pop($keys);
            $value = array_map(function ($value) use ($objectProp) {
                $object = [];
                $object[$objectProp] = $value;

                return $object;
            }, explode(',', $value));

            $propertyName = implode('.', $keys);
        }

        if ('integer' === $property->getType()) {
            $value = (int) $value;
        }

        if ('boolean' === $property->getType()) {
        }

        $this->arrayUtils->set($object, $propertyName, $value);
    }

    public function explainSchema(\stdClass $data)
    {
        $builder = new ExplanationBuilder($this->translator);

        return $builder->explainSchema($data);
    }

    public function explainIdentifiers(array $schemas)
    {
        $builder = new ExplanationBuilder($this->translator);

        return $builder->explainIdentifiers($schemas);
    }

    public function format(array $data, array $options)
    {
        $lines = [];
        $headers = $options['headers'];
        $lines[] = implode(';', $headers);

        foreach ($data as $object) {
            $properties = [];

            $object = json_decode(json_encode($object));

            foreach ($headers as $header) {
                $properties[] = $this->getCsvSerialized($object, $header);
            }

            $lines[] = implode(';', $properties);
        }

        $data = implode('</br>', $lines);

        return $data;
    }

    /**
     * Returns the property of the object according to the path for the csv export.
     *
     * @param \stdClass $object
     * @param string    $path
     *
     * @return string
     */
    private function getCsvSerialized(\stdClass $object, $path)
    {
        //it's easier to work with objects here
        $parts = explode('.', $path);
        $first = array_shift($parts);

        if (property_exists($object, $first) && is_object($object->{$first})) {
            return $this->getCsvSerialized($object->{$first}, implode($parts, '.'));
        }

        if (property_exists($object, $first) && is_array($object->{$first})) {
            return $this->getCsvArraySerialized($object->{$first}, implode($parts, '.'));
        }

        if (property_exists($object, $first) && !is_array($object->$first)) {
            return $object->{$first};
        }
    }

    /**
     * Returns the serialized array for the csv export.
     *
     * @param array  $elements - the array to serialize
     * @param string $path     - the property path inside the array
     *
     * @return string
     */
    private function getCsvArraySerialized(array $elements, $path)
    {
        $data = [];

        foreach ($elements as $element) {
            $data[] = $this->getCsvSerialized($element, $path);
        }

        return implode($data, ',');
    }

    public function getMimeTypes()
    {
        return ['text/csv', 'csv', 'text/plain'];
    }
}
