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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use Claroline\PdfPlayerBundle\Manager\UserEvaluationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Integrates PDF files into Claroline.
 */
class PdfListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SerializerProvider */
    private $serializer;

    /** @var UserEvaluationManager */
    private $userEvaluationManager;

    /**
     * PdfListener constructor.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        SerializerProvider $serializer,
        UserEvaluationManager $userEvaluationManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->userEvaluationManager = $userEvaluationManager;
    }

    public function onLoad(LoadFileEvent $event)
    {
        /** @var File $pdf */
        $pdf = $event->getResource();
        $user = $this->tokenStorage->getToken()->getUser();

        $event->setData([
            'userEvaluation' => $user instanceof User ? $this->serializer->serialize(
                $this->userEvaluationManager->getResourceUserEvaluation($pdf->getResourceNode(), $user),
                [Options::SERIALIZE_MINIMAL]
            ) : null,
        ]);
        $event->stopPropagation();
    }
}
