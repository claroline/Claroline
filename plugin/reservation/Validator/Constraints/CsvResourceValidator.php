<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use Ddeboer\DataImport\Reader\CsvReader;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("csv_resource_validator")
 */
class CsvResourceValidator extends ConstraintValidator
{
    private $em;
    /**
     * @DI\InjectParams({
     *      "em" = @Di\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($file, Constraint $constraint)
    {
        if ($file) {
            $file = new \SplFileObject($file->getPathname());
            $reader = new CsvReader($file);
            $reader->setHeaderRowNumber(0);

            foreach ($reader as $lineNb => $row) {
                $lineNb = $lineNb + 1;

                if (count($row) !== 7) {
                    $this->context->addViolation('invalid_number_of_column', ['%lineNb%' => $lineNb]);

                    return;
                }

                $columnHeaders = [
                    'resource_type',
                    'name',
                    'max_time_reservation',
                    'description',
                    'localisation',
                    'quantity',
                    'color',
                ];
                foreach ($columnHeaders as $name) {
                    if (!array_key_exists($name, $row)) {
                        $this->context->addViolation('invalid_column_headers', ['%columnName%' => $name]);

                        return;
                    }
                }

                if (strlen($row['resource_type']) < 2 || strlen($row['resource_type']) > 50) {
                    $this->context->addViolation('invalid_number_characters_resource_type', ['%lineNb%' => $lineNb]);

                    return;
                }

                if (strlen($row['name']) < 2 || strlen($row['name']) > 50) {
                    $this->context->addViolation('invalid_number_characters_name', ['%lineNb%' => $lineNb]);

                    return;
                }

                $file2 = new \SplFileObject($file->getPathname());
                $reader2 = new CsvReader($file2);
                $reader2->setHeaderRowNumber(0);
                foreach ($reader2 as $lineNb2 => $row2) {
                    $lineNb2 = $lineNb2 + 1;
                    if ($lineNb !== $lineNb2 &&
                        strtolower($row['resource_type']) === strtolower($row2['resource_type']) &&
                        strtolower($row['name']) === strtolower($row2['name'])) {
                        $this->context->addViolation('double_resource_for_one_resource_type', ['%lineNb1%' => $lineNb, '%lineNb2%' => $lineNb2]);

                        return;
                    }
                }

                if (!empty($row['max_time_reservation']) && !preg_match('#^[0-9]+:[0-9]{2}(:[0-9]{2})?$#', $row['max_time_reservation'])) {
                    $this->context->addViolation('invalid_type_for_max_time_reservation', ['%lineNb%' => $lineNb]);

                    return;
                }

                if (strlen($row['localisation']) > 255) {
                    $this->context->addViolation('invalid_number_characters_localisation', ['%lineNb%' => $lineNb]);

                    return;
                }

                if (intval($row['quantity']) < 1) {
                    $this->context->addViolation('invalid_number_quantity', ['%lineNb%' => $lineNb]);

                    return;
                }

                if (!preg_match('/#[a-zA-Z0-9]{6}/', $row['color']) && !empty($row['color'])) {
                    $this->context->addViolation('invalid_color_format', ['%lineNb%' => $lineNb]);

                    return;
                }

                $resourceType = $this->em->getRepository('FormaLibreReservationBundle:ResourceType')->findOneBy(['name' => $row['resource_type']]);
                if ($resourceType) {
                    $resource = $this->em->getRepository('FormaLibreReservationBundle:Resource')->findBy([
                        'resourceType' => $resourceType->getId(),
                        'name' => $row['name'],
                    ]);

                    if ($resource) {
                        $this->context->addViolation('resource_already_exists_for_resource_type', ['%lineNb%' => $lineNb]);

                        return;
                    }
                }
            }
        }
    }
}
