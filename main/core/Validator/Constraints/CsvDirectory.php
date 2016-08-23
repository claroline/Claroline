<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CsvDirectory extends Constraint
{
    public $message = 'Each row requires at least 2 parameters.';

    public function __construct()
    {
        parent::__construct();
    }

    public function validatedBy()
    {
        return 'csv_directory_import_validator';
    }
}
