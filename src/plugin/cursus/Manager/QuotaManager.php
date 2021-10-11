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

    public function sendSetStatusMail(SessionUser $sessionUser): void
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
        $subject = $this->templateManager->getTemplate('training_quota_set_status', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('training_quota_set_status', $placeholders, $locale);

        $this->mailManager->send($subject, $body, [$user], null, [], true);
        /*$user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof \Claroline\CoreBundle\Entity\User) {
        }*/
    }

    public function generateFromTemplate(Quota $quota, array $subscriptions, string $locale): string
    {
        $STATUS_STRINGS = [
            $this->translator->trans('subscription_pending', [], 'cursus'),
            $this->translator->trans('subscription_refused', [], 'cursus'),
            $this->translator->trans('subscription_validated', [], 'cursus'),
            $this->translator->trans('subscription_managed', [], 'cursus'),
        ];
        $placeholders = [
            'organization_name' => $quota->getOrganization()->getName(),
            'quota_threshold' => $quota->getThreshold(),
            'subscriptions_count' => count($subscriptions),
            'subscriptions' => array_reduce($subscriptions, function ($accum, $subscription) use ($STATUS_STRINGS) {
                $user = $subscription->getUser();
                $session = $subscription->getSession();

                return $accum.sprintf('
                <tr>
                    <td style="border:solid 1px #888;padding:.5rem;">%s</td>
                    <td style="border:solid 1px #888;padding:.5rem;">%s %s</td>
                    <td style="border:solid 1px #888;padding:.5rem;">%s</td>
                    <td style="border:solid 1px #888;padding:.5rem;">CHF %s</td>
                    <td style="border:solid 1px #888;padding:.5rem;">%s</td>
                    <td style="border:solid 1px #888;padding:.5rem;">%s</td>
                </tr>
                ',
                $session->getName(),
                $user->getFirstName(),
                $user->getLastName(),
                number_format($session->getQuotaDays(), 2),
                number_format($session->getPrice(), 2),
                $session->getStartDate()->format('d/m/Y'),
                $STATUS_STRINGS[$subscription->getStatus()]
            );
            }, '<table style="width:100%;border:solid 1px #000;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="border:solid 1px #888;padding:.5rem;">'.$this->translator->trans('session', [], 'cursus').'</th>
                    <th style="border:solid 1px #888;padding:.5rem;">'.$this->translator->trans('user', [], 'cursus').'</th>
                    <th style="border:solid 1px #888;padding:.5rem;">'.$this->translator->trans('days', [], 'cursus').'</th>
                    <th style="border:solid 1px #888;padding:.5rem;">'.$this->translator->trans('price', [], 'cursus').'</th>
                    <th style="border:solid 1px #888;padding:.5rem;">'.$this->translator->trans('start_date', [], 'cursus').'</th>
                    <th style="border:solid 1px #888;padding:.5rem;">'.$this->translator->trans('status', [], 'cursus').'</th>
                </tr>
            </thead>
            <tbody>').'</tbody></table>',
        ];

        return $this->templateManager->getTemplate('training_quota', $placeholders, $locale);
    }
}
