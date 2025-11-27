<?php

namespace Claroline\EvaluationBundle\Component\Template;

use Claroline\TemplateBundle\Component\Template\PdfComponent;
use Claroline\TemplateBundle\Library\SystemTemplate;
use Claroline\TemplateBundle\Model\TemplateInterface;

final class EvaluationSuccessCertificatePdf extends PdfComponent
{
    public static function getName(): string
    {
        return 'evaluation_success_certificate';
    }

    public function getPlaceholders(): array
    {
        return [
            'certificate_obtention_datetime_utc',
            'certificate_obtention_date_utc',
            'certificate_obtention_time_utc',
            'certificate_obtention_datetime',
            'certificate_obtention_date',
            'certificate_obtention_time',

            'certificate_issued_datetime_utc',
            'certificate_issued_date_utc',
            'certificate_issued_time_utc',
            'certificate_issued_datetime',
            'certificate_issued_date',
            'certificate_issued_time',

            'evaluated_content_name',
            'evaluated_content_code',
            'evaluated_content_description',
            'evaluated_content_poster',

            'user_first_name',
            'user_last_name',
            'user_username',

            'evaluation_duration',
            'evaluation_status',

            'evaluation_last_activity_datetime_utc',
            'evaluation_last_activity_date_utc',
            'evaluation_last_activity_time_utc',
            'evaluation_last_activity_datetime',
            'evaluation_last_activity_date',
            'evaluation_last_activity_time',

            'evaluation_start_datetime_utc',
            'evaluation_start_date_utc',
            'evaluation_start_time_utc',
            'evaluation_start_datetime',
            'evaluation_start_date',
            'evaluation_start_time',

            'evaluation_end_datetime_utc',
            'evaluation_end_date_utc',
            'evaluation_end_time_utc',
            'evaluation_end_datetime',
            'evaluation_end_date',
            'evaluation_end_time',

            'evaluation_score',
            'evaluation_score_max',
        ];
    }

    public function getSystemTemplate(): TemplateInterface
    {
        return (new SystemTemplate())
            ->addTemplateContent(
                'en',
                'Certificate of achievement in "%evaluated_content_name%"',
                $this->twig->render('@ClarolineEvaluation/template/evaluation_success_certificate.en.pdf.twig')
            )
            ->addTemplateContent(
                'fr',
                'Certificat de réussite à "%evaluated_content_name%"',
                $this->twig->render('@ClarolineEvaluation/template/evaluation_success_certificate.fr.pdf.twig')
            );
    }
}
