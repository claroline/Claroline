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

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResourceEvaluationEvent extends Event
{
    private $user;
    private $resourceNode;
    private $evaluation;

    public function __construct(User $user, ResourceNode $resourceNode, ResourceEvaluation $evaluation)
    {
        $this->user = $user;
        $this->resourceNode = $resourceNode;
        $this->evaluation = $evaluation;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getResourceNode(): ResourceNode
    {
        return $this->resourceNode;
    }

    public function getResourceEvaluation(): ResourceEvaluation
    {
        return $this->evaluation;
    }

    public function getMessage(TranslatorInterface $translator)
    {
        return $translator->trans(
            'resourceEvaluation',
            [
                'userName' => $this->user->getUsername(),
                'resourceName' => $this->resourceNode->getName(),
                'statusName' => $this->evaluation->getStatus(),
                'userProgression' => $this->evaluation->getProgression().'/'.$this->evaluation->getProgressionMax(),
            ],
            'functional'
        );
    }
}
