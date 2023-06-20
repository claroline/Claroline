<?php

namespace Claroline\PrivacyBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PrivacyManager
{
    private ObjectManager $objectManager;
    private MailManager $mailManager;
    private TranslatorInterface $translator;
    private TemplateManager $templateManager;

    public function __construct(
        ObjectManager $objectManager,
        MailManager $mailManager,
        TranslatorInterface $translator,
        TemplateManager $templateManager,
    ) {
        $this->objectManager = $objectManager;
        $this->mailManager = $mailManager;
        $this->translator = $translator;
        $this->templateManager = $templateManager;
    }

    public function sendRequestToDPO(UserInterface $user)
    {
        if ($this->mailManager->isMailerAvailable()) {
            $name = $user->getFullName();
            $idUser = $user->getId();
            $privacyEntity = $this->objectManager->getRepository(PrivacyParameters::class)->findOneBy([], ['id' => 'ASC']);
            $dpoEmail = $privacyEntity->getDpoEmail();

            $locale = $user->getLocale();

            $subject = $this->translator->trans('account_deletion.subject', [], 'privacy', $locale);
            $content = $this->translator->trans('account_deletion.body', ['%name%' => $name, '%id%' => $idUser], 'privacy', $locale);
            $body = $this->templateManager->getTemplate('email_layout', ['content' => $content], $locale);

            return $this->mailManager->send($subject, $body, [], null, ['to' => [$dpoEmail]], false, $user->getEmail());
        }

        return false;
    }
}
