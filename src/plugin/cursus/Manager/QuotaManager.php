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
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class QuotaManager
{
    /** @var TemplateManager */
    private $templateManager;

    /** @var LocaleManager */
    private $localeManager;

    /** @var MailManager */
    private $mailManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        TemplateManager $templateManager,
        LocaleManager $localeManager,
        MailManager $mailManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->templateManager = $templateManager;
        $this->localeManager = $localeManager;
        $this->mailManager = $mailManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function sendValidatedStatusMail(SessionUser $sessionUser): void
    {
        $manager = $this->tokenStorage->getToken()->getUser();

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

        $this->mailManager->send($subject, $body, [$user], $manager, [], true);
    }

    public function sendManagedStatusMail(SessionUser $sessionUser): void
    {
        $manager = $this->tokenStorage->getToken()->getUser();

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

        $this->mailManager->send($subject, $body, [$user], $manager, [], true);
    }

    public function sendRefusedStatusMail(SessionUser $sessionUser): void
    {
        $manager = $this->tokenStorage->getToken()->getUser();

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

        $this->mailManager->send($subject, $body, [$user], $manager, [], true);
    }

    public function sendCancelledStatusMail(SessionUser $sessionUser): void
    {
        $manager = $this->tokenStorage->getToken()->getUser();

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

        $this->mailManager->send($subject, $body, [$user], $manager, [], true);
    }
}
