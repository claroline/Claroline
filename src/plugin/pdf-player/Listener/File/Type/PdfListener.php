<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PdfPlayerBundle\Listener\File\Type;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use Claroline\PdfPlayerBundle\Manager\EvaluationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Integrates PDF files into Claroline.
 */
class PdfListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerProvider $serializer,
        private readonly EvaluationManager $evaluationManager
    ) {
    }

    public function onLoad(LoadFileEvent $event): void
    {
        /** @var File $pdf */
        $pdf = $event->getResource();
        $user = $this->tokenStorage->getToken()?->getUser();

        $event->setData([
            'userEvaluation' => $user instanceof User ? $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($pdf->getResourceNode(), $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            ) : null,
        ]);
        $event->stopPropagation();
    }
}
