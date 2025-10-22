<?php

namespace Claroline\CursusBundle\Component\Template;

use Claroline\TemplateBundle\Component\Template\PdfComponent;
use Claroline\TemplateBundle\Library\SystemTemplate;
use Claroline\TemplateBundle\Model\TemplateInterface;

final class SessionPdf extends PdfComponent
{
    public static function getName(): string
    {
        return 'training_session';
    }

    public function getPlaceholders(): array
    {
        return [
            'course_name',
            'course_code',
            'course_description',
            'course_price',
            'course_price_description',
            'session_url',
            'session_name',
            'session_code',
            'session_description',
            'session_poster',
            'session_price',
            'session_price_description',
            'session_public_registration',
            'session_max_users',
            'session_start_datetime_utc',
            'session_start_date_utc',
            'session_start_time_utc',
            'session_start_datetime',
            'session_start_date',
            'session_start_time',
            'session_end_datetime_utc',
            'session_end_date_utc',
            'session_end_time_utc',
            'session_end_datetime',
            'session_end_date',
            'session_end_time',
            'location',
            'workspace_url',
        ];
    }

    public function getSystemTemplate(): TemplateInterface
    {
        return (new SystemTemplate())
            ->addTemplateContent(
                'en',
                'Training session',
                $this->twig->render('@ClarolineCursus/template/training_session.en.pdf.twig')
            )
            ->addTemplateContent(
                'fr',
                'Session de formation',
                $this->twig->render('@ClarolineCursus/template/training_session.fr.pdf.twig')
            );
    }
}
