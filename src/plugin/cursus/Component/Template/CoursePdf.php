<?php

namespace Claroline\CursusBundle\Component\Template;

use Claroline\TemplateBundle\Component\Template\PdfComponent;
use Claroline\TemplateBundle\Library\SystemTemplate;
use Claroline\TemplateBundle\Model\TemplateInterface;

final class CoursePdf extends PdfComponent
{
    public static function getName(): string
    {
        return 'training_course';
    }

    public function getPlaceholders(): array
    {
        return [
            'course_name',
            'course_code',
            'course_description',
            'course_poster',
            'course_price',
            'course_price_description',
            'course_default_duration',
            'course_public_registration',
        ];
    }

    public function getSystemTemplate(): TemplateInterface
    {
        return (new SystemTemplate())
            ->addTemplateContent(
                'en',
                'Training - %course_name%',
                $this->twig->render('@ClarolineCursus/template/training_course.en.pdf.twig')
            )
            ->addTemplateContent(
                'fr',
                'Formation - %course_name%',
                $this->twig->render('@ClarolineCursus/template/training_course.fr.pdf.twig')
            );
    }
}
