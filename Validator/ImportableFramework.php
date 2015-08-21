<?php

namespace HeVinci\CompetencyBundle\Validator;

use Symfony\Component\Validator\Constraint;

class ImportableFramework extends Constraint
{
    public $jsonIssue = 'framework_import.json_issue';
    public $schemaIssue = 'framework_import.schema_issue';
    public $dataIssue = 'framework_import.data_issue';
    public $conflictIssue = 'framework_import.conflict_issue';

    public function validatedBy()
    {
        return 'importable_framework_validator';
    }
}
