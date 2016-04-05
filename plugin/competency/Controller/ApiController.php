<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use HeVinci\CompetencyBundle\Transfer\Validator;
use JMS\DiExtraBundle\Annotation as DI;
use Nelmio\ApiDocBundle\Annotation as NMO;
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
     * @NMO\ApiDoc(
     *     description="List competency frameworks. Included attributes are *id*, *name* and *description*.",
     *     views={"competencies"}
     * )
     */
    public function frameworksAction()
    {
        return new JsonResponse($this->manager->listFrameworks(true));
    }

    /**
     * @EXT\Route("/{id}", requirements={"id"="\d+"})
     * @NMO\ApiDoc(
     *     description="Get the full JSON representation of a specified competency framework.",
     *     parameters={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="id of the framework"}
     *     },
     *     views = {"competencies"}
     * )
     *
     * @param Competency $framework
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
     * @NMO\ApiDoc(
     *     description="Create a new competency framework. Sent data must be a valid JSON representation of the framework.",
     *     views={"competencies"}
     * )
     * @param Request $request
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
