<?php

namespace Claroline\EvaluationBundle\Transfer\Exporter;

use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class WorkspaceEvaluationListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['evaluation', 'workspace_evaluation_list'];
    }

    protected static function getClass(): string
    {
        return Evaluation::class;
    }
}
