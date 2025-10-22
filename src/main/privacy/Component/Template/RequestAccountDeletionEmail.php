<?php

namespace Claroline\PrivacyBundle\Component\Template;

use Claroline\TemplateBundle\Component\Template\EmailComponent;
use Claroline\TemplateBundle\Library\SystemTemplate;
use Claroline\TemplateBundle\Model\TemplateInterface;

final class RequestAccountDeletionEmail extends EmailComponent
{
    public static function getName(): string
    {
        return 'request_account_deletion';
    }

    public function getPlaceholders(): array
    {
        return [
            'first_name',
            'last_name',
            'username',
            'email',
            'id',
        ];
    }

    public function getSystemTemplate(): TemplateInterface
    {
        return (new SystemTemplate())
            ->addTemplateContent(
                'en',
                'Account deletion request',
                $this->twig->render('@ClarolinePrivacy/template/request_account_deletion.en.html.twig')
            )
            ->addTemplateContent(
                'fr',
                'Demande de suppression de compte',
                $this->twig->render('@ClarolinePrivacy/template/request_account_deletion.fr.html.twig')
            );
    }
}
