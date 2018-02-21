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
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @DI\Validator("csv_home_text_widget_import_validator")
 */
class CsvHomeTextWidgetValidator extends ConstraintValidator
{
    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator"),
     *     "ut"         = @DI\Inject("claroline.utilities.misc"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        TranslatorInterface $translator,
        ClaroUtilities $ut,
        ObjectManager $om
    ) {
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
                if ($nbElements < 4) {
                    $this->context->addViolation($constraint->message);

                    return;
                }
            }
        }

        foreach ($lines as $i => $line) {
            $line = trim($line);
            $widget = explode(';', $line);
            $code = $widget[0];
            $tabName = $widget[1];
            $file = $widget[3];

            $workspace = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($code);

            if (!$workspace) {
                $msg = $this->translator->trans(
                    'workspace_not_exists',
                    ['%code%' => $code, '%line%' => $i + 1],
                    'platform'
                ).' ';
                $this->context->addViolation($msg);
            }

            $tab = $this->om->getRepository('ClarolineCoreBundle:Home\HomeTab')->findBy(['workspace' => $workspace, 'name' => $tabName]);

            if (!$tab) {
                $msg = $this->translator->trans(
                    'tab_not_exists',
                    ['%tab%' => $tabName, '%line%' => $i + 1],
                    'platform'
                ).' ';
                $this->context->addViolation($msg);
            }

            if (!file_exists($file)) {
                $msg = $this->translator->trans(
                    'file_not_exists',
                    ['%file%' => $file, '%line%' => $i + 1],
                    'platform'
                ).' ';
                $this->context->addViolation($msg);
            }
        }
    }
}
