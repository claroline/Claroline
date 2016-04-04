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
class CsvUser extends Constraint
{
    public $message = 'Each row requires at least 5 parameters.';
    private $mode = 0;

    public function __construct($mode = 0)
    {
        parent::__construct();
        $this->mode = $mode;
    }

    public function validatedBy()
    {
        return 'csv_user_validator';
    }

    public function getDefaultOption()
    {
        return $this->mode;
    }
}
