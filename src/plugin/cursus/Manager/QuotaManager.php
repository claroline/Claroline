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

class QuotaManager
{
    /** @var TemplateManager */
    private $templateManager;

    public function __construct(
        TemplateManager $templateManager
    ) {
        $this->templateManager = $templateManager;
    }

    public function generateFromTemplate(Quota $quota, array $subscriptions, string $locale): string
    {
        $placeholders = [
            'organization_name' => $quota->getOrganization()->getName(),
        ];

        return $this->templateManager->getTemplate('training_quota', $placeholders, $locale);
    }
}
