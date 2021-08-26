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

use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CursusBundle\Entity\Quota;
use Symfony\Contracts\Translation\TranslatorInterface;

class QuotaManager
{
    /** @var TranslatorInterface */
    private $translator;

    /** @var TemplateManager */
    private $templateManager;

    public function __construct(
        TranslatorInterface $translator,
        TemplateManager $templateManager
    ) {
        $this->translator = $translator;
        $this->templateManager = $templateManager;
    }

    public function generateFromTemplate(Quota $quota, array $subscriptions, string $locale): string
    {
        $status = [
            $this->translator->trans('subscription_pending', [], 'cursus'),
            $this->translator->trans('subscription_refused', [], 'cursus'),
            $this->translator->trans('subscription_validated', [], 'cursus'),
            $this->translator->trans('subscription_managed', [], 'cursus'),
        ];
        $placeholders = [
            'organization_name' => $quota->getOrganization()->getName(),
            'quota_threshold' => $quota->getThreshold(),
            'subscriptions_count' => count($subscriptions),
            'subscriptions' => array_reduce($subscriptions, function ($accum, $subscription) use ($status) {
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
                $status[$subscription->getStatus()]
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
