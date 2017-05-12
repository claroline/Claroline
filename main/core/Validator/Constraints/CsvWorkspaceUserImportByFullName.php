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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CsvWorkspaceUserImportByFullName extends Constraint
{
    private $workspace;

    public $message = 'Each row requires at least 2 parameters.';

    public function __construct(Workspace $workspace)
    {
        parent::__construct();
        $this->workspace = $workspace;
    }

    public function validatedBy()
    {
        return 'csv_workspace_user_import_by_full_name_validator';
    }

    public function getDefaultOption()
    {
        return $this->workspace;
    }
}
