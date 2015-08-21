<?php

namespace HeVinci\CompetencyBundle\Validator;

use HeVinci\CompetencyBundle\Manager\TransferManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @DI\Validator("importable_framework_validator")
 *
 * Validator ensuring that a competency framework can be imported.
 */
class ImportableFrameworkValidator extends ConstraintValidator
{
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("hevinci.competency.transfer_manager")
     * })
     *
     * @param TransferManager $manager
     */
    public function __construct(TransferManager $manager)
    {
        $this->manager = $manager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof UploadedFile || !$constraint instanceof ImportableFramework) {
            return;
        }

        $errors = $this->manager->validate($value);

        switch ($errors['type']) {
            case TransferManager::ERR_TYPE_JSON:
                $this->addViolations($constraint->jsonIssue, $errors['errors']);
                break;
            case TransferManager::ERR_TYPE_SCHEMA:
                $this->addViolations($constraint->schemaIssue, $errors['errors']);
                break;
            case TransferManager::ERR_TYPE_INTERNAL:
                $this->addViolations($constraint->dataIssue, $errors['errors']);
                break;
            case TransferManager::ERR_TYPE_CONFLICT:
                $this->addViolations($constraint->conflictIssue, $errors['errors']);
                break;
            case TransferManager::ERR_TYPE_NONE:
            default:
                break;
        }
    }

    private function addViolations($baseMessage, $errors)
    {
        $this->context->buildViolation($baseMessage)->addViolation();

        foreach ($errors as $error) {
            $this->context->buildViolation('framework_import.original_error')
                ->setParameter('%msg%', $error)
                ->addViolation();
        }
    }
}
