<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Functional;

use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResourceScoreEvent extends Event
{
    private $user;
    private $resourceUserEvaluation;

    public function __construct(User $user, ResourceUserEvaluation $resourceUserEvaluation)
    {
        $this->user = $user;
        $this->resourceUserEvaluation = $resourceUserEvaluation;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getResourceUserEvaluation(): ResourceUserEvaluation
    {
        return $this->resourceUserEvaluation;
    }

    public function getMessage(TranslatorInterface $translator)
    {
        return $translator->trans(
            'resourceScore',
            [
                'userName' => $this->user->getUsername(),
                'statusName' => $this->resourceUserEvaluation->getStatus(),
                'resourceName' => $this->resourceUserEvaluation->getResourceNode()->getName(),
                'currentScore' => $this->resourceUserEvaluation->getScore(),
                'scoreMin' => $this->resourceUserEvaluation->getScoreMin(),
                'scoreMax' => $this->resourceUserEvaluation->getScoreMax(),
            ],
            'functional'
        );
    }
}
