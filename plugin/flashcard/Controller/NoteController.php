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
use Claroline\FlashCardBundle\Entity\Card;
use Claroline\FlashCardBundle\Entity\Deck;
use Claroline\FlashCardBundle\Entity\FieldValue;
use Claroline\FlashCardBundle\Entity\FieldValueImage;
use Claroline\FlashCardBundle\Entity\FieldValueText;
use Claroline\FlashCardBundle\Entity\Note;
use Claroline\FlashCardBundle\Entity\NoteType;
use Claroline\FlashCardBundle\Manager\NoteManager;
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
class NoteController
{
    private $manager;
    private $formHandler;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.flashcard.note_manager"),
     *     "handler" = @DI\Inject("claroline.form_handler"),
     *     "checker" = @DI\Inject("security.authorization_checker"),
     *     "serializer" = @DI\Inject("serializer")
     * })
     *
     * @param NoteManager                   $manager
     * @param FormHandler                   $handler
     * @param AuthorizationCheckerInterface $checker
     */
    public function __construct(
        NoteManager $manager,
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
     *     "/note/create/deck/{deck}/note_type/{noteType}",
     *     name="claroline_create_note"
     * )
     * @EXT\Method("POST")
     *
     * @param Request  $request
     * @param Deck     $deck
     * @param NoteType $noteType
     *
     * @return JsonResponse
     */
    public function createNoteAction(Request $request, Deck $deck, NoteType $noteType)
    {
        $fields = $request->request->get('fields', false);
        $response = new JsonResponse();

        $this->assertCanCreate($deck);

        if ($fields) {
            $note = new Note();
            $note->setDeck($deck);
            $note->setNoteType($noteType);

            foreach ($fields as $field) {
                if ($field['fieldValue']['type'] === 'text') {
                    $fieldValue = new FieldValueText();
                    $fieldValue->setValue($field['fieldValue']['value']);
                }
                if ($field['fieldValue']['type'] === 'image') {
                    $fieldValue = new FieldValueImage();
                    $fieldValue->setValue($field['fieldValue']['value']);
                    $fieldValue->setAlt($field['fieldValue']['alt']);
                }

                $fieldValue->setFieldLabel($noteType->getFieldLabel($field['id']));
                $fieldValue->setNote($note);
                $note->addFieldValue($fieldValue);
            }

            foreach ($noteType->getCardTypes() as $cardType) {
                $card = new Card();
                $card->setCardType($cardType);
                $card->setNote($note);
                $note->addCard($card);
            }

            $note = $this->manager->create($note);

            $context = new SerializationContext();
            $context->setGroups('api_flashcard_deck');
            $response->setData(json_decode(
                $this->serializer->serialize($note, 'json', $context)
            ));
        } else {
            $response->setData('Field "fields" is missing');
            $response->setStatusCode(422);
        }

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/note/edit/{note}",
     *     name="claroline_edit_note"
     * )
     * @EXT\Method("POST")
     *
     * @param Request $request
     * @param Note    $note
     *
     * @return JsonResponse
     */
    public function editNoteAction(Request $request, Note $note)
    {
        $fieldValues = $request->request->get('fieldValues', false);
        $response = new JsonResponse();

        $this->assertCanEdit($note->getDeck());

        if ($fieldValues) {
            foreach ($fieldValues as $f) {
                $note->setFieldValue($f['id'], $f['value']);
            }

            $note = $this->manager->create($note);

            $context = new SerializationContext();
            $context->setGroups('api_flashcard_deck');
            $response->setData(json_decode(
                $this->serializer->serialize($note, 'json', $context)
            ));
        } else {
            $response->setData('Field "fieldValues" is missing');
            $response->setStatusCode(422);
        }

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/note/get/{note}",
     *     name="claroline_get_note"
     * )
     *
     * @param Note $note
     *
     * @return JsonResponse
     */
    public function findNoteAction(Note $note)
    {
        $this->assertCanOpen($note->getDeck());

        $response = new JsonResponse();
        $context = new SerializationContext();
        $context->setGroups('api_flashcard_deck');

        return $response->setData(json_decode(
            $this->serializer->serialize($note, 'json', $context)
        ));
    }

    /**
     * @EXT\Route(
     *     "/note/list/deck/{deck}/note_type/{noteType}",
     *     name="claroline_list_notes"
     * )
     *
     * @param Deck     $deck
     * @param NoteType $noteType
     *
     * @return JsonResponse
     */
    public function listNotesAction(Deck $deck, NoteType $noteType)
    {
        $this->assertCanOpen($deck);

        $notes = $this->manager->findByNoteType($deck, $noteType);

        $response = new JsonResponse();
        $context = new SerializationContext();
        $context->setGroups('api_flashcard_deck');

        return $response->setData(json_decode(
            $this->serializer->serialize($notes, 'json', $context)
        ));
    }

    /**
     * @EXT\Route(
     *     "/note/delete/{note}",
     *     name="claroline_delete_note"
     * )
     *
     * @param Note $note
     *
     * @return JsonResponse
     */
    public function deleteNoteAction(Note $note)
    {
        $this->assertCanDelete($note->getDeck());

        $noteId = $note->getId();
        $this->manager->delete($note);

        return new JsonResponse($noteId);
    }

    private function assertCanOpen($obj)
    {
        if (!$this->checker->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedHttpException();
        }
        if (!$this->checker->isGranted('OPEN', $obj)) {
            throw new AccessDeniedHttpException();
        }
    }

    private function assertCanEdit($obj)
    {
        if (!$this->checker->isGranted('EDIT', $obj)) {
            throw new AccessDeniedHttpException();
        }
    }

    private function assertCanCreate($obj)
    {
        if (!$this->checker->isGranted('CREATE', $obj)) {
            throw new AccessDeniedHttpException();
        }
    }

    private function assertCanDelete($obj)
    {
        if (!$this->checker->isGranted('DELETE', $obj)) {
            throw new AccessDeniedHttpException();
        }
    }
}
