<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Manager;

use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CursusBundle\Entity\Quota;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class QuotaManager
{
    /** @var TranslatorInterface */
    private $translator;

    /** @var TemplateManager */
    private $templateManager;

    /** @var LocaleManager */
    private $localeManager;

    /** @var MailManager */
    private $mailManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        TranslatorInterface $translator,
        TemplateManager $templateManager,
        LocaleManager $localeManager,
        MailManager $mailManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->translator = $translator;
        $this->templateManager = $templateManager;
        $this->localeManager = $localeManager;
        $this->mailManager = $mailManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function sendValidatedStatusMail(SessionUser $sessionUser): void
    {
        $user = $sessionUser->getUser();
        $locale = $this->localeManager->getLocale($user);

        $placeholders = [
            'session_name' => $sessionUser->getSession()->getName(),
            'user_first_name' => $user->getFirstName(),
            'user_last_name' => $user->getLastName(),
            'session_start' => $sessionUser->getSession()->getStartDate()->format('d/m/Y'),
            'session_end' => $sessionUser->getSession()->getEndDate()->format('d/m/Y'),
        ];
        $subject = $this->templateManager->getTemplate('training_quota_status_validated', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('training_quota_status_validated', $placeholders, $locale);

        $this->mailManager->send($subject, $body, [$user], null, [], true);
    }

    public function sendManagedStatusMail(SessionUser $sessionUser): void
    {
        $user = $sessionUser->getUser();
        $locale = $this->localeManager->getLocale($user);

        $placeholders = [
            'session_name' => $sessionUser->getSession()->getName(),
            'user_first_name' => $user->getFirstName(),
            'user_last_name' => $user->getLastName(),
            'session_start' => $sessionUser->getSession()->getStartDate()->format('d/m/Y'),
            'session_end' => $sessionUser->getSession()->getEndDate()->format('d/m/Y'),
        ];
        $subject = $this->templateManager->getTemplate('training_quota_status_managed', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('training_quota_status_managed', $placeholders, $locale);

        $this->mailManager->send($subject, $body, [$user], null, [], true);
    }

    public function sendRefusedStatusMail(SessionUser $sessionUser): void
    {
        $user = $sessionUser->getUser();
        $locale = $this->localeManager->getLocale($user);

        $placeholders = [
            'session_name' => $sessionUser->getSession()->getName(),
            'user_first_name' => $user->getFirstName(),
            'user_last_name' => $user->getLastName(),
            'session_start' => $sessionUser->getSession()->getStartDate()->format('d/m/Y'),
            'session_end' => $sessionUser->getSession()->getEndDate()->format('d/m/Y'),
        ];
        $subject = $this->templateManager->getTemplate('training_quota_status_refused', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('training_quota_status_refused', $placeholders, $locale);

        $this->mailManager->send($subject, $body, [$user], null, [], true);
    }

    public function sendCancelledStatusMail(SessionUser $sessionUser): void
    {
        $user = $sessionUser->getUser();
        $locale = $this->localeManager->getLocale($user);

        $placeholders = [
            'session_name' => $sessionUser->getSession()->getName(),
            'user_first_name' => $user->getFirstName(),
            'user_last_name' => $user->getLastName(),
            'session_start' => $sessionUser->getSession()->getStartDate()->format('d/m/Y'),
            'session_end' => $sessionUser->getSession()->getEndDate()->format('d/m/Y'),
        ];
        $subject = $this->templateManager->getTemplate('training_quota_status_cancelled', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('training_quota_status_cancelled', $placeholders, $locale);

        $this->mailManager->send($subject, $body, [$user], null, [], true);
    }

    public function sendChangedStatusMail(SessionUser $sessionUser): void
    {
        $STATUS_STRINGS = [
            $this->translator->trans('subscription_pending', [], 'cursus'),
            $this->translator->trans('subscription_refused', [], 'cursus'),
            $this->translator->trans('subscription_validated', [], 'cursus'),
            $this->translator->trans('subscription_managed', [], 'cursus'),
        ];

        $user = $sessionUser->getUser();
        $locale = $this->localeManager->getLocale($user);

        $placeholders = [
            'session_name' => $sessionUser->getSession()->getName(),
            'user_first_name' => $user->getFirstName(),
            'user_last_name' => $user->getLastName(),
            'session_start' => $sessionUser->getSession()->getStartDate()->format('d/m/Y'),
            'session_end' => $sessionUser->getSession()->getEndDate()->format('d/m/Y'),
            'status' => $STATUS_STRINGS[$sessionUser->getStatus()],
        ];
        $subject = $this->templateManager->getTemplate('training_quota_status_changed', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('training_quota_status_changed', $placeholders, $locale);

        $this->mailManager->send($subject, $body, [$user], null, [], true);
        /*$user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof \Claroline\CoreBundle\Entity\User) {
        }*/
    }
}
