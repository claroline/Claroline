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
use Claroline\FlashCardBundle\Entity\CardType;
use Claroline\FlashCardBundle\Entity\FieldLabel;
use Claroline\FlashCardBundle\Entity\NoteType;
use Claroline\FlashCardBundle\Manager\NoteTypeManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
    ) {
        $this->manager = $manager;
        $this->formHandler = $handler;
        $this->checker = $checker;
        $this->serializer = $serializer;
    }

    /**
     * @EXT\Route(
     *     "/note_type/edit",
     *     name="claroline_edit_note_type"
     * )
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function editNoteTypeAction(Request $request)
    {
        $this->assertIsAuthenticated();

        $response = new JsonResponse();
        $noteType = $request->request->get('noteType', false);
        if ($noteType) {
            if (array_key_exists('id', $noteType) && !empty($noteType['id'])) {
                $response->setData('Function not implemented yet');
                $response->setStatusCode(422);
            } else {
                $newNoteType = new NoteType();
                $newNoteType->setName($noteType['name']);
                foreach ($noteType['field_labels'] as $field) {
                    $f = new FieldLabel();
                    $f->setName($field['name']);
                    $f->setNoteType($newNoteType);
                    $newNoteType->addFieldLabel($f);
                }
                foreach ($noteType['card_types'] as $cardType) {
                    $newCardType = new cardType();
                    $newCardType->setNoteType($newNoteType);
                    $newCardType->setName($cardType['name']);
                    foreach ($cardType['questions'] as $question) {
                        $newCardType->addQuestion(
                            $newNoteType->getFieldLabelFromName($question['name']));
                    }
                    foreach ($cardType['answers'] as $answer) {
                        $newCardType->addAnswer(
                            $newNoteType->getFieldLabelFromName($answer['name']));
                    }
                    $newNoteType->addCardType($newCardType);
                }
                if ($newNoteType->isValid()) {
                    $newNoteType = $this->manager->create($newNoteType);
                    $response->setData($newNoteType->getId());
                } else {
                    $response->setData('NoteType is not valid');
                    $response->setStatusCode(422);
                }
            }
        } else {
            $response->setData('Field "noteType" is missing');
            $response->setStatusCode(422);
        }

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/note_type/get/{noteTypeId}",
     *     name="claroline_get_note_type"
     * )
     *
     * @param int $noteTypeId
     *
     * @return JsonResponse
     */
    public function findNoteTypeAction($noteTypeId)
    {
        $this->assertIsAuthenticated();
        $noteType = $this->manager->get($noteTypeId);
        if (!$noteType) {
            $noteType = new NoteType();
            $noteType->setName('Basic');

            $frontField = new FieldLabel();
            $frontField->setName('Front');
            $noteType->addFieldLabel($frontField);
            $backField = new FieldLabel();
            $backField->setName('Back');
            $noteType->addFieldLabel($backField);

            $cardType = new CardType();
            $cardType->setName('Forward');
            $cardType->addQuestion($frontField);
            $cardType->addAnswer($backField);
            $noteType->addCardType($cardType);
        }

        $response = new JsonResponse();
        $context = new SerializationContext();
        $context->setGroups('api_flashcard_note_type');
        $response->setData(json_decode(
            $this->serializer->serialize($noteType, 'json', $context)
        ));

        return $response;
    }

    /**
     * @EXT\Route("/note_type/all", name="claroline_getall_note_type")
     *
     * @return JsonResponse
     */
    public function allNoteTypesAction()
    {
        $this->assertIsAuthenticated();

        $noteTypes = $this->manager->getAll();
        $context = new SerializationContext();
        $context->setGroups('api_flashcard_note_type');

        return new JsonResponse(json_decode(
            $this->serializer->serialize($noteTypes, 'json', $context)
        ));
    }

    private function assertIsAuthenticated()
    {
        if (!$this->checker->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedHttpException();
        }
    }
}
