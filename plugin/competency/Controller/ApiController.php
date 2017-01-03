<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use HeVinci\CompetencyBundle\Transfer\Validator;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @EXT\Route("/frameworks")
 * @EXT\Method("GET")
 */
class ApiController
{
    private $manager;
    private $validator;

    /**
     * @DI\InjectParams({
     *     "manager"    = @DI\Inject("hevinci.competency.competency_manager"),
     *     "validator"  = @DI\Inject("hevinci.competency.transfer_validator")
     * })
     *
     * @param CompetencyManager $manager
     */
    public function __construct(CompetencyManager $manager, Validator $validator)
    {
        $this->manager = $manager;
        $this->validator = $validator;
    }

    /**
     * @EXT\Route("")
     */
    public function frameworksAction()
    {
        return new JsonResponse($this->manager->listFrameworks(true));
    }

    /**
     * @EXT\Route("/{id}", requirements={"id"="\d+"})
     *
     * @param Competency $framework
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function frameworkAction(Competency $framework)
    {
        $this->manager->ensureIsRoot($framework);
        $response = new Response($this->manager->exportFramework($framework));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @EXT\Route("")
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createFrameworkAction(Request $request)
    {
        $data = $request->getContent();
        $errors = $this->validator->validate($data);

        if ($errors['type'] === Validator::ERR_TYPE_NONE) {
            $this->manager->importFramework($data);

            return new JsonResponse('Framework created');
        }

        return new JsonResponse($errors['errors']);
    }
}
