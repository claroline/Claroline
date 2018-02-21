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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @DI\Validator("csv_home_tab_import_validator")
 */
class CsvHomeTabValidator extends ConstraintValidator
{
    /**
     * @DI\InjectParams({
     *     "translator"       = @DI\Inject("translator"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "ut"               = @DI\Inject("claroline.utilities.misc"),
     *     "om"               = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        TranslatorInterface $translator,
        WorkspaceManager $workspaceManager,
        ClaroUtilities $ut,
        ObjectManager $om
    ) {
        $this->workspaceManager = $workspaceManager;
        $this->translator = $translator;
        $this->ut = $ut;
        $this->om = $om;
    }

    public function validate($value, Constraint $constraint)
    {
        $data = $this->ut->formatCsvOutput(file_get_contents($value));
        $lines = str_getcsv($data, PHP_EOL);

        foreach ($lines as $line) {
            $linesTab = explode(';', $line);
            $nbElements = count($linesTab);

            if ('' !== trim($line)) {
                if ($nbElements < 2) {
                    $this->context->addViolation($constraint->message);

                    return;
                }
            }
        }

        foreach ($lines as $i => $line) {
            $line = trim($line);
            $tab = explode(';', $line);
            $code = $tab[0];
            $workspace = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($code);

            if (!$workspace) {
                $msg = $this->translator->trans(
                    'workspace_not_exists',
                    ['%code%' => $code, '%line%' => $i + 1],
                    'platform'
                ).' ';
                $this->context->addViolation($msg);
            }
        }
    }
}
