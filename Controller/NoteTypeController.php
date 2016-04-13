<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Controller;

use Claroline\CoreBundle\Form\Handler\FormHandler;
use Claroline\FlashCardBundle\Entity\NoteType;
use Claroline\FlashCardBundle\Manager\NoteTypeManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @EXT\Route(requirements={"id"="\d+", "abilityId"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class NoteTypeController
{
    private $manager;
    private $formHandler;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.flashcard.note_type_manager"),
     *     "handler" = @DI\Inject("claroline.form_handler"),
     *     "checker" = @DI\Inject("security.authorization_checker"),
     *     "serializer" = @DI\Inject("serializer")
     * })
     *
     * @param NoteTypeManager               $manager
     * @param FormHandler                   $handler
     * @param AuthorizationCheckerInterface $checker
     */
    public function __construct(
        NoteTypeManager $manager,
        FormHandler $handler,
        AuthorizationCheckerInterface $checker,
        $serializer
    )
    {
        $this->manager = $manager;
        $this->formHandler = $handler;
        $this->checker = $checker;
        $this->serializer = $serializer;
    }

    /**
     * @EXT\Route("/note_type/all", name="claroline_getall_note_type")
     *
     * @return JsonResponse
     */
    public function allNoteTypesAction()
    {
        $noteTypes = $this->manager->getAll();
        $context = new SerializationContext();
        $context->setGroups('api_flashcard_note_type');

        return new JsonResponse(json_decode(
            $this->serializer->serialize($noteTypes, 'json', $context)
        ));
    }
}
