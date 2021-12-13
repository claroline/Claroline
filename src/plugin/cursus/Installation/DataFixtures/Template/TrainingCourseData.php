<?php

namespace Claroline\CursusBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class TrainingCourseData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_course';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Training',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_course.en.pdf.twig'),
                ],
                'fr' => [
                    'title' => 'Formation',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_course.fr.pdf.twig'),
                ],
            ],
        ];
    }
}
