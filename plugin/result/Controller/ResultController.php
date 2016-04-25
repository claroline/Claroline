<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\Handler\FormHandler;
use Claroline\ResultBundle\Entity\Mark;
use Claroline\ResultBundle\Entity\Result;
use Claroline\ResultBundle\Manager\ResultManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route(requirements={"id"="\d+", "abilityId"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class ResultController
{
    private $manager;
    private $formHandler;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.result.result_manager"),
     *     "handler" = @DI\Inject("claroline.form_handler"),
     *     "checker" = @DI\Inject("security.authorization_checker")
     * })
     *
     * @param ResultManager                 $manager
     * @param FormHandler                   $handler
     * @param AuthorizationCheckerInterface $checker
     */
    public function __construct(
        ResultManager $manager,
        FormHandler $handler,
        AuthorizationCheckerInterface $checker
    ) {
        $this->manager = $manager;
        $this->formHandler = $handler;
        $this->checker = $checker;
    }

    /**
     * @EXT\Route("/{id}", name="claro_open_result")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Template
     *
     * @param Result $result
     *
     * @return array
     */
    public function resultAction(Result $result, User $user)
    {
        if (!$this->checker->isGranted('OPEN', $result)) {
            throw new AccessDeniedException();
        }

        $canEdit = $this->checker->isGranted('EDIT', $result);

        return [
            '_resource' => $result,
            'marks' => $this->manager->getMarks($result, $user, $canEdit),
            'users' => $this->manager->getUsers($result, $canEdit),
            'canEdit' => $canEdit,
        ];
    }

    /**
     * @EXT\Route("/{id}/users/{userId}", name="claro_create_mark")
     * @EXT\ParamConverter("user", options={"id"= "userId"})
     * @EXT\Method("POST")
     *
     * @param Request $request
     * @param Result  $result
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function createMarkAction(Request $request, Result $result, User $user)
    {
        $this->assertCanEdit($result);
        $mark = $request->request->get('mark', false);
        $response = new JsonResponse();

        if ($mark !== false) {
            if (!$this->manager->isValidMark($result, $mark)) {
                $response->setData('Mark is not valid');
                $response->setStatusCode(422);
            } else {
                $mark = $this->manager->createMark($result, $user, $mark);
                $response->setData($mark->getId());
            }
        } else {
            $response->setData('Field "mark" is missing');
            $response->setStatusCode(422);
        }

        return $response;
    }

    /**
     * @EXT\Route("/marks/{id}", name="claro_delete_mark")
     * @EXT\Method("DELETE")
     *
     * @param Mark $mark
     *
     * @return JsonResponse
     */
    public function deleteMarkAction(Mark $mark)
    {
        $this->assertCanEdit($mark->getResult());
        $this->manager->deleteMark($mark);

        return new JsonResponse('', 204);
    }

    /**
     * @EXT\Route("/marks/{id}", name="claro_edit_mark")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     * @param Mark    $mark
     *
     * @return JsonResponse
     */
    public function editMarkAction(Request $request, Mark $mark)
    {
        $this->assertCanEdit($mark->getResult());
        $newValue = $request->request->get('value', false);
        $response = new JsonResponse();

        if ($newValue !== false) {
            if (!$this->manager->isValidMark($mark->getResult(), $newValue)) {
                $response->setData('Mark is not valid');
                $response->setStatusCode(422);
            } else {
                $this->manager->updateMark($mark, $newValue);
                $response->setStatusCode(204);
            }
        } else {
            $response->setData('Field "value" is missing');
            $response->setStatusCode(422);
        }

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/{id}/marks/import/{type}",
     *     name="claro_import_marks",
     *     defaults={"type"="fullname"},
     * )
     * @EXT\Method("POST")
     *
     * @param Result  $result
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function importAction(Request $request, Result $result, $type)
    {
        $this->assertCanEdit($result);
        $file = $request->files->get('file', false);
        $response = new JsonResponse();

        if ($file === false) {
            $response->setData('Field "file" is missing');
            $response->setStatusCode(422);
        } else {
            $data = $this->manager->importMarksFromCsv($result, $file, $type);

            if (count($data['errors']) > 0) {
                $response->setStatusCode(422);
                $response->setData($data['errors']);
            } else {
                $response->setData($data['marks']);
            }
        }

        return $response;
    }

    private function assertCanEdit(Result $result)
    {
        if (!$this->checker->isGranted('EDIT', $result)) {
            throw new AccessDeniedHttpException();
        }
    }
}
