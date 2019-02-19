<?php

namespace UJM\ExoBundle\Controller\Api;

use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\DocimologyManager;
use UJM\ExoBundle\Manager\ExerciseManager;

/**
 * Exercise API Controller exposes REST API.
 *
 * @EXT\Route("/exercises", options={"expose"=true})
 */
class ExerciseController extends AbstractController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var ExerciseManager */
    private $exerciseManager;

    /** @var DocimologyManager */
    private $docimologyManager;

    /**
     * ExerciseController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"     = @DI\Inject("security.authorization_checker"),
     *     "exerciseManager"   = @DI\Inject("ujm_exo.manager.exercise"),
     *     "docimologyManager" = @DI\Inject("ujm_exo.manager.docimology")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ExerciseManager               $exerciseManager
     * @param DocimologyManager             $docimologyManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ExerciseManager $exerciseManager,
        DocimologyManager $docimologyManager
    ) {
        $this->authorization = $authorization;
        $this->exerciseManager = $exerciseManager;
        $this->docimologyManager = $docimologyManager;
    }

    /**
     * Gets the full representation of an exercise (including solutions) in a JSON format.
     *
     * @EXT\Route("/{id}", name="exercise_get")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
     *
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function getAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        return new JsonResponse(
            $this->exerciseManager->serialize($exercise, [Transfer::INCLUDE_SOLUTIONS])
        );
    }

    /**
     * Updates an Exercise.
     *
     * @EXT\Route("/{id}", name="exercise_update")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
     * @EXT\Method("PUT")
     *
     * @param Exercise $exercise
     * @param Request  $request
     *
     * @return JsonResponse
     */
    public function updateAction(Exercise $exercise, Request $request)
    {
        $this->assertHasPermission('EDIT', $exercise);

        $errors = [];

        $data = $this->decodeRequestData($request);

        if (null === $data) {
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to update exercise
            try {
                $this->exerciseManager->update($exercise, $data);
            } catch (InvalidDataException $e) {
                $errors = $e->getErrors();
            }
        }

        if (!empty($errors)) {
            // Invalid data received
            return new JsonResponse($errors, 422);
        }

        // Exercise updated
        return new JsonResponse(
            $this->exerciseManager->serialize($exercise, [Transfer::INCLUDE_SOLUTIONS])
        );
    }

    /**
     * Publishes an exercise.
     *
     * @EXT\Route("/{id}/publish", name="exercise_publish")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
     *
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function publishAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $this->exerciseManager->publish($exercise);

        return new JsonResponse(null, 204);
    }

    /**
     * Unpublishes an exercise.
     *
     * @EXT\Route("/{id}/unpublish", name="exercise_unpublish")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
     *
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function unpublishAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $this->exerciseManager->unpublish($exercise);

        return new JsonResponse(null, 204);
    }

    /**
     * download json quiz.
     *
     * @EXT\Route("/{id}/export", name="exercise_export")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
     *
     * @param Exercise $exercise
     *
     * @return Response
     */
    public function exportAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $file = $this->exerciseManager->export($exercise);

        $response = new StreamedResponse();
        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $name = $exercise->getResourceNode()->getName().'.json';
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.urlencode($name));
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Connection', 'close');
        $response->send();

        return new Response();
    }

    /**
     * Opens the docimology of a quiz.
     *
     * @EXT\Route("/{id}/docimology", name="exercise_docimology")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
     * @EXT\Template("UJMExoBundle:exercise:docimology.html.twig")
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    public function docimologyOpenAction(Exercise $exercise)
    {
        return [
            '_resource' => $exercise,
            'resourceNode' => $exercise->getResourceNode(),
            'exercise' => $this->exerciseManager->serialize($exercise, [Transfer::MINIMAL]),
            'statistics' => $this->docimologyManager->getStatistics($exercise, 100),
        ];
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
